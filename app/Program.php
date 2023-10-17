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
            private int $id = 0, private string $name = "", private ?string $alias = null, string $degree = ""
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

            $this->required_keys = [
                "name", "alias", "degree"
            ];

            $this->class_table = "programs";
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
            if(empty(static::$connect))
                $instance = new self(new Database);
            
            $column = "*"; $where = ["id=$program_id"]; $table = "programs";

            $search = self::$connect->fetch($column, $table, $where);

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
         * This function creates a new program
         * @param array $details The details to be sent into the database
         * @return bool True for a successful create and error string for a fail
         */
        public function create(array $details) :bool|string{
            $response = false;
            Auth::authorize("admin");

            if($this->checkInsert($details, self::$connect)){
                if(($response = $this->validate($details, "insert")) === true){
                    //check if the degree is valid
                    if(in_array(strtoupper($details["degree"]), $this->degrees)){
                        if(!$this->programExists($details)){
                            //parse the data into the database
                            $response = self::$connect->insert("programs", $details);
                        }else{
                            http_response_code(422);
                            $response = "This program has already been added";
                            self::$connect->setStatus($response, true);
                        }
                    }else{
                        http_response_code(422);
                        $response = "Your degree '{$details['degree']}' specified is not valid";
                        self::$connect->setStatus($response, true);
                    }
                }else{
                    http_response_code(422);
                    self::$connect->setStatus($response, true);
                }
            }else{
                http_response_code(422);
                $response = static::$connect->status();
            }
            
            return $response;
        }

        /**
         * This is used to check if a record is already inserted or not
         * @param string[] $details the details to be used for checking
         * @return bool true if found and false if not found
         */
        private function programExists(array $details){
            //check using the id if its in
            if(!empty($details["id"])){
                $response = self::$connect->fetch("*", $this->class_table, "id={$details['id']}");
            }else{
                $response = self::$connect->fetch("*", $this->class_table, 
                    ["name='{$details['name']}'", "degree='{$details['degree']}'"], "AND");
            }

            return is_array($response) ? true : false;
        }

        /**
         * Function is used to update a record
         * @param array $details Details to be used to update
         * @return bool|string returns true if successful or an error string
         */
        public function update(array $details) :bool|string{
            $response = false;

            Auth::authorize(["admin"]);
            if(($response = $this->validate($details, "update")) === true){
                //grab current details
                if($current = self::find($details["id"])){
                    $current = $current->data();

                    //update programs table
                    if($this->programExists($details)){
                        if($response = self::$connect->update($current, $details, $this->class_table, ["id"]) === true){
                            $response = "Program was updated";
                        }
                    }
                }else{
                    $response = "Course could not be found";
                }
            }

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

        /**
         * This function is used to validate input data
         * @param array $data The data to be processed
         * @param string $mode The mode of the request
         * @return string|bool returns true if everything is fine or string of error
         */
        private function validate(array $data, string $mode) :bool|string{
            $general = [
                "name" => ["program name", "string"],
                "degree" => ["degree", "string"]
            ];

            if($mode == "update"){
                $general["id"] = ["program id", "int"];
            }

            $response = $this->check($data, $general);

            return $response;
        }
    }