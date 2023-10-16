<?php
    declare(strict_types=1);

    namespace App;
    use App\Traits\Table;
use DateTime;

    class Quiz
    {
        use Table;
        private static Database $connect;
        private const program_levels = [100, 200, 300, 400];


        public function __construct(Database $db = new Database,
            private int $id = 0, private string $title = "", private string $description = "", private int $course_id = 0,
            private int $program_id = 0, private int $program_level = 0, private int $instructor_id = 0,
            private string $start_date = "", private string $end_time = "", private bool $active = false
        ){
            self::$connect = $db;
            $this->set_defaults();
        }

        /**
         * This function is used to set the validation keys and also set up the class
         */
        protected function set_defaults(){
            $this->class_table = "quizzes";
            $this->required_keys = [
                "instructor_id", "course_id", "title", "description", "program_id",
                "program_level", "start_date", "end_time"
            ];
            static::$attributes = [
                "id" => "int", "course_id" => "int", "title" => "string", "description" => "string",
                "instructor_id" => "int", "program_id" => "string", "program_level" => "int", "start_date" => "string",
                "end_date" => "string", "active" => "bool"
            ];

            $this->start_date = date("Y-m-d h:i:s", strtotime("1 hour"));
            $this->end_time = date("Y-m-d h:i:s", strtotime("1hour 30mins"));
        }

        /**
         * This function is used to return an array format of the class details
         * @return array|string returns an array of the class attributes or a string error message
         */
        public function data() :array|string{
            $response = "No quiz data to show";

            if($this->id > 0){
                $response = [
                    "id" => $this->id,
                    "instructor_id" => $this->instructor_id,
                    "course_id" => $this->course_id,
                    "title" => $this->title,
                    "description" => $this->description,
                    "program_id" => $this->program_id,
                    "program_level" => $this->program_level,
                    "start_date" => $this->start_date,
                    "end_time" => $this->end_time,
                    "active" => $this->active,
                ];
            }

            return $response;
        }

        /**
         * This function is used to fetch all quizzes from the quizzes table
         * @return array|string|bool array of data or error string or false
         */
        public function all() :array|string|bool{
            $response = static::$connect->fetch("*",$this->class_table);

            return $response;
        }

        /**
         * Search for a quiz
         * @param string|int $quiz_id This is the id for the quiz
         * @return self|bool returns a new quiz or false
         */
        public static function find(string|int $quiz_id) :self|bool{
            $response = false;

            $instance = new static(new Database);

            $search = static::$connect->fetch("*",$instance->class_table, "id=$quiz_id");

            if(is_array($search)){
                //create a new instance of the class
                $search = self::convertToConstruct($search);
                $response = new self(static::$connect, ...$search);
            }else{
                $response = false;
            }

            return $response;
        }

        /**
         * This generates the instructor who set the quiz
         * @return Instructor|false The details of the instructor or false if none
         */
        public function instructor() :Instructor|false{
            return $this->instructor_id > 0 ? Instructor::find($this->instructor_id) : false;
        }

        /**
         * This generates the instructor who set the quiz
         * @return Course|false The details of the instructor or false if none
         */
        public function course() :Course|false{
            return $this->course_id > 0 ? Course::find($this->course_id) : false;
        }

        /**
         * This generates the instructor who set the quiz
         * @return Program|false The details of the instructor or false if none
         */
        public function program() :Program|false{
            return $this->program_id > 0 ? Program::find($this->program_id) : false;
        }

        /**
         * This function creates a new course
         * @param array $details The details to be sent into the database
         * @return bool True for a successful create and error string for a fail
         */
        public function create(array $details) :bool|string{
            $response = false;
            
            Auth::authorize(["admin","instructor"]);

            //add current date and time as the default start date if its not set
            $details["start_date"] = $this->setDefault($details, "start_date", $this->start_date);

            if($this->checkInsert($details, self::$connect)){
                if(($response = $this->validate($details, "insert")) === true){
                    $response = static::$connect->insert($this->class_table, $details);
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
            $response = false;

            if($this->checkInsert($details, static::$connect)){
                if($this->validate($details, "update")){
                    //grab current details
                    if($current_details = self::find($details["id"])){
                        $current_details = $current_details->data();

                        $response = self::$connect->update($current_details, $details, $this->class_table, ["id"]);
                    }else{
                        $response = "Quiz data was not found";
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
            $response = self::$connect->delete($this->class_table, "id=$id");

            if($response){
                self::$connect->setStatus("Quiz '$id' record deleted", true);
            }else{
                self::$connect->setStatus("Quiz '$id' record could not be deleted", true);
            }

            return $response;
        }

        /**
         * This function is used to validate input data
         * @param array $data The data to be processed
         * @param string $mode The mode of the request
         * @return string|bool returns true if everything is fine or string of error
         */
        protected function validate(array &$data, string $mode) :string|bool{
            $response = true;

            $keys = [
                "instructor_id" => ["instructor", "int"],
                "course_id" => ["course", "int"],
                "title" => ["quiz title", "string"],
                "description" => ["quiz description", "string"],
                "program_id" => ["program", "int"],
                "program_level" => ["program level", "int"],
                "end_time" => ["end period", "string"],
            ];

            if($mode == "update"){
                $keys["id"] = ["item id", "int"];
            }

            $response = $this->check($data, $keys);

            //check if the instructor and courses provided are valid 
            if($response === true)
                $response = $this->validate_fields($data);

            return $response;
        }

        /**
         * Check key details in the input string
         * @param array $details The input data to be checked
         * @return bool|string True if everything is correct or an error string
         */
        private function validate_fields(array &$details) :bool|string{
            //check if user provided is an instructor
            $instructor = Instructor::find((int) $details["instructor_id"]);

            if(!$instructor){
                return "Instructor provided does not exist";
            }elseif($instructor->getRole() != 2){
                return "User specified is not an instructor";
            }

            //check if the selected course is valid
            if(!Course::find((int) $details["course_id"])){
                return "Course provided does not exist";
            }

            //check if the program is valid
            if(!Program::find($details["program_id"])){
                return "Program chosen does not exist";
            }

            //check the program level
            if(!in_array($details["program_level"], self::program_levels))
                return "Program level provided is invalid";
            
            //check the date format of the end period
            if(!$this->checkDate($details["start_date"])){
                return "Start date is not valid";
            }elseif(!$this->checkDate($details["end_time"], false)){
                return "End date is not valid";
            }

            //make sure start date is not higher than end date
            if(strtotime($details["start_date"]) >= strtotime($details["end_time"])){
                return "Your start period must take a lower date";
            }

            //return true if there was no failure
            return true;
        }

        /**
         * Check if a date string is in the right format
         * @param string $value The date value
         * @param bool $start This tells if its the start or end date
         * @return bool formats date on true if its a valid date or return false if otherwise
         */
        private function checkDate(string &$value, bool $start = true) :bool{
            $hasTime = preg_match('/\b(?:\d{1,2}:){1,2}\d{1,2}\b/', $value);

            if(strtotime($value)){
                //add time (+1hr) if it does not have one
                if(!$hasTime){
                    if($start){
                        $value .= date(" H:i:s");
                    }else{
                        $value .= date(" H:i:s", strtotime("1 hour"));
                    }
                }

                //format date in datetime format
                $value = date("Y-m-d H:i:s", strtotime($value));
                return true;
            }else{
                return false;
            }
        }
    }