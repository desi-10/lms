<?php
    declare(strict_types=1);

    namespace App;
    use mysqli;
    use RuntimeException;
    use Throwable;

    class Database extends mysqli
    {
        private string $status;
        private string $status1;

        private array $logs = [];
        private array $queries = [];

        public function __construct(
            private string $host = "localhost", private string $username  = "root",  
            private string $password = "", private string $database = "elms"
        ){
            $this->$host = $host; 
            $this->$username = $username; 
            $this->password = $password; 
            $this->database = $database;

            $this->connect($host, $username, $password, $database);

            if($this->connect_errno){
                $this->status = "Mysqli Connection Error: ".$this->connect_errno;
                $this->addLog($this->status);
                throw new RuntimeException("Mysqli Connection Error: ".$this->connect_errno);
            }else{
                $this->setStatus("success");
                $this->addLog("Database connection was successful");
            }
        }

        /**This function returns all queries done from the database */
        public function queries() :array{
            return $this->queries;
        }

        private function setQuery(string $sql, array $values = []){
            $this->queries[] = [
                "statement" => $sql, "values" => implode(", ",$values)
            ];
        }

        public function status(){
            return $this->status;
        }

        /**
         * This function is used to log a message into the logs array
         * @param string $message the message to log
         */
        private function addLog(string $message){
            $this->logs[] = date("Y-m-d H:i:s") .": ". $message;
        }

        /**
         * Retrieve all saved logs
         */
        public function getLogs() :array{
            return $this->logs;
        }

        public function clearStatus(){
            $this->status = $this->status1 = "";
        }

        /**
         * used to select a group of items from db
         * presents an associated array
         * @param array|string $columns Array or comma separated string of the columns
         * @param array|string $table_name Array or string of table(s) to fetch data from
         * @param array|string $where Array or string of condition(s) to use
         * @param array|string $where_binds Array or strings for binding where conditions
         * @param int $limit This is the limit of results to be shown
         * @param null|string $no_results This holds the error message to show when no result is returned
         * 
         * @return array|string|bool an array of data or a single data or error message
         */
        public function fetch(array|string $columns, array|string $table_name, 
            array|string $where = "", string|array $where_binds = "", 
            string|null $no_results = null
        ) :array|string|bool{
            $response = false;

            try{
                $columns = $this->stringifyColumn($columns);
                $table_name = $this->stringifyTable($table_name);
                $where = $this->stringifyWhere($where, $where_binds);

                $sql = "SELECT $columns FROM $table_name";
                $sql .= !empty($where) ? " WHERE $where" : "";

                //record query
                $this->setQuery($sql);

                $data = $this->query($sql);
                
                if($data->num_rows > 0){
                    $response = $data->fetch_all(1);
                    $this->setStatus("{$data->num_rows} results returned from fetch",true);
                }elseif($data->num_rows == 0){
                    $response = $no_results ?? "No results matched the query";
                    $this->setStatus($response, true);
                }else{
                    $response = false;
                }
            }catch(Throwable $th){
                $response = false;
                $this->setStatus($th->getMessage(), true);
            }

            return $response;
        }

        /**
         * Used to stringify the column
         * @param string|string[] $column This is the column to be stringified
         * @return string the stringified column
         */
        private function stringifyColumn(string|array $column) :string{
            $new_column = "";

            if(!is_array($column)){
                $new_column = $column;
            }else{
                $new_column = implode(", ", $column);
            }

            return $new_column;
        }

        /**
         * Used to stringify the column
         * @param string|string[] $where This is the where query part to be stringified
         * @param string|string[] $binder This is what joins the parts together
         * @return string the stringified where part of the query string
         */
        private function stringifyWhere(string|array $where, string|array $bind = "") :string{
            $new_where = "";

            if(!is_array($where)){
                $new_where = $where;
            }else{
                if(is_array($bind)){
                    foreach($bind as $key => $binder){
                        if(!empty($new_where)){
                            $new_where .= " ";
                        }

                        $new_where .= $where[$key]." ".$binder;
                    }
                }else if(!empty($bind) && 
                    array_search(strtolower($bind), ["or", "and", "like"], true) !== false
                ){
                    $new_where = implode(" $bind ", $where);
                }else{
                    $new_where = implode(" ", $where);
                }
            }

            return $new_where;
        }

        /**
         * used to stringify the table query part
         * @param string|string[] $table The table query to stringify
         * 
         * @return string The formated version of the table query
         */
        private function stringifyTable(string|array $tables) :string{
            $new_tables = "";

            if(is_array($tables)){
                if(is_array($tables[0])){
                    // at this point, tables should have the following keys
                    // "join" => "table1 table2", "alias" => "tb1 tb2" and "on" => "id1 id2"
                    foreach($tables as $table){
                        list($table1, $table2, $alias1, 
                            $alias2, $ref1, $ref2) = $this->tableArraySplit($table);
    
                        //bind table1 to string
                        $this->joinTableString($new_tables, $table1, $alias1);
                        $this->joinTableString($new_tables, $table2, $alias2);
                        $this->onTableString($new_tables, $table);
                    }
                }else{
                    // only table names should assume ids of the tables
                    $new_tables = implode(" JOIN ", $tables);                
                }
            }else{
                $new_tables = $tables;
            }

            return $new_tables;
        }

        public function setStatus(string $message, bool $log = false){
            $this->status = $message;

            if($log){
                $this->addLog($message);
            }
        }

        private function tableArraySplit(array $table) :array{
            return array_merge(
                explode(" ", $table["join"]), 
                explode(" ", $table["alias"]), 
                explode(" ", $table["on"])
            );
        }

        private function joinTableString(&$new_table, $table, $table_alias){
            if(!str_contains($new_table, $table)){
                if(empty($new_table)){
                    $new_table = $table;
                }else{
                    $new_table .= " JOIN " . $table;
                }

                if(!empty($table_alias)){
                    $new_table .= " $table_alias";
                }
            }
        }

        private function onTableString(&$new_table, $table){
            list($table1, $table2, $alias1, 
                        $alias2, $ref1, $ref2) = $this->tableArraySplit($table);
            $lhs = empty($alias1) ? $table1 : $alias1;
            $rhs = empty($alias2) ? $table2 : $alias2;

            $new_table .= " ON $lhs.$ref1 = $rhs.$ref2";
        }

        /**
         * This function is used to insert a new rows into a table
         * @param string $table_name This is the table name
         * @param array $data This is the data to be inserted [NB: It should be an associative array]
         * @return bool returns true or false if something
         */
        public function insert(string $table_name, array $data) :bool|string{
            $response = false;

            try {
                if(!is_array($data)){
                    $this->setStatus("Data provided is not an array", true);
                    return false;
                }

                if($this->verifyTable($table_name)){
                    $columns = array_keys($data);
                    $values = array_values($data);
                    $placeholders = $this->createPlaceholder(count($columns));
        
                    $sql = "INSERT INTO $table_name (".implode(", ", $columns).") VALUES ($placeholders)";
                    
                    //keep track of query
                    $this->setQuery($sql, $values);
                    
                    if($response = $this->parse($sql, $values)){
                        $this->setStatus("Data was added to '$table_name' table", true);
                    }else{
                        $this->addLog("Data could not be added to table '$table_name'");
                    }
                }else{
                    $this->setStatus("Table not found", true);
                }
            } catch (Throwable $th) {
                $this->setStatus($th->getTraceAsString());
                $this->addLog($th->getMessage());
            }

            return $response;
        }

        /**
         * This function is used to delete from a specific table
         * @param string $table The name of the table from which data should be deleted
         * @param string|string[] $condition The condition to be used 
         * @param string|string[] $conditon_binds This is the set of binds to be used for the condition
         */
        public function delete(string $table, string|array $condition, string|array $condition_binds = "") :bool{
            $response = false;

            try {
                $condition = $this->stringifyWhere($condition, $condition_binds);

                $sql = "DELETE FROM $table WHERE $condition";
                $this->setQuery($sql);

                if($this->query($sql)){
                    $response = true;
                    $this->setStatus("Data deleted from '$table'", true);
                }
            } catch (Throwable $th) {
                $this->setStatus($th->getMessage(), true);
            }
            
            return $response;
        }

        /**
         * This function is used to parse prepared statememts
         * Effective for INSERT, UPDATE and DELETE statements
         * @param string $prepared_statement This is the prepared statement
         * @param array $values This is the list of values to be inserted
         * @return bool Returns true if successful, or false if failure
         */
        private function parse(string $prepared_statement, array $values) :bool{
            $response = false;

            try{
                $stmt = $this->prepare($prepared_statement);
                    
                if($stmt->execute($values) !== false){
                    $response = true;
                }
            }catch(Throwable $th){
                $this->setStatus($th->getMessage(), true);
            }

            return $response;
        }

        private function verifyTable($table_name) :bool{
            return boolval($this->query("SHOW TABLES LIKE '$table_name'")->num_rows);
        }

        public function placeHolders(int $columns){
            return $this->createPlaceholder($columns);
        }

        private function createPlaceholder(int $column_count): string{
            $placeholder = [];

            while($column_count-- > 0){
                $placeholder[] = "?";
            }

            return implode(", ", $placeholder);
        }

        public function __destruct(){
            $this->clearStatus();
            $this->close();
        }
    }
