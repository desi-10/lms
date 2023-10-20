<?php
    declare(strict_types=1);

    namespace App;
    use App\Traits\Table;

    class Question
    {
        use Table;
        private static Database $connect;
        public const question_types = ["text","radio","checkbox"];
        public const multiple_select = ["radio","checkbox"];

        public function __construct(
            Database $db = new Database, private int $id = 0, private int $quiz_id = 0,
            private string $question_type = "", private string $question_text = "", private ?string $question_image = null
        ){
            self::$connect = $db;
            $this->set_defaults();
        }

        /**
         * This function is used to set the validation keys and also set up the class
         */
        protected function set_defaults(){
            $this->class_table = "questions";
            $this->required_keys = ["quiz_id","question_type","question_text"];
            static::$attributes = [
                "id" => "int", "quiz_id" => "int", "question_type" => "string", "question_text" => "string",
                "question_image" => "string"
            ];
        }

        /**
         * This function is used to return an array format of the class details
         * @return array|string returns an array of the class attributes or a string error message
         */
        public function data() :array|string{
            $response = "No question to be provided";

            if($this->id > 0){
                $response = [
                    "id" => $this->id,
                    "quiz_id" => $this->quiz_id,
                    "question_type" => $this->question_type,
                    "question_text" => $this->question_text,
                    "question_image" => $this->question_image,
                ];

                if(in_array($this->question_type, self::multiple_select)){
                    $response["options"] = $this->options();
                }
            }

            return $response;
        }

        /**
         * This gets the options for a specified question
         * @return array|string|false array of options or false if nothing more
         */
        private function options() :array|string|false{
            $response = false;
            if($this->id > 0){
                $response = self::$connect->fetch("content","questionoptions","question_id={$this->id}");
            }

            return $response;
        }

        /**
         * This function is used to fetch all courses from the courses table
         * @return array|string|bool array of data or error string or false
         */
        public function all() :array|string|bool{
            $response = false;

            $columns = "*"; $table = $this->class_table; $where = $this->quiz_id > 0 ? "quiz_id={$this->quiz_id}" : "";

            $response = self::$connect->fetch($columns, $table, $where);

            return $response;
        }

        /**
         * Search for a question
         * @param string|int $question_id This is the id for the question
         * @return self|bool returns a new question or false
         */
        public static function find(string|int $question_id, Database &$connection = new Database) :self|bool{
            $question = false;

            if(empty(static::$connect)){
                $instance = new static($connection);
            }

            $search = static::$connect->fetch("*","questions","id=$question_id");

            if(is_array($search)){
                $search = static::convertToConstruct($search);
                $question = new static(static::$connect, ...$search);
            }else{
                $search = $search !== false ? $search : "Question was not found";
                static::$connect->setStatus($search, true);
            }

            return $question;
        }

        /**
         * This function creates a new course
         * @param array $details The details to be sent into the database
         * @return bool True for a successful create and error string for a fail
         */
        public function create(array $details) :bool|string{
            $response = false;

            Auth::authorize(["admin", "instructor"]);

            if($this->checkInsert($details, static::$connect)){
                if(($response = $this->validate($details, "insert")) === true){
                    $response = self::$connect->insert($this->class_table, $details);
                }
            }

            return $response;
        }

        /**
         * Function is used to update a record
         * @param array $details Details to be used to update
         * @return bool|string returns true if successful or an error string
         */
        public function update(array $details) :bool|string{
            $response = false;

            Auth::authorize(["admin","instructor"]);

            if($this->checkInsert($details, self::$connect)){
                if(($response = $this->validate($details, "update")) === true){
                    //take current details
                    if($current_details = static::find((int) $details["id"])){
                        $current_details = $current_details->data();

                        $response = self::$connect->update($current_details, $details, $this->class_table, ["id"]);
                    }else{
                        $response = "Question data was not found";
                    }
                }
            }

            return $response;
        }

        /**
         * This function is used to delete a course
         * @param string|int $id The id to be deleted
         * @return bool True if successful and false if not
         */
        public function delete(string|int $id) :bool{
            Auth::authorize(["admin","instructor"]);
            $response = self::$connect->delete($this->class_table, "id=$id");

            if($response){
                self::$connect->setStatus("Question '$id' record deleted", true);
            }else{
                self::$connect->setStatus("Question '$id' record could not be deleted", true);
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
                "quiz_id" => ["quiz", "int"],
                "question_type" => ["type of question", "string"],
                "question_text" => ["content of question", "string"]
            ];

            if($mode == "update"){
                $keys["id"] = ["quiz identifier", "int"];
            }

            $response = $this->check($data, $keys);

            if($response === true)
                $response = $this->validate_fields($data);

            return $response;
        }

        /**
         * Check key details in the input string
         * @param array $details The input data to be checked
         * @return bool|string True if everything is correct or an error string
         */
        private function validate_fields(array $details) :bool|string{
            if(!in_array($details["question_type"], self::question_types)){
                return "Type of question chosen is invalid";
            }

            //check if the quiz chosen exists
            if(!Quiz::find((int) $details["quiz_id"], connection: static::$connect)){
                return "The specified quiz does not exist";
            }

            return true;
        }
    }