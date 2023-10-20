<?php
    declare(strict_types=1);

    namespace App;
    use App\Traits\Table;

    class Roles
    {
        use Table;
        public bool $is_admin;
        public bool $is_instructor;
        public bool $is_student;
        public string $name;
        public int $id;

        private static Database $connect;

        private array $values = ["admin","instructor","student"];

        public function __construct(Database $db = new Database, string $name = "", int $id = 0){
            $this->set_defaults();

            $this->resetAll($name, $id);

            static::$connect = $db;
        }

        protected function set_defaults(){
            $this->class_table = "roles";
            $this->required_keys = ["name"];
            static::$attributes = [
                "name" => "string", "id" => "int"
            ];
        }

        private function resetAll(string $name, int $id){
            $this->is_admin = false;
            $this->is_instructor = false;
            $this->is_student = false;
            $this->name = $name;
            $this->id = $id;

            $this->setUserRole($this);
        }

        public function data() :array|string{
            $response = "No results found";

            if($this->id > 0){
                $response = [
                    "id" => $this->id,
                    "name" => $this->name
                ];
            }

            return $response;
        }

        /**
         * This function is used to search a new role
         * @param int $role_id The id to be searched
         * @return self|bool returns a new instance of a role 
         */
        public static function find(int $role_id, Database &$connection = new Database) :self|bool{
            if(empty(static::$connect)){
                $instance = new static($connection);
            }
            
            $response = $connection->fetch("*","roles","id=$role_id");
            
            if(is_array($response)){
                $response = self::convertToConstruct($response);
                $response = new static($connection, ...$response);

                //determine current role set
                $response->setuserRole($response);
            }else{
                static::$connect->setStatus("Role '$role_id' could not be found", true);
                $response = false;
            }

            return $response;
        }

        public function create(){
            
        }

        private function setUserRole(self &$role){
            switch($role->id){
                case 1: $role->is_admin = true; $name = "admin"; break;
                case 2: $role->is_instructor = true; $name = "instructor"; break;
                case 3: $role->is_student = true; $name = "student"; break;
            }
        }
    }