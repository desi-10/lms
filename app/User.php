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

        /** @var bool $loggedIn takes the status of the user's login  */
        public static bool $loggedIn;

        /** @var Roles $role Returns a whole data of the user role */
        public Roles $role;
        
        public function __construct(
            Database $database,
            protected int $user_id = 0, protected string $lname = "", protected string $oname = "",
            protected string $username = "", protected int $user_role = 0
        ){
            static::$connect = $database;
            self::$loggedIn = false;
            $this->set_defaults();
        }

        public function getID(){
            return $this->user_id;
        }

        public function getRole(){
            return $this->user_role;
        }

        /**
         * This function is used to set the validation keys and also set up the class
         */
        protected function set_defaults(){
            $this->class_table = "users";
            $this->required_keys = [
                "lname", "oname", "username", "user_role"
            ];
            static::$attributes = [
                "id" => "int", "lname" => "string",
                "oname" => "string", "username" => "string",
                "user_role" => "int"
            ];
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
                    static::$loggedIn = true;
                    
                    //send user data and user token
                    $response = $this->data();
                    $response["token"] = $this->generateToken($this->user_id);
                }
            }else{
                $response = "Username '$username' not found. Please check and try again";
                static::$connect->setStatus($response, true);
            }

            return $response;
        }

        /**
         * This function provides the details of the current signed in user
         * @return static|bool returns the data of the current logged in user or false if there is none
         */
        public static function auth() :static|array|bool{
            $response = false;

            if(empty(static::$connect)){
                new static(new Database);
            }
            
            $headers = getallheaders();

            //check for a header called authorization
            if(isset($headers["Authorization"])){
                //remove bearer if there is any
                $token = str_replace("Bearer","", $headers["Authorization"]);
                $decoded = static::decode($token);

                if(str_contains($decoded, ".")){
                    $user_id = (int) explode(".",$decoded)[0];

                    $columns = ["u.*", "r.name", "r.id as role_id"];
                    $tables = [
                        "join" => "users roles", 
                        "alias" => "u r", 
                        "on" => "user_role id"
                    ];
                    $where = "u.id=$user_id";

                    $response = static::$connect->fetch($columns, $tables, $where);

                    if(is_array($response)){
                        $response = $response[0];
                        $role = static::remove_keys($response, ["role_id", "name"], true);
                        // replace role_id with id
                        static::replace_key($role, "role_id", "id");

                        $role["id"] = (int) $role["id"];
                        $role = new Roles(static::$connect, ...$role);

                        //create the user
                        $response["id"] = (int) $response["id"];
                        $response["user_role"] = (int) $response["user_role"];

                        static::replace_key($response, "id", "user_id");

                        $response = new static(static::$connect, ...$response);

                        //set the role
                        $response->role = $role;

                        //register user as logged in
                        $response::$loggedIn = true;
                    }else{
                        $response = false;
                    }
                }else{
                    $response = false;
                }
            }

            return $response;
        }

        /**
         * This creates a new role object for the class
         * @param self $object The class instance to make modifications of
         * @return Roles Returns a roles object
         */
        private function create_role(self $object) :Roles{
            return Roles::find($object->user_role, static::$connect);
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
            if($this->checkInsert($details, static::$connect)){
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

                    //remove password key if it exists in details
                    $password = null;
                    if(isset($details["password"])){
                        $password = $details["password"];
                        unset($details["password"]);
                    }

                    //update user table
                    if(($response = static::$connect->update($current_details, 
                        $details, $this->class_table, ["id"])) === true){
                        //update the logins table
                        $login_details = [
                            "user_id" => $current_details["id"],
                            "username" => $current_details["username"],
                            "password" => $password
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
                $response = "User login details not found";
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

        public function logs() :array{
            return static::$connect->getLogs();
        }

        public static function find(string|int $user_id, $table = [], Database &$connection = new Database) :static|false{
            if(empty(static::$connect)){
                $instance = new static($connection);
            }
            
            if(!empty($table)){
                list("columns"=>$columns, "tables"=>$tables, "where"=>$where) = $table;
            }
            else{
                $columns = "*"; $tables = "users"; $where = "id=$user_id";
                $instance = new static(static::$connect);
                if($instance->user_role > 0){
                    $where .= " AND user_role=".$instance->user_role;
                }
            }
            
            $search = static::$connect->fetch($columns,$tables,$where);

            if(is_array($search)){
                //create a new instance of the user
                $search = static::convertToConstruct($search);
                
                //change id to user_id
                self::replace_key($search, "id", "user_id");

                $user = new static(static::$connect, ...$search);

                //set the user role
                $user->role = $user->create_role($user);
            }else{
                $search = $search !== false ? $search : "User was not found";
                static::$connect->setStatus($search, true);
                
                $user = false;
            }

            return $user;
        }

        private function checkUsername($username) :bool{
            $response = static::$connect->fetch("*","users","username='$username'");

            //pass user details as current user for class
            if(is_array($response[0])){
                $user = $response[0];

                $this->user_id = (int) $user["id"];
                $this->lname = $user["lname"];
                $this->oname = $user["oname"];
                $this->username = $user["username"];
                $this->user_role = (int) $user["user_role"];

                static::$connect->setStatus("Marked '$username' as current user", true);
            }

            //based on results, tell if user was found or not
            $response = is_array($response[0]) ? true : false;
            
            return $response;
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
            $keys = [
                "lname" => ["last name", "string"],
                "oname" => ["other name(s)", "string"],
                "username" => ["username", "string"],
                "user_role" => ["user role", "int"]
            ];

            //update checks
            if(strtolower($mode) == "update"){
                $keys["id"] = ["user identification number","int"];
            }

            //general checks
            $response = $this->check($data, $keys);
            
            return $response;
        }

        public function delete(string|int $user_id) :bool{
            $response = static::$connect->delete("users", "id=$user_id");

            if($response){
                static::$connect->setStatus("User '$user_id' record deleted", true);
            }else{
                static::$connect->setStatus("User '$user_id' record could not be deleted", true);
            }
            
            return $response;
        }

        /**
         * Get the discussions this user has been involved in
         * @return array|false An array of discussions or false if none
         */
        public function discussions() :array|false{
            $discussion = new Discussion(self::$connect, user_id: $this->user_id);
            $response = $discussion->all();

            return is_array($response) ? $response : false;
        }
    }