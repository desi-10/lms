<?php 
    declare(strict_types=1);
    namespace App;

    class User
    {
        /** @var Database $connect This is a static database connection */
        protected static Database $connect;

        /** @var string[] $table_keys The necessary keys to be seen from input elements */
        protected array $table_keys = [
            "lname", "oname", "username", "user_role"
        ];

        /** @var bool $loggedIn takes the status of the user's login  */
        public bool $loggedIn;
        
        public function __construct(
            protected int $user_id = 0, protected string $lname = "", protected string $oname = "",
            protected string $username = "", protected $user_role = 0
        ){
            $this->user_id = $user_id;
            $this->lname = $lname;
            $this->oname = $oname;
            $this->username = $username;
            $this->user_role = $user_role;

            self::$connect = new Database;
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
                    self::$connect->setStatus($response, true);
                }else{
                    self::$connect->setStatus("'$username' is signed in", true);
                    $this->loggedIn = true;
                }
            }else{
                $response = "Username '$username' not found. Please check and try again";
                self::$connect->setStatus($response, true);
            }

            return $response;
        }

        public function create(array $details) :bool|string{
            $response = $this->checkInsert($details);
            
            if($response){
                $response = self::$connect->insert("users", $details);

                //create login info
                if($response === true){
                    $new_det["password"] = password_hash($details["password"] ?? "Password@1", PASSWORD_DEFAULT);
                    $new_det["username"] = $details["username"];
                    $new_det["user_id"] = $this->user_id = self::$connect->insert_id;

                    $response = self::$connect->insert("userlogin", $new_det);
                }
            }else{
                $response = "Array list sent does not conform to table columns";
                self::$connect->setStatus($response.
                    "<br> Array(".implode(", ", $details).")", true);
            }

            return $response;
        }

        protected function passwordMatch($password, $username) :bool{
            $response = false;

            $db_password = self::$connect->fetch("password","userlogin","username='$username'");
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
                    $response = false; break;
                }
            }
            
            return $response;
        }

        private function makeKeys(array $data) :array{
            return array_key_exists(0,$data) ? 
                    $data : array_keys($data);
        }

        public function logs() :array{
            return self::$connect->getLogs();
        }

        public static function find(int $user_id, string|array $columns = "", User $instance = new self) :self|bool{
            $columns = empty($columns) ? "*" : $columns;
            $search = $instance::$connect->fetch("*","users","id=$user_id");

            if(is_array($search)){
                //create a new instance of the user
                $search = $instance->convertToConstruct($search);
                $user = new self(...array_values($search));
            }else{
                $search = $search !== false ? $search : "User was not found";
                self::$connect->setStatus($search, true);
                
                $user = false;
            }

            return $user;
        }

        private function checkUsername($username) :bool{
            $response = self::$connect->fetch("id","users","username='$username'");
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
    }