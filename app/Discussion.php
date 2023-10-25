<?php 
    declare(strict_types=1);

    namespace App;
    use App\Traits\Table;

    class Discussion
    {
        use Table;
        private static Database $connect;
        private string $post_time;

        public function __construct(
            Database $db = new Database, private int $id = 0, private int $course_id = 0,
            private int $user_id = 0, private string $content = ""
        ){
            self::$connect = $db;
            $this->set_defaults();
        }

        /**
         * This function is used to set the validation keys and also set up the class
         */
        protected function set_defaults(){
            $this->class_table = "discussions";
            $this->required_keys = [
                "course_id","user_id","content"
            ];
            static::$attributes = [
                "id" => "int", "course_id" => "int", "user_id" => "int",
                "content" => "string", "post_time" => "string"
            ];

            $this->post_time = date("Y-m-d H:i:s");
        }

        /**
         * This function is used to return an array format of the class details
         * @return array|string returns an array of the class attributes or a string error message
         */
        public function data() :array|string{
            $response = "No discussions at the moment";

            if($this > 0){
                $response = [
                    "id" => $this->id,
                    "course_id"=> $this->course_id,
                    "user_id"=> $this->user_id,
                    "content"=> $this->content,
                    "post_time"=> $this->post_time
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

            if($this->course_id > 0){
                $where[] = "course_id={$this->course_id}";
            }

            if($this->user_id > 0){
                $where[] = "user_id={$this->user_id}";
            }

            self::$connect->fetch("*", $this->class_table, $where, "AND");

            return $response;
        }

        /**
         * Search for a discussion
         * @param string|int $discussion_id This is the id for the discussion
         * @return self|bool returns a new discussion or false
         */
        public static function find(string|int $discussion_id, Database &$connection = new Database) :self|bool{
            $response = false;

            if(empty(static::$connect))
                $instance = new self($connection);
            
            $search = static::$connect->fetch("*", "discussions" , "id=$discussion_id");

            if(is_array($search)){
                $search = self::convertToConstruct($search);
                $response = new self(self::$connect, ...$search);
            }else{
                $search = false;
            }

            return $response;
        }

        /**
         * This function creates a new discussion
         * @param array $details The details to be sent into the database
         * @return bool True for a successful create and error string for a fail
         */
        public function create(array $details) :bool|string{
            $response = false;

            if($this->checkInsert($details, self::$connect)){
                $response = $this->validate($details, "insert");

                if($response === true){
                    //add the post time
                    $details["post_time"] = $details["post_time"] ?? $this->post_time;

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

            if($this->checkInsert($details, self::$connect)){
                $response = $this->validate($details, "update");

                if($response === true){
                    if($current_details = self::find($details["id"])){
                        $current_details = $current_details->data();

                        $response = self::$connect->update($current_details, $details, $this->class_table, ["id"]);
                    }else{
                        $response = "Requested discussion cannot be found";
                    }
                }
            }

            return $response;
        }

        /**
         * This function is used to delete a discussion
         * @param string|int $id The id to be deleted
         * @return bool True if successful and false if not
         */
        public function delete(string|int $id) :bool{
            Auth::authorize("admin");
            $response = self::$connect->delete($this->class_table, ["id=$id"]);

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
                "course_id" => ["course", "int"],
                "user_id" => ["user", "int"],
                "content" => ["discussion content", "string"]
            ];

            if($mode == "update"){
                $keys["id"] = ["current discussion", "int"];
                $keys["post_time"] = ["post time", "string"];
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
            if(!Course::find($details["course_id"])){
                return "The selected course could not be found";
            }

            if(!User::find($details["user_id"])){
                return "The requested user could not be found";
            }

            if(!empty($details["post_time"]) && !strtotime($details["post_time"])){
                return "The time for the posted content is invalid";
            }

            return true;
        }

        /**
         * Get the requested user details
         * @return User|false Returns a user or false if fail
         */
        public function user() :User|false{
            return $this->user_id > 0 ? User::find($this->user_id, connection: self::$connect) : false;
        }

        /**
         * Get the requested course
         * @return Course|false Returns the course or false if fail
         */
        public function course() :Course|false{
            return $this->course_id > 0 ? Course::find($this->course_id, connection: self::$connect) : false;
        }
    }