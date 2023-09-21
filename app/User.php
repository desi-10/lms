<?php 
    declare(strict_types=1);
    namespace App;

    class User
    {
        protected Database $connect;
        
        public function __construct(
            protected int $user_id = 0, protected string $lname = "", protected string $oname = "",
            protected string $username = "", protected $user_role = 0
        ){
            $this->user_id = $user_id;
            $this->lname = $lname;
            $this->oname = $oname;
            $this->username = $username;
            $this->user_role = $user_role;

            $this->connect = new Database;
        }

        public function login() :int|bool{
            $response = false;

            return $response;
        }
    }