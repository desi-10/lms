<?php 
    declare(strict_types=1);
    namespace App;
    use App\Traits\Table;
    use App\Traits\Token;

    class User
    {
        use Table, Token;
        
        /** @var Database $connect This is a static database connection */
        protected static Database $connect;

        /** @var string[] $table_keys The necessary keys to be seen from input elements */
        protected array $table_keys = [
            "lname", "oname", "username", "user_role"
        ];

        /** @var bool $loggedIn takes the status of the user's login  */
        public static bool $loggedIn;
        
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

            static::$connect = $database;
            self::$loggedIn = false;
            $this->class_table = "users";
        }

        public function data() :array|string{
            $response = "Detail not found";

            if($this->user_id > 0){
                $response = [
                    "user_id" => $this->user_id,
                    "user_role" => $this->user_role,
                    "username" => $this->username,
                    "lname" => $this->lname,
                    "oname" => $this->oname
                ];
            }

            return $response;
        }

        public function login() :string|array|bool{
            self::$loggedIn = $response = false;

            //grab components
            list("username" => $username, "password" => $password) = $_POST;
            
            //verify user details
            if($this->checkUsername($username)){
                $response = $this->passwordMatch($password, $username);

                if($response === false){
                    $response = "Password does not match the username";
                    static::$connect->setStatus($response, true);
                }else{
                    static::$connect->setStatus("'$username' is signed in", true);
                    $id = (int) static::$connect->fetch("id","users","username='$username'")[0]["id"];
                    
                    //send user data and user token
                    $response = static::find($id)->data();
                    $response["token"] = $this->generateToken($id);
                }
            }else{
                $response = "Username '$username' not found. Please check and try again";
                static::$connect->setStatus($response, true);
            }

            return $response;
        }

        /**
         * This function provides the details of the current signed in user
         * @return static|array|bool returns the data of the current logged in user or false if there is none
         */
        public static function auth() :static|array|string|bool{
            $response = false;
            
            $headers = getallheaders();

            //check for a header called authorization
            if(isset($headers["Authorization"])){
                //remove bearer
                $token = str_replace("Bearer","", $headers["Authorization"]);
                $decoded = static::decode($token);

                if(is_array($decoded)){
                    $response = static::find($decoded["user_id"]);
                    // $response = $decoded;
                }else{
                    $response = "Error: $decoded";
                }
            }

            return $response;
        }

        /**
         * This is used to add a new user to the database
         * @param array $details This is the array data to be sent into the database
         * @return bool|string returns true or an error string
         */
        public function create(array $details) :bool|string{
            //provide a user role if one is not provided but return false if there is no user role defined by class
            if($this->user_role > 0){
                $details["user_role"] = $this->setDefault($details, "user_role", $this->user_role);
            }

            //check if the necessary fields are present            
            if($this->checkInsert($details)){
                if(($response = $this->validate($details, "insert")) === true){
                    $response = static::$connect->insert("users", $details);

                    //create login info
                    if($response === true){
                        $password = $this->setDefault($details, "password", "Password@1");
                        $new_det["password"] = password_hash($password, PASSWORD_DEFAULT);
                        $new_det["username"] = $details["username"];
                        $new_det["user_id"] = $this->user_id = static::$connect->insert_id;

                        $response = static::$connect->insert("userlogin", $new_det);
                    }
                }else{
                    http_response_code(422);
                    static::$connect->setStatus((string) $response, true);
                }
            }else{
                http_response_code(422);
                $response = static::$connect->status();
            }

            return $response;
        }

        /**
         * This function is used to update a user record
         * @param array $details This is the set of details to be used. It should also have an id field
         * @return bool|string returns true or a string error message
         */
        public function update(array $details) :string|bool{
            //validate data request
            if(($response = $this->validate($details, "update")) === true){
                //grab user details
                if(($current_details = static::find($details["id"]))!==false){
                    $current_details = $current_details->data();

                    //change any user_id to id
                    $this->replaceKey($current_details, "user_id", "id");

                    //update user table
                    if(($response = static::$connect->update($current_details, 
                        $details, $this->class_table, ["id"])) === true){
                        //update the logins table
                        $login_details = [
                            "user_id" => $current_details["id"],
                            "username" => $current_details["username"]
                        ];

                        $response = $this->updateLogins($login_details);
                        if($response === true){
                            static::$connect->setStatus("'{$current_details['username']}' was updated successfully", true);
                        }
                    }
                }else{
                    $response = "User not found";
                }
            }

            return $response;
        }

        public function updateLogins(array $user_data){
            $user_id = $user_data["id"] ?? $user_data["user_id"];
            $user = static::$connect->fetch("*","userlogin","user_id=$user_id");

            if(is_array($user)){
                $user = $user[0];
                if(!empty($user_data["password"])){
                    $user_data["password"] = password_hash($user_data["password"], PASSWORD_DEFAULT);
                }else{
                    $user_data["password"] = $user["password"];
                }

                //convert id key to user_id
                $this->replaceKey($user_data, "id", "user_id");

                $response = static::$connect->update($user, $user_data, "userlogin", ["user_id"]);
            }else{
                $response = "User not found";
            }
            
            return $response;
        }

        protected function passwordMatch($password, $username) :bool{
            $response = false;

            $db_password = static::$connect->fetch("password","userlogin","username='$username'");
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
                    static::$connect->setStatus("The field named '$key' was considered an invalid key for the request", true);
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
            return static::$connect->getLogs();
        }

        public static function find(string|int $user_id, $table = []) :static|false{
            $instance = new static(new Database);
            
            if(!empty($table)){
                list("columns"=>$columns, "tables"=>$tables, "where"=>$where) = $table;
            }
            else{
                $columns = "*"; $tables = "users"; $where = "id=$user_id";
            }
            
            $search = static::$connect->fetch($columns,$tables,$where);

            if(is_array($search)){
                //create a new instance of the user
                $search = $instance->convertToConstruct($search);
                $user = new static(static::$connect, ...array_values($search));
            }else{
                $search = $search !== false ? $search : "User was not found";
                static::$connect->setStatus($search, true);
                
                $user = false;
            }

            return $user;
        }

        private function checkUsername($username) :bool{
            $response = static::$connect->fetch("id","users","username='$username'");
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

            $response = static::$connect->fetch($column, $table, $where);

            return $response;
        }

        protected function validate(array $data, string $mode) :string|bool{
            $response = true;

            //general checks
            if(empty($data["lname"]) || is_null($data["lname"])){
                $response = "No last name was provided";
            }elseif(empty($data["oname"]) || is_null($data["oname"])){
                $response = "No other name(s) provided";
            }elseif(empty($data["username"]) || is_null($data["username"])){
                $response = "No username was provided";
            }elseif(empty($data["user_role"]) || is_null($data["user_role"])){
                $response = "User role was not specified";
            }elseif(ctype_digit($data["user_role"]) === false || $data["user_role"] < 1){
                $response = "User role provided is invalid";
            }
            
            if(strtolower($mode) == "update"){
                if(empty($data["id"]) || is_null($data["id"])){
                    $response = "User was not specified";
                }
            }

            return $response;
        }

        public function delete(string|int $user_id) :bool{
            $response = static::$connect->delete("users", "id=$user_id");

            if($response){
                static::$connect->delete("userlogin","user_id=$user_id");
                static::$connect->setStatus("User '$user_id' record deleted", true);
            }else{
                static::$connect->setStatus("User '$user_id' record could not be deleted", true);
            }
            
            return $response;
        }
    }