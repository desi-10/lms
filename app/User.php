<?php 
    declare(strict_types=1);
    namespace App;

    class User
    {
        protected static Database $connect;

        protected array $table_keys = [
            "lname", "oname", "username", "user_role"
        ];
        
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
        }

        public function login() :int|string|bool{
            $response = false;

            return $response;
        }

        public function create(array $details) :bool|string{
            $response = $this->checkInsert($details);
            
            if($response){
                $response = self::$connect->insert("users", $details);

                //create login info
                if($response === true){
                    $new_det["password"] = md5($details["password"] ?? "Password@1");
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
    }