<?php
    declare(strict_types= 1);

    namespace App;

use App\Traits\Table;

    class Grade
    {
        use Table;
        private static Database $connect;

        private const work_type = ["quiz", "assignment"];

        public function __construct(
            Database $db = new Database, private int $id = 0, private string $work_type = "",
            private int $work_id = 0, private int $student_id = 0, private float $score = 0
        ){
            self::$connect = $db;
            $this->set_defaults();
        }

        /**
         * This function is used to set the validation keys and also set up the class
         */
        protected function set_defaults(){
            $this->class_table = "grades";
            $this->required_keys = [
                "work_type", "work_id", "student_id", "score"
            ];
            static::$attributes = [
                "id" => "int", "work_type" => "string", "work_id" => "int",
                "student_id" => "int", "score" => "float"
            ];
        }

        /**
         * This function is used to return an array format of the class details
         * @return array|string returns an array of the class attributes or a string error message
         */
        public function data() :array|string{
            $response = "No grade available";

            if($this->id > 0){
                $response = [
                    "id" => $this->id,
                    "work_type"=> $this->work_type,
                    "work_id"=> $this->work_id,
                    "student_id"=> $this->student_id,
                    "score"=> $this->score
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

            $where = [];

            if($this->work_id > 0){
                $where[] = "work_id={$this->work_id}";
            }
            if($this->student_id > 0){
                $where[] = "student_id={$this->student_id}";
            }
            if(!empty($this->work_type)){
                $where[] = "work_type='{$this->work_type}'";
            }

            $response = self::$connect->fetch("*", $this->class_table, $where, "AND");

            return $response;
        }

        /**
         * Search for a grade
         * @param string|int $grade_id This is the id for the grade
         * @return self|bool returns a new grade or false
         */
        public static function find(string|int $grade_id) :self|bool{
            $response = false;

            if(empty(static::$connect))
                $instance = new self;

            $search = self::$connect->fetch("*", "grades", "id=$grade_id");

            if(is_array($search)){
                $search = self::convertToConstruct($search);
                $response = new self(self::$connect, ...$search);
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

            Auth::authorize(["admin","instructor"]);

            if($this->checkInsert($details, self::$connect)){
                $response = $this->validate($details, "insert");

                if($response === true){
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
                $response = $this->validate($details, "update");

                if($response === true){
                    if($current_details = self::find($details["id"])){
                        $current_details = $current_details->data();

                        $response = self::$connect->update($current_details, $details, $this->class_table, ["id"]);
                    }else{
                        $response = "Specified grade does not exist";
                    }
                }
            }

            return $response;
        }

        /**
         * This function is used to delete a stored grade
         * @param string|int $id The id to be deleted
         * @return bool True if successful and false if not
         */
        public function delete(string|int $id) :bool{
            $response = self::$connect->delete($this->class_table, "id=$id");

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
                "work_type" => ["grade type", "string"],
                "work" => ["quiz/assignment", "int"],
                "student_id" => ["student", "int"],
                "score" => ["score", "float"],
            ];

            if($mode == "update"){
                $keys["id"] = ["grade id", "int"];
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
            if(!in_array($details["work_type"], self::work_type)){
                return "The specified grade is invalid";
            }

            if($details["work_type"] == "quiz" && !Quiz::find($details["work_id"])){
                return "The specified quiz does not exist";
            }elseif($details["work_type"] == "assignment" && Assignment::find($details['work_type'])){
                return "The specified assignment does not exist";
            }

            if(!Student::find($details["student_id"])){
                return "The specified student does not exist or is not a student";
            }

            return true;
        }

        /**
         * Get the student for this course
         * @return Student|false
         */
        public function student() :Student|false{
            return $this->student_id > 0 ? Student::find($this->student_id) : false;
        }

        /**
         * Get the quiz this grade belongs to
         * @return Quiz|false
         */
        public function quiz() :Quiz|false{
            return $this->work_type == "quiz" && $this->work_id > 0 ? Quiz::find($this->work_id) : false;
        }

        /**
         * Get the assignment this grade belongs to
         * @return Assignment|false
         */
        public function assignment() :Assignment|false{
            return $this->work_type == "assignment" && $this->work_id > 0 ? Assignment::find($this->work_id) : false;
        }
    }