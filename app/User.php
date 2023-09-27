<?php 
    declare(strict_types=1);
    namespace App;

    class User
    {
        protected static Database $connect;
        
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
            $response = $this->connect->insert("users", $details);
            
            return $response;
        }
    }