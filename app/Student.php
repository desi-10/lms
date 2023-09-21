<?php 
    declare(strict_types = 1);

    namespace App;

    class Student extends User
    {
        private string $index_number;

        public function getIndexNumber() :string{
            return $this->index_number;
        }

        public function setIndexNumber(string $value) :void{
            $this->index_number = $value;
        }

        public function login() :int|bool{
            $response = false;

            list("index_number" => $index_number, "password" => $password) = $_POST;

            //search the index number
            $found_index = $this->connect->fetch("user_id","students","index_number='$index_number'");

            if($found_index){
                
            }else{
                $response = false;
            }

            return $response;
        }
    }