<?php
    namespace App\Controller;

    use App\Database;

    class ResponseController
    {
        private Database $database;

        public function __construct(){
            $this->database = new Database;
        }

        public function processRequest(string $method, string $class_name, ?string $id){
            if($id){
                $this->processResource($method, $class_name, $id);
            }else{
                $this->processCollection($method, $class_name);
            }
        }

        /**
         * This function is used to get a single result from the database
         */
        private function processResource(string $method, string $class_name, string $id){
            $object = new $class_name($this->database);

            switch($method){

            }
        }

        /**
         * This function is used to retrieve a collection of results from the database
         */
        private function processCollection(string $method, string $class_name){
            $object = new $class_name($this->database);
            switch ($method){
                case "GET":
                    $results = $object->all();
                    if(is_array($results)){
                        $success = true;
                    }else{
                        $success = false;
                    }
    
                    break;
                case "POST":
                    $data = $_POST;
                    $results = $object->create($data);

                    if($results === true){
                        $success = true;
                    }else{
                        $success = false;
                    }
                    $results = $this->database->status();
                    
                    break;
                default:
                    http_response_code(501);
                    header("Allow: GET, POST");
            }

            echo json_encode(["success" => $success, "results" => $results]);
            // echo json_encode(["success" => $success, "results" => $results, "queries" => $this->database->queries(), "logs" => $this->database->getLogs()]);
        }
    }