<?php 
    declare(strict_types=1);

    namespace App;
    use App\Traits\Table;
    use RuntimeException;

    class Program
    {
        use Table;
        private static Database $connect;
        private array $degrees = ["HND", "BTECH"];
        private string $degree;

        public function __construct(Database $db = new Database,
            private int $id = 0, private string $name = "", private string $alias = "", string $degree = ""
        ){
            self::$connect = $db;
            
            $this->setDegree($degree);
            $this->set_defaults();
        }

        private function set_defaults() :void{
            self::$attributes = [
                "id" => "int", "name" => "string", 
                "alias" => "string", "degree" => "string"
            ];
        }

        private function setDegree($value){
            if(!empty($value) && !in_array($value, $this->degrees)){
                throw new RuntimeException("Degree value is not acceptable");
            }else{
                $this->degree = $value;
            }
        }

        /**
         * This function is used to fetch all courses from the courses table
         * @return array|string|bool array of data or error string or false
         */
        public function all() :array|string|bool{
            $response = false;

            $column = "*"; $table = $this->class_table; $where = "";

            $response = static::$connect->fetch($column, $table, $where);

            return $response;
        }

        /**
         * Search for a course
         * @param string|int $course_id This is the id for the course
         * @return self|bool returns a new course or false
         */
        public static function find(string|int $program_id) :self|bool{
            $column = "*"; $where = ["id=$program_id"]; $table = "programs";

            $search = static::$connect->fetch($column, $table, $where);

            if(is_array($search)){
                $search = self::convertToConstruct($search);
                $response = new self(self::$connect, ...array_values($search));
            }else{
                $response = false;
            }

            return $response;
        }

        /**
         * Convert an array to suit the constructor
         * @param array $search_results The data to be converted
         * @return array The formated array
         */
        private static function convertToConstruct(array $search_results) :array{
            if(is_array($search_results[0])){
                $search_results = $search_results[0];
            }

            $search_results["id"] = (int) $search_results["id"];

            return $search_results;
        }

        /**
         * This function creates a new course
         * @param array $details The details to be sent into the database
         * @return bool True for a successful create and error string for a fail
         */
        public function create(array $details) :bool|string{
            $response = false;

            return $response;
        }

        /**
         * Function is used to update a record
         * @param array $details Details to be used to update
         * @return bool|string returns true if successful or an error string
         */
        public function update(array $details) :bool|string{
            $response = false;

            return $response;
        }

        /**
         * This function is used to delete a program
         * @param string|int $program_id The program id to be deleted
         * @return bool True if successful and false if not
         */
        public function delete(string|int $program_id) :bool{
            $response = self::$connect->delete($this->class_table, "id=$program_id");

            return $response;
        }

        /**
         * This function is used to return an array format of the class details
         * @return array|string returns an array of the class attributes or a string error message
         */
        public function data() :array|string{
            $response = "No results found";

            if($this->id > 0){
                $response = [
                    "id" => $this->id,
                    "name" => $this->name,
                    "alias" => $this->alias,
                    "degree" => $this->degree
                ];
            }

            return $response;
        }
    }