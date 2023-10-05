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
            $success = false;

            switch($method){
                case "GET":
                    $results = $object::find($id);
                    
                    if($results !== false){
                        $success = true;
                        $results = $results->data();
                    }else{
                        http_response_code(404);
                        $results = "No results were found";
                    }

                    break;
                case "PATCH":
                    // $results = $_REQUEST;
                    $data = $this->getInputs();

                    //insert the id if it is not provided
                    $data["id"] = $data["id"] ?? $id;

                    $results = $object->update($data);
                    // $results = $object->update($_REQUEST);
                    break;
                case "DELETE":
                        $results = $object->delete($id);
                        break;
                default:
                    http_response_code(405);
                    header("Allow: GET, PATCH, DELETE");
            }

            echo json_encode(["success" => $success, "results" => $results, "message" => $this->database->status()]);
            // echo json_encode(["success" => $success, "results" => $results, "queries" => $this->database->queries(), "logs" => $this->database->getLogs()]);
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
                        http_response_code(201);
                        $success = true;
                    }else{
                        $success = false;
                    }
                    $results = $this->database->status();
                    
                    break;
                default:
                    http_response_code(405);
                    $success = false;
                    $results = "Method not allowed";
                    header("Allow: GET, POST");
            }

            echo json_encode(["success" => $success, "results" => $results]);
            // echo json_encode(["success" => $success, "results" => $results, "queries" => $this->database->queries(), "logs" => $this->database->getLogs()]);
        }

        private function getInputs() :array{
            $inputs = file_get_contents("php://input");
            $data = [];

            if(str_contains($inputs, "=")){
                $inputs = explode("&",file_get_contents("php://input"));
                foreach($inputs as $input){
                    $input = explode("=",$input);
                    $data[$input[0]] = str_replace(["+"], [" "], $input[1]);
                }
            }
            

            return $data;
        }
    }