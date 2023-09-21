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

        public function __construct(
            private string $host = "localhost", private string $username  = "root",  
            private string $password = "", private string $database = "elms"
        ){
            $this->$host = $host; 
            $this->$username = $username; 
            $this->password = $password; 
            $this->database = $database;

            parent::__construct($this->$host, $this->$username, $this->password, $this->database);

            if($this->connect_errno){
                $this->status = "Mysqli Connection Error: ".$this->connect_errno;
                throw new RuntimeException("Mysqli Connection Error: ".$this->connect_errno);
            }else{
                $this->setStatus("success");
            }
        }

        public function status(){
            return $this->status ?? $this->status1;
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
         * 
         * @return array|string|bool an array of data or a single data or error message
         */
        public function fetch(array|string $columns, array|string $table_name, 
            array|string $where = "", string|array $where_binds = ""){
            $response = false;

            try{
                $columns = $this->stringifyColumn($columns);
                $where = $this->stringifyWhere($where, $where_binds);
                $table_name = $this->stringifyTable($table_name);

                $sql = "$columns $table_name $where";

                $response = $sql;
            }catch(Throwable $th){
                $response = false;
                $this->status = "Error: ".$th->getMessage();
                $this->status = "Error: ".$th->getTraceAsString();
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
                if(!is_array($tables[0])){
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

        private function setStatus(string $message){
            $this->status = $message;
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

            $new_table .= " $lhs ON $rhs";
        }

        public function __destruct(){
            $this->clearStatus();
            $this->close();
        }
    }
