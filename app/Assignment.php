<?php
    declare(strict_types= 1);

    namespace App;
    use App\Traits\Table;

    class Assignment
    {
        use Table;
        private static Database $connect;

        private const program_levels = [100, 200, 300, 400];

        public function __construct(
            Database $db = new Database, private int $id = 0, private int $course_id = 0, private string $title = "",
            private string $description = "", private int $instructor_id = 0, private int $program_id = 0, private int $program_level = 0, 
            private ?string $material_ids = null, private string $start_date = "", private string $end_date = "", private int|bool $active = -1
        ){
            self::$connect = $db;
            $this->set_defaults();
        }

        /**
         * This function is used to set the validation keys and also set up the class
         */
        protected function set_defaults(){
            $this->class_table = "assignments";
            $this->required_keys = [
                "course_id", "title", "description", "instructor_id", "program_id",
                "program_level", "end_date"
            ];
            static::$attributes = [
                "id" => "int", "course_id" => "int", "title" => "string", "description" => "string",
                "instructor_id" => "int", "program_id" => "int", "program_level" => "int", "matrial_ids" => "string",
                "start_date" => "string", "end_date" => "string", "active" => "bool"
            ];
        }

        /**
         * This function is used to return an array format of the class details
         * @return array|string returns an array of the class attributes or a string error message
         */
        public function data() :array|string{
            $response = "No assignment detail to display";

            if($this->id > 0){
                $response = [
                    "id" => $this->id,
                    "course_id" => $this->course_id,
                    "title"=> $this->title,
                    "description" => $this->description,
                    "instructor_id" => $this->instructor_id,
                    "program_id" => $this->program_id,
                    "program_level" => $this->program_level,
                    "material_ids" => $this->material_ids,
                    "start_date" => $this->start_date,
                    "end_date" => $this->end_date,
                    "active" => $this->active,
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

            if($this->instructor_id){
                $where[] = "instructor_id={$this->instructor_id}";
            }

            if($this->course_id){
                $where[] = "course_id={$this->course_id}";
            }

            if($this->program_id){
                $where[] = "program_id={$this->program_id}";
            }

            if($this->program_level){
                $where[] = "program_level={$this->program_level}";
            }

            if($this->active > -1){
                $where[] = "active={$this->active}";
            }

            $response = self::$connect->fetch("*", $this->class_table, $where, "AND");
            
            return $response;
        }

        /**
         * Search for a assignment
         * @param string|int $assignment_id This is the id for the assignment
         * @return self|bool returns a new assignment or false
         */
        public static function find(string|int $assignment_id, Database &$connection = new Database) :self|bool{
            $response = false;

            if(empty(static::$connect))
                $instance = new self($connection);
            
            $search = static::$connect->fetch("*", "assignments","id=$assignment_id");

            if(is_array($search)){
                $search = static::convertToConstruct($search);
                $response = new static(static::$connect, ...$search);
            }

            return $response;
        }

        /**
         * This function creates a new assignment
         * @param array $details The details to be sent into the database
         * @return bool True for a successful create and error string for a fail
         */
        public function create(array $details) :bool|string{
            $response = false;

            Auth::authorize(["admin","instructor"]);

            if($this->checkInsert($details, self::$connect)){
                //set the start date to today if its not set
                $details["start_date"] = $details["start_date"] ?? date("Y-m-d h:i:s");

                $response = $this->validate($details, "insert");
                if($response === true){
                    //mark the assignment as active if the start time is today
                    if(!isset($details["active"])){
                        if(date("Y-m-d", strtotime($details["start_date"])) == date("Y-m-d")){
                            $details["active"] = true;
                        }else{
                            $details["active"] = false;
                        }
                    }
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
                        $response = "Assignment previous data was not found";
                    }
                }
            }

            return $response;
        }

        /**
         * This function is used to delete an assignment
         * @param string|int $id The id to be deleted
         * @return bool True if successful and false if not
         */
        public function delete(string|int $id) :bool{
            $response = self::$connect->delete($this->class_table, ["id"=> $id]);

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
                "course_id" => ["course", "int"],
                "title" => ["assignment title", "string"],
                "description" => ["assignment description", "string"],
                "instructor_id" => ["instructor", "int"],
                "program_id" => ["program", "int"],
                "program_level" => ["program level", "int"],
                "end_date" => ["final submission date", "string"],
            ];

            if($mode == "update"){
                $keys["id"] = ["assignment id", "int"];
                $keys["start_date"] = ["submission start date", "string"];
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
        private function validate_fields(array &$details) :bool|string{
            //make sure course is valid
            if(!Course::find($details["course_id"], static::$connect)){
                return "The specified course is invalid or not registered in the database";
            }

            //verify the provided instructor
            if(!Instructor::find($details["instructor_id"], connection: static::$connect)){
                return "The specified user is not registered as an instructor";
            }

            //verify the selected program
            if(!Program::find($details["program_id"], static::$connect)){
                return "The specified program is invalid or could not be found";
            }

            //verify that the program levels are valid
            if(!in_array((int) $details["program_level"], self::program_levels)){
                return "The specified program level is invalid";
            }

            //check the date format of the end period
            if(!$this->checkDate($details["start_date"])){
                return "Assignment beginning date is not a valid date";
            }elseif(!$this->checkDate($details["end_date"], false)){
                return "Final submission date is not a valid date";
            }

            //make sure start date is not higher than end date
            if(strtotime($details["start_date"]) >= strtotime($details["end_date"])){
                return "Your final submission date cannot be the past";
            }
            return true;
        }

        /**
         * This gets the course this assignment is assigned to
         * @return Course|false The course details or false
         */
        public function course() :Course|false{
            return $this->course_id > 0 ? Course::find($this->course_id, static::$connect) : false;
        }

        /**
         * This gets the instructor this assignment is assigned to
         * @return Instructor|false The instructor details or false
         */
        public function instructor() :Instructor|false{
            return $this->instructor_id > 0 ? Instructor::find($this->instructor_id, connection: static::$connect) : false;
        }

        /**
         * This gets the program this assignment is assigned to
         * @return Program|false The program details or false
         */
        public function program() :Program|false{
            return $this->program_id > 0 ? Program::find($this->course_id, static::$connect) : false;
        }

        /**
         * This returns the grades of the specified quiz
         * @return array|false Array of grades or false if none
         */
        public function grades() :array|false{
            $grade = new Grade(self::$connect, work_type: "assignment", work_id: $this->id);
            $grades = $grade->all();

            return is_array($grades) ? $grades : false;
        }
    }