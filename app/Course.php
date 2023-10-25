<?php 
    declare(strict_types=1);
    namespace App;

    use App\Traits\Table;

    class Course
    {
        use Table;

        /** @var Database $connect The database connection object */
        private static Database $connect;

        /** @var bool $is_admin This is used at admin privilege areas */
        private bool $is_admin;

        public function __construct(private Database $db, 
            private int $id = 0, private string $course_name = "",
            private string $course_alias = "", 
            private string $course_code = "", private int $instructor_id = 0, private int $program_id = 0 
        ){
            static::$connect = $db;
            $this->set_defaults();
        }

        private function set_defaults(){
            $this->class_table = "courses";
            $this->required_keys = [ "course_name", "course_alias", "instructor_id", "course_code", "program_id" ];

            //class attributes
            static::$attributes = [
                "id" => "int", "course_name" => "string",
                "course_alias" => "string", "course_code" => "string",
                "instructor_id" => "int", "program_id" => "int"
            ];
        }

        /**
         * This function creates a new course
         * @param array $details The details to be sent into the database
         * @return bool True for a successful create and error string for a fail
         */
        public function create(array $details) :bool|string{
            $response = false;

            //authenticate user first
            Auth::authorize(["admin","instructor"]);

            if($this->checkInsert($details, static::$connect)){
                //course name could be same as course alias if alias is not given
                $details["course_alias"] = $this->setDefault($details, "course_alias", $details["course_name"]);
                
                if(($response = $this->validate($details, "insert")) === true){
                    //make sure the user specified is an instructor
                    if(Instructor::find((int) $details["instructor_id"], connection: static::$connect)){
                        $response = self::$connect->insert($this->class_table, $details);
                    }else{
                        $response = "Selected user is not an instructor";
                        self::$connect->setStatus($response, true);
                    }
                }else{
                    http_response_code(422);
                    self::$connect->setStatus((string) $response, true);
                }
            }
        
            
            return $response;
        }

        /**
         * Function is used to update a record
         * @param array $details Details to be used to update
         * @return bool|string returns true if successful or an error string
         */
        public function update(array $details) :bool|string {
            //make alias the same as the course name if its empty
            $details["course_alias"] = $this->setDefault($details, "course_alias", $details["course_name"]);
            
            if(($response = $this->validate($details, "update")) === true){
                if(($current_details = self::find($details["id"]))){
                    var_dump($current_details);
                }else{
                    $response = "Course was not found";
                    self::$connect->setStatus($response, true);
                }
            }

            return $response;
        }

        /**
         * This function is used to delete a course
         * @param string|int $course_id The course id to be deleted
         * @return bool True if successful and false if not
         */
        public function delete(string|int $course_id) :bool{
            $response = self::$connect->delete($this->class_table, "id=$course_id");

            if(!$response){
                self::$connect->setStatus("Course '$course_id' could not be deleted", true);
            }else{
                self::$connect->setStatus("Course '$course_id' has been deleted", true);
            }

            return $response;
        }

        /**
         * Search for a course
         * @param string|int $course_id This is the id for the course
         * @return Course|bool returns a new course or false
         */
        public static function find(string|int $course_id, Database &$connection = new Database) : Course|bool{
            if(empty(static::$connect))
                $instance = new static($connection);
            
            $column = "*"; $where = ["id=$course_id"]; $table = "courses";

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
         * This function is used to validated the input data
         * @param array $data The data to be validated
         * @param string $mode The mode of validation
         * @return bool returns true if there are no errors 
         */
        private function validate(array $data, string $mode) :bool|string{
            $response = false;
            $keys = [
                "course_name" => ["course name","string"],
                "instructor_id" => ["instructor","int"],
                "program_id" => ["program name","int"],
                "course_code" => ["course code","string"],
            ];
            
            //update checks
            if(strtolower($mode) == "update"){
                $keys["id"] = ["course id", "int"];
            }

            //general checks
            $response = $this->check($data, $keys);

            return $response;
        }

        /**
         * This function is used to return an array format of the class details
         * @return array|string returns an array of the class attributes or a string error message
         */
        public function data() :array|string{
            $response = "No course returned";

            if($this->id > 0){
                $response = [
                    "id" => $this->id,
                    "course_name" => $this->course_name,
                    "course_alias" => $this->course_alias,
                    "instructor_id" => $this->instructor_id
                ];
            }

            return $response;
        }

        /**
         * This function is used to fetch all courses from the courses table
         * @return array|string|bool array of data or error string or false
         */
        public function all() :array|string|bool{
            $column = "*"; $table = $this->class_table; $where = "";

            //for instructors, get courses related to them alone
            if($this->instructor_id > 0){
                $where = "instructor_id={$this->instructor_id}";
            }

            $response = static::$connect->fetch($column, $table, $where);

            return $response;
        }

        /**
         * This is used to grab the full details of the instructor of this course
         * @return Instructor|bool returns an instructor object or false if not found
         */
        public function instructor() :Instructor|bool{
            return $this->instructor_id > 0 ? 
                Instructor::find($this->instructor_id, connection: static::$connect) : false;
        }

        /**
         * Grab the full details of the programs offering the course
         * @return Program|bool returns the program details of the course
         */
        public function program() :Program|bool{
            return $this->program_id > 0 ? 
                Program::find($this->program_id, connection: static::$connect) : false;
        }

        /**
         * Grab the full details of the assignments offered in this course
         * @return array|false returns all assignments or false if none
         */
        public function assignments() :array|false{
            $assignment = new Assignment(self::$connect, course_id: $this->id);
            $response = $assignment->all();

            return is_array($response) ? $response : false;
        }

        /**
         * Grab all the discussions held in this course
         * @return array|false returns all discussions in the course
         */
        public function discussions() :array|false{
            $discussion = new Discussion(self::$connect, course_id: $this->id);
            $response = $discussion->all();

            return is_array($response) ? $response : false;
        }

        /**
         * This returns an array of course materials
         * @return array|false Array or course materials or false if none
         */
        public function materials() :array|false{
            $material = new CourseMaterials(self::$connect, course_id: $this->id);
            $response = $material->all();

            return is_array($response) ? $response : false;
        }
    }