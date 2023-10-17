<?php
    declare(strict_types=1);

    namespace App;

    class Roles
    {
        public bool $is_admin;
        public bool $is_instructor;
        public bool $is_student;
        public string $name;
        public int $id;

        private static Database $connect;

        private array $values = ["admin","instructor","student"];

        public function __construct(Database $db = new Database, string $name = "", int $id = 0){
            $this->resetAll($name, $id);

            static::$connect = $db;
        }

        private function resetAll(string $name, int $id){
            $this->is_admin = false;
            $this->is_instructor = false;
            $this->is_student = false;
            $this->name = $name;
            $this->id = $id;
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
        public static function find(int $role_id, Database $database = new Database) :self|bool{
            if(empty(static::$connect)){
                $instance = new static;
            }
            
            $response = $database->fetch("*","roles","id=$role_id");

            if(is_array($response)){
                $response = self::convertToConstruct($response);
                $response = new static($database, ...$response);

                //determine current role set
                $response->setuserRole($response);
            }else{
                static::$connect->setStatus("Role '$role_id' could not be found", true);
                $response = false;
            }

            return $response;
        }

        private static function convertToConstruct(array $search_results) :array{
            if(is_array($search_results[0])){
                $search_results = $search_results[0];
            }

            $search_results["id"] = (int) $search_results["id"];

            return $search_results;
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