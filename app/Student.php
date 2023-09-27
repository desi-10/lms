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

        public function login() :int|string|bool{
            $response = false;

            list("index_number" => $index_number, "password" => $password) = $_POST;

            //search the index number
            $found_index = self::$connect->fetch("user_id","students",
                "index_number='$index_number'", no_results:"Student with index number '$index_number' not found");
            
            if($found_index === true){
                //verify user password
                $password_match = self::$connect->fetch("password");
            }elseif($found_index !== false){
                $response = $found_index;
            }else{
                $response = false;
            }

            return $response;
        }
        
        public function create(array $details) :bool|string{
            $response = true;

            //grab index number
            $index_number = $details["index_number"] ?? $this->createIndexNumber();

            //remove index number from 
            if(isset($details["index_number"])){
                unset($details["index_number"]);
            }

            //parse user info to users table
            $response = parent::create($details);

            //parse user into students table
            if($response === true){
                $student_data = [
                    "user_id" => $this->user_id,
                    "index_number" => $index_number
                ];

                $response = self::$connect->insert("students", $student_data);
            }

            return $response;
        }

        public function createIndexNumber(){
            $department_id = "08";
            $indexNumber = "03" . date("y") . $department_id;
            $unique = rand(1,999);
            $indexNumber .= str_pad((string) $unique, 4, "0", STR_PAD_LEFT);

            return $indexNumber;
        }
    }