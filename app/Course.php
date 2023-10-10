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
            private int $course_id = 0, private string $course_name = "",
            private string $course_alias = "", 
            private string $course_code = "", private int $instructor_id = 0
        ){
            static::$connect = $db;
            $this->set_defaults();
        }

        private function set_defaults(){
            $this->class_table = "courses";
            $this->required_keys = [ "course_name", "course_alias", "instructor_id" ];

            //check user authentication
            $this->checkForAdmin();
        }

        /**
         * Function to check user role
         * @return void validates is_admin as true if user is an admin
         */
        private function checkForAdmin() :void{
            $response  = User::auth();

            if($response){
                $this->is_admin = $response->role->is_admin;
            }else{
                $this->is_admin = false;
            }
        }

        /**
         * This function creates a new course
         * @param array $details The details to be sent into the database
         * @return bool True for a successful create and error string for a fail
         */
        public function create(array $details) :bool|string{
            $response = false;

            //authenticate user first
            if($this->is_admin){
                if($this->checkInsert($details, static::$connect)){
                    //course name could be same as course alias if alias is not given
                    $details["course_alias"] = !empty($details["course_alias"]) ? $details["course_alias"] : $details["course_name"];
                    
                    if(($response = $this->validate($details, "insert")) === true){
                        //make sure the user specified is an instructor
                        if($this->checkInstructor((int) $details["instructor_id"])){
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
            }else{
                self::$connect->setStatus("You are not authorized to create a course", true);
            }
            
            return $response;
        }

        /**
         * Function is used to update a record
         * @param array $details Details to be used to update
         * @return bool|string returns true if successful or an error string
         */
        public function update(array $details) :bool|string {
            if(($response = $this->validate($details, "update")) === true){
                // if(($current_details = self::find()))
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
         * This function is used to find the details of a user
         * @param int $user_id The id of the user
         * @return User|bool returns a user or false if not found
         */
        private function findUser(int $user_id) :User|bool{
            return User::find($user_id);
        }

        /**
         * Search for a course
         * @param string|int $course_id This is the id for the course
         * @return Course|bool returns a new course or false
         */
        public static function find(string|int $course_id) : Course|bool{
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
         * This function is used to check if the user specified is an instructor in the system
         * @param int $user_id The id of the user
         * @return bool true if the user is an instructor and false if not
         */
        private function checkInstructor(int $user_id) :bool{
            if($response = $this->findUser($user_id)){
                $response = $response->role->is_instructor;
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
            $general_keys = [
                "course_name" => ["course name","string"],
                "course_alias" => ["course alias", "string"],
                "instructor_id" => ["instructor","int"]
            ];

            $update_keys = [
                "id" => ["course id", "int"]
            ];

            //general checks
            $response = $this->check($data, $general_keys);
            
            //update checks
            if(strtolower($mode) == "update"){
                if($response === true){
                    $response = $this->check($data, $update_keys);
                }
            }

            return $response;
        }

        /**
         * This function is used to return an array format of the class details
         * @return array|string returns an array of the class attributes or a string error message
         */
        public function data() :array|string{
            $response = "No course returned";

            if($this->course_id > 0){
                $response = [
                    "id" => $this->course_id,
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
         */
        public function instructor() :Instructor|bool{
            return $this->instructor_id > 0 ? 
                Instructor::find($this->instructor_id) : false;
        }
    }