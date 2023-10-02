<?php 
    declare(strict_types=1);
    namespace App;

    class User
    {        
        /** @var Database $connect This is a static database connection */
        protected Database $connect;

        /** @var string[] $table_keys The necessary keys to be seen from input elements */
        protected array $table_keys = [
            "lname", "oname", "username", "user_role"
        ];

        /** @var bool $loggedIn takes the status of the user's login  */
        public bool $loggedIn;
        
        public function __construct(
            Database $database,
            protected int $user_id = 0, protected string $lname = "", protected string $oname = "",
            protected string $username = "", protected int $user_role = 0
        ){
            $this->user_id = $user_id;
            $this->lname = $lname;
            $this->oname = $oname;
            $this->username = $username;
            $this->user_role = $user_role;

            $this->connect = $database;
            $this->loggedIn = false;
        }

        public function login() :int|string|bool{
            $this->loggedIn = $response = false;

            //grab components
            list("username" => $username, "password" => $password) = $_POST;
            
            //verify user details
            if($this->checkUsername($username)){
                $response = $this->passwordMatch($password, $username);

                if($response === false){
                    $response = "Password does not match the username";
                    $this->connect->setStatus($response, true);
                }else{
                    $this->connect->setStatus("'$username' is signed in", true);
                    $this->loggedIn = true;
                }
            }else{
                $response = "Username '$username' not found. Please check and try again";
                $this->connect->setStatus($response, true);
            }

            return $response;
        }

        public function create(array $details) :bool|string{
            //provide a user role if one is not provided but return false if there is no user role defined by class
            if($this->user_role > 0){
                $details["user_role"] = $this->setDefault($details, "user_role", $this->user_role);
            }

            $response = $this->checkInsert($details);
            
            if($response){
                $response = $this->connect->insert("users", $details);

                //create login info
                if($response === true){
                    $password = $this->setDefault($details, "password", "Password@1");
                    $new_det["password"] = password_hash($password, PASSWORD_DEFAULT);
                    $new_det["username"] = $details["username"];
                    $new_det["user_id"] = $this->user_id = $this->connect->insert_id;

                    $response = $this->connect->insert("userlogin", $new_det);
                }
            }else{
                $response = $this->connect->status();
            }

            return $response;
        }

        protected function passwordMatch($password, $username) :bool{
            $response = false;

            $db_password = $this->connect->fetch("password","userlogin","username='$username'");
            if(is_array($db_password)){
                $response = password_verify($password, $db_password[0]["password"]);
            }

            return $response;
        }

        protected function checkInsert(array $input_array) :bool{
            $response = true;
            $keys = $this->makeKeys($input_array);

            //loop through input array for the value
            foreach($keys as $key){
                if(array_search($key, $this->table_keys) === false){
                    $response = false;
                    $this->connect->setStatus("The field named '$key' was considered an invalid key for the request", true);
                    break;
                }
            }
            
            return $response;
        }

        private function makeKeys(array $data) :array{
            return array_key_exists(0,$data) ? 
                    $data : array_keys($data);
        }

        public function logs() :array{
            return $this->connect->getLogs();
        }

        public static function find(int $user_id, string|array $columns = "", User $instance = new self) :self|bool{
            $columns = empty($columns) ? "*" : $columns;
            $search = $instance->connect->fetch("*","users","id=$user_id");

            if(is_array($search)){
                //create a new instance of the user
                $search = $instance->convertToConstruct($search);
                $user = new self(...array_values($search));
            }else{
                $search = $search !== false ? $search : "User was not found";
                $instance->connect->setStatus($search, true);
                
                $user = false;
            }

            return $user;
        }

        private function checkUsername($username) :bool{
            $response = $this->connect->fetch("id","users","username='$username'");
            $response = is_array($response[0]) ? true : false;

            return $response;
        }

        private function convertToConstruct(array $search_results) :array{
            if(is_array($search_results[0])){
                $search_results = $search_results[0];
            }

            $search_results["id"] = intval($search_results["id"]);
            $search_results["user_role"] = intval($search_results["user_role"]);

            return $search_results;
        }

        /**
         * This function is used to fetch all user data from the database
         */
        public function all() :array|string|bool{
            $column = "*"; $table = "users"; $where = "";
            if($this->user_role > 0){
                $where = "user_role={$this->user_role}";
            }

            $response = $this->connect->fetch($column, $table, $where);

            return $response;
        }

        protected function setDefault(array $array, string|int $key, $default_value){
            return empty($array[$key]) || is_null($array[$key]) ? $default_value : $array[$key];
        }

        private function validate(array $data, string $mode){

        }
    }