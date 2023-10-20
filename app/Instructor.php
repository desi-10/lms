<?php 
    declare(strict_types = 1);

    namespace App;

    class Instructor extends User
    {
        public function __construct(Database $database, 
            int $user_id = 0, string $lname = '', 
            string $oname = '', string $username = '', int $user_role = 2){
                parent::__construct($database, $user_id, $lname, $oname, $username, $user_role);
        }

        /**
         * Returns all courses for the instructor
         * @return array|bool A list of courses or a bool data
         */
        public function courses() :array|string|bool{
            $response = false;

            $response = new Course(static::$connect, instructor_id: $this->user_id);
            $response = $response->all();

            return $response;
        }

        /**
         * This returns all the active assignments of the user
         * @return array|string|bool a list of active assignments
         */
        public function assignments() :array|string|bool{
            $response = false;

            // Auth::authorize(["admin", "instructor"]);
            
            $response = new Assignment(static::$connect, active: true, instructor_id: $this->user_id);
            $response = $response->all();

            return $response;
        }
    }