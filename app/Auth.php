<?php
    declare(strict_types=1);

    namespace App;

    class Auth
    {
        public static bool $admin = false;
        public static bool $student = false;
        public static bool $instructor = false;

        public static User|Instructor|Student|bool $user;

        public static string $role_name = "undefined";

        private function __construct(){
            // hide initialization
        }

        public static function auth(){
            if((static::$user = User::auth())){
                static::$admin = static::$user->role->is_admin;
                static::$student = static::$user->role->is_student;
                static::$instructor = static::$user->role->is_instructor;
                static::$role_name = static::$user->role->name;
            }else{
                self::$role_name = "not found";
            }
        }

        private static function getClass(){
            $parts = explode("/",$_SERVER["REQUEST_URI"]);

            //remove any leading string and start with api/
            if(array_search("api",$parts) !== false){
                $parts = array_splice($parts, array_search("api", $parts)+1);
            }

            return $parts[0];
        }

        /**
         * This function is used in place of checking for the specified user variables
         * Takes a string or array of allowed users to a certain part of a script
         * @param string|string[] $user_roles The authorized user roles
         * @return void
         */
        public static function authorize(string|array $user_roles) :void{
            $user_roles = (array) $user_roles;

            if(!in_array(static::$role_name, $user_roles)){
                http_response_code(401);
                $response = json_encode(["success" => false, "results" => "Current user is unauthorized for this operation"]);
                exit($response);
            }
        }

        public static function check() :bool{
            return static::$user ? true : false;
        }
    }