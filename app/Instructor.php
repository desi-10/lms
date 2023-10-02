<?php 
    declare(strict_types = 1);

    namespace App;

    class Instructor extends User
    {
        public function __construct(Database $database, 
            int $user_id = 0, string $lname = '', 
            string $oname = '', string $username = '', int $user_role = 2){
                parent::__construct($database, $user_id, $lname, $oname, $username, $user_role);
        }
    }