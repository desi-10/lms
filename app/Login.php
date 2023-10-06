<?php
    namespace App;

    class Login
    {
        private string $status;
        public function __construct(private Database $connect){
            $this->connect = $connect;
            $this->status = "";
        }
    }