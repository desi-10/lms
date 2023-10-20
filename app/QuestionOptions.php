<?php 
    declare(strict_types=1);
    
    namespace App;
    use App\Traits\Table;

    class QuestionOptions
    {
        use Table;
        private static Database $connect;

        public function __construct(Database $db = new Database,
            private int $id = 0, private int $question_id = 0, private string $content = ""
        ){
            self::$connect = $db;
            $this->set_defaults();
        }

        /**
         * This function is used to set the validation keys and also set up the class
         */
        protected function set_defaults(){
            $this->class_table = "questionoptions";
            $this->required_keys = ["question_id","content"];
            static::$attributes = [
                "id" => "int", "question_id" => "int", "content" => "string"
            ];
        }

        /**
         * This function is used to return an array format of the class details
         * @return array|string returns an array of the class attributes or a string error message
         */
        public function data() :array|string{
            $response = "No options for this question";

            if($this->question_id > 0){
                $response = [
                    "id" => $this->id,
                    "question_id" => $this->question_id,
                    "content" => $this->content
                ];
            }

            return $response;
        }

        /**
         * This function is used to fetch all courses from the courses table
         * @return array|string|bool array of data or error string or false
         */
        public function all() :array|string|bool{
            $response = false;

            $where = $this->question_id > 0 ? "question_id={$this->question_id}" : "";

            $response = self::$connect->fetch("*",$this->class_table, $where);

            return $response;
        }

        /**
         * Get the question this option belongs to
         * @return array|false array result of the question or a false
         */
        public function question() :array|false{
            $response = false;

            if($this->question_id > 0 && $response = Question::find($this->question_id, connection: static::$connect)){
                $response = $response->data();
            }

            return $response;
        }

        /**
         * Search for a option
         * @param string|int $option_id This is the id for the option
         * @return self|bool returns a new option or false
         */
        public static function find(string|int $option_id, Database &$connection = new Database) :self|bool{
            $response = false;

            if(empty(static::$connect))
                $instance = new static($connection);
            
            $search = static::$connect->fetch("*","questionoptions", "id=$option_id");

            if(is_array($search)){
                $search = static::convertToConstruct($search);
                $response = new static(static::$connect, ...$search);
            }

            return $response;
        }

        /**
         * This function creates a new course
         * @param array $details The details to be sent into the database
         * @return bool True for a successful create and error string for a fail
         */
        public function create(array $details) :bool|string{
            $response = false;

            if($this->checkInsert($details, self::$connect)){
                if(($response = $this->validate($details, "insert")) === true){
                    $response = self::$connect->insert($this->class_table, $details);
                }
            }

            if($response !== true && $response !== false){
                self::$connect->setStatus($response, true);
            }

            return $response;
        }

        /**
         * Function is used to update a record
         * @param array $details Details to be used to update
         * @return bool|string returns true if successful or an error string
         */
        public function update(array $details) :bool|string{
            Auth::authorize(["admin","instructor"]);
            
            $response = false;

            if($this->checkInsert($details, static::$connect)){
                if(($response = $this->validate($details, "update")) === true){
                    //grab current details
                    if($current_details = static::find($details["id"])){
                        $current_details = $current_details->data();

                        $response = self::$connect->update($current_details, $details, $this->class_table, ["id"]);
                    }else{
                        $response = "Question option data was not found. Update could not be carried out";
                    }
                }
            }

            return $response;
        }

        /**
         * This function is used to delete an option
         * @param string|int $id The id to be deleted
         * @return bool True if successful and false if not
         */
        public function delete(string|int $id) :bool{
            Auth::authorize(["admin","instructor"]);

            $response = self::$connect->delete($this->class_table, "id=$id");

            if($response){
                self::$connect->setStatus("Question option '$id' record deleted", true);
            }else{
                self::$connect->setStatus("Question option '$id' record could not be deleted", true);
            }

            return $response;
        }

        /**
         * This function is used to validate input data
         * @param array $data The data to be processed
         * @param string $mode The mode of the request
         * @return string|bool returns true if everything is fine or string of error
        */
        protected function validate(array $data, string $mode) :string|bool{
            $response = true;

            $keys = [
                "question_id" => ["question", "int"],
                "content" => ["option's content", "string"]
            ];

            if($mode == "update"){
                $keys["id"] = ["option identifier", "int"];
            }

            $response = $this->check($data, $keys);

            if($response === true){
                $response = $this->validate_fields($data);
            }

            return $response;
        }

        /**
         * Check key details in the input string
         * @param array $details The input data to be checked
         * @return bool|string True if everything is correct or an error string
         */
        private function validate_fields(array $details) :bool|string{
            if(!Question::find($details["question_id"], static::$connect)){
                return "Question provided is invalid or does not exist";
            }

            return true;
        }
    }