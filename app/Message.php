<?php
    declare(strict_types= 1);

    namespace App;
    use App\Traits\Table;

    class Message
    {
        use Table;
        private static Database $connect;

        public function __construct(
            Database $db = new Database, private int $id = 0, private int $sender_id = 0, private int $recepient_id = 0,
            private string $content = "", private string $message_time = ""
        ){
            self::$connect = $db;
            $this->set_defaults();
        }

        /**
         * This function is used to set the validation keys and also set up the class
         */
        protected function set_defaults(){
            $this->class_table = "messages";
            $this->required_keys = [
                "sender_id", "recipient_id", "content"
            ];
            static::$attributes = [
                "id" => "int", "sender_id" => "int", "recepient_id" => "int",
                "content" => "string", "message_time" => "string"
            ];

            $this->message_time = date("Y-m-d H:i:s");
        }

        /**
         * This function is used to return an array format of the class details
         * @return array|string returns an array of the class attributes or a string error message
         */
        public function data() :array|string{
            $response = "No message";

            if($this->id > 0){
                $response = [
                    "id"=> $this->id,
                    "sender_id"=> $this->sender_id,
                    "recepient_id"=> $this->recepient_id,
                    "content"=> $this->content,
                    "message_time"=> $this->message_time
                ];
            }

            return $response;
        }

        /**
         * This function is used to fetch all messages from the messages table
         * @return array|string|bool array of data or error string or false
         */
        public function all() :array|string|bool{
            $response = false;

            $where = [];

            if($this->sender_id > 0){
                $where[] = "sender_id={$this->sender_id}";
            }

            if($this->recepient_id > 0){
                $where[] = "recepient_id={$this->recepient_id}";
            }

            $response = self::$connect->fetch("*",$this->class_table, $where, "OR");

            return $response;
        }

        /**
         * Search for a message
         * @param string|int $message_id This is the id for the message
         * @return self|bool returns a new message or false
         */
        public static function find(string|int $message_id) :self|bool{
            $response = false;

            if(empty(static::$connect))
                $instance = new self;

            $search = self::$connect->fetch("*","messages", "id=$message_id");

            if(is_array($search)){
                $search = self::convertToConstruct($search);
                $response = new self(self::$connect, ...$search);
            }
            return $response;
        }

        /**
         * This function creates a new message
         * @param array $details The details to be sent into the database
         * @return bool True for a successful create and error string for a fail
         */
        public function create(array $details) :bool|string{
            $response = false;

            if($this->checkInsert($details, self::$connect)){
                $response = $this->validate($details, "insert");

                if($response === true){
                    //set the time if it is not set
                    $details["message_time"] = $details["message_time"] ?? $this->message_time;

                    $response = self::$connect->insert($this->class_table, $details);
                }
            }
            return $response;
        }

        /**
         * Function is used to update a record
         * @param array $details Details to be used to update
         * @return bool|string returns true if successful or an error string
         */
        public function update(array $details) :bool|string{
            $response = false;

            if($this->checkInsert($details, self::$connect)){
                $response = $this->validate($details, "update");

                if($response === true){
                    if($current_data = self::find($details["id"])){
                        $current_data = $current_data->data();

                        self::$connect->update($current_data, $details, $this->class_table, ["id"]);
                    }else{
                        $response = "Message was not found";
                    }
                }
            }

            return $response;
        }

        /**
         * This function is used to delete a message
         * @param string|int $id The id to be deleted
         * @return bool True if successful and false if not
         */
        public function delete(string|int $id) :bool{
            $response = self::$connect->delete($this->class_table, "id=$id");

            return $response;
        }

        /**
         * This function is used to validate input data
         * @param array $data The data to be processed
         * @param string $mode The mode of the request
         * @return string|bool returns true if everything is fine or string of error
        */
        protected function validate(array &$data, string $mode) :string|bool{
            $response = true;

            $keys = [
                "sender_id" => ["sender", "int"],
                "recepient_id" => ["recepient", "int"],
                "content" => ["content", "string"]
            ];

            if($mode == "update"){
                $keys["id"] = ["message id", "int"];
                $keys["message_time"] = ["message time", "string"];
            }

            $response = $this->check($data, $keys);

            if($response === true){
                $response = $this->validate_fields($data);
            }

            return $response;
        }

        /**
         * Check key details in the input string
         * @param array $details The input data to be checked
         * @return bool|string True if everything is correct or an error string
         */
        private function validate_fields(array $details) :bool|string{
            //check if the users are valid
            if(!User::find($details["sender_id"])){
                return "Sender data does not exist or is invalid";
            }

            if(!User::find($details["recepient_id"])){
                return "Recepient data does not exist or is invalid";
            }

            if($details["recipient_id"] == $details["sender_id"]){
                return "You cannot send a message to yourself";
            }

            if(!empty($details["message_time"]) && !strtotime($details["message_time"])){
                return "Invalid date has been identified";
            }

            return true;
        }

        /**
         * Get the recepient data of this message
         * @return User|false returns the user or false if invalid
         */
        public function recepient() :User|false{
            return $this->recepient_id > 0 ? User::find( $this->recepient_id ) : false;
        }

        /**
         * Get the sender data of this message
         * @return User|false returns the user or false if invalid
         */
        public function sender() :User|false{
            return $this->sender_id > 0 ? User::find( $this->sender_id ) : false;
        }
    }