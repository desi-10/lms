<?php
    namespace App\Controller;

    use App\Database;

    class ResponseController
    {
        private Database $database;

        public function __construct(){
            $this->database = new Database;
        }

        public function processRequest(string $method, string $class_name, ?string $id, ?string $additional){
            if($id && is_null($additional)){
                $this->processResource($method, $class_name, $id);
            }elseif($id && $additional){
                $this->processMethod($method, $class_name, $id, $additional);
            }else{
                $this->processCollection($method, $class_name);
            }
        }

        /**
         * This function is used to get a single result from the database
         * @param string $method The request method
         * @param string $class_name The name of the requested class
         * @param int|string $id The id or login in case its a login request
         * @return void processes a resource request
         */
        private function processResource(string $method, string $class_name, string $id){
            $object = new $class_name($this->database);
            $success = false;
            
            switch($method){
                case "GET":
                    $results = $object::find($id, connection: $this->database);
                    
                    if($results !== false){
                        $success = true;
                        $results = $results->data();
                    }else{
                        http_response_code(404);
                        $results = "No results were found";
                    }

                    break;
                case "PATCH":
                    $data = $this->getInputs();

                    //insert the id if it is not provided
                    $data["id"] = $data["id"] ?? $id;

                    $results = $object->update($data);

                    if($results === true){
                        $success = true;
                    }

                    break;
                case "DELETE":
                        $results = $object->delete($id);
                        http_response_code(204);
                        break;
                case "POST":
                    //usually for user logins
                    if(strtolower($id) === "login"){
                        $data = !empty($_POST) ? $_POST : $this->getInputs();
                        if(!empty($data)){
                            $_POST = $data;
                            $results = $object->login();
    
                            if(is_array($results)){
                                $results = str_replace("Token: ", "", $results);
                                $success = true;
                            }else{
                                http_response_code(401);
                                $success = false;
                            }
                        }else{
                            $results = "Username or Password not set";
                        }
                        
                    }else{
                        http_response_code(405);
                        header("Allow: GET, PATCH, DELETE");
                        $results = "Wrong request or request format identified";
                    }
                    break;
                default:
                    http_response_code(405);
                    header("Allow: GET, PATCH, DELETE, POST");
            }

            echo json_encode(["success" => $success, "results" => $results, "message" => $this->database->status()]);
            // echo json_encode(["success" => $success, "results" => $results, "queries" => $this->database->queries(), "logs" => $this->database->getLogs()]);
        }

        /**
         * This function is used to retrieve a collection of results from the database
         * It is also used for adding a new element to the database
         * @param string $method This is the request method
         * @param string $class_name The name of the request class
         * @return void processes a collection request in get or adds a content
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
                    $data = !empty($_POST) ? $_POST : $this->getInputs();
                    $results = $object->create($data);

                    if($results === true){
                        http_response_code(201);
                        $success = true;
                    }else{
                        http_response_code(422);
                        $success = false;
                    }
                    break;
                default:
                    http_response_code(405);
                    $success = false;
                    $results = "Method not allowed";
                    header("Allow: GET, POST");
            }

            echo json_encode(["success" => $success, "results" => $results, "message" => $this->database->status()]);
            // echo json_encode(["success" => $success, "results" => $results, "queries" => $this->database->queries(), "logs" => $this->database->getLogs()]);
        }

        /**
         * This function is responsible for additional method
         * Used to handle requests in the format [api/class/id/method]
         * Works best for methods which require no parameters
         * @param string $method This is the request method [Uses only get request]
         * @param string $class_name The name of the requested class
         * @param int $id The integer id
         * @param string $additional The additional methods to be processed
         */
        private function processMethod(string $method, string $class_name, int $id, string $additional){
            $success = false;
            switch ($method){
                case "GET":
                    if($this->checkAdditional($class_name, $additional)){
                        $object = $class_name::find($id, connection: $this->database);

                        if($object){
                            $results = $object->$additional();

                            if(!is_array($results)){
                                if(is_object($results)){
                                    $success = true;
                                    $results = $results->data();
                                }
                            }elseif(is_array($results)){
                                $success = true;
                            }
                        }else{
                            http_response_code(422);
                            //would usually return false for finds
                            $results = "Item not found or is invalid";
                        }
                    }else{
                        http_response_code(422);
                        $results = "Requested method is invalid";
                    }
                    break;
                default:
                    http_response_code(405);
                    $results = "Method not allowed";
                    header("Allow: GET");
            }

            echo json_encode(["success" => $success, "results" => $results, "message" => $this->database->status()]);
            // echo json_encode(["success" => $success, "results" => $results, "queries" => $this->database->queries(), "logs" => $this->database->getLogs()]);
        }

        private function checkAdditional(string $class_name, string $additional) :bool{
            //get class name only without namespace
            $class_name = str_replace("\App\\", "", $class_name);
            
            //the allowed methods for the various classes
            $allowed_methods = [
                "user" => ["discussions", "messages", "message_sent", "message_received"],
                "quiz" => ["instructor", "course", "program", "questions", "grades"],
                "student" => ["program", "discussions", "grades", "messages", "message_sent", "message_received"],
                "question" => [],
                "questionoption" => ["question"],
                "program" => ["assignments"],
                "course" => ["instructor", "program", "assignments", "discussions", "materials"],
                "instructor" => ["courses", "assignments", "discussions", "messages", "message_sent", "message_received"],
                "assignment" => ["course","instructor","program", "grades"],
                "discussion" => ["course","user"],
                "grade" => ["quiz","assignment", "student"],
                "message" => ["sender", "recipient"],
                "coursematerial" => ["course"],
            ];

            return in_array($additional, $allowed_methods[$class_name]);
        }

        private function getInputs() :array{
            $inputs = file_get_contents("php://input");
            $data = [];

            if(str_contains($inputs,"Content-Disposition")){
                //get the boundary
                $boundary = $this->getBoundary();
                $parts = explode("--$boundary", $inputs);
                // var_dump($parts);
                $data = $this->sortMultiForm($parts);
            }elseif(str_contains($inputs, "=")){
                $inputs = explode("&",file_get_contents("php://input"));
                foreach($inputs as $input){
                    $input = explode("=",$input);
                    $data[$input[0]] = str_replace(["+"], [" "], $input[1]);
                }
            }elseif(str_contains($inputs, ":")){
                $data = (array) json_decode($inputs);
            }
            

            return $data;
        }

        /**
         * For multipart forms, catch boundaries, especially if it is non POST requests
         */
        private function getBoundary() :string{
            $header = getallheaders();
            $boundary = $header["Content-Type"];
            $boundary = explode(";", $boundary)[1];
            $boundary = explode("=", $boundary)[1];

            return $boundary;
        }

        /**
         * This sorts multipart forms into the array format as $array[$key] = $value
         * @param array $input_array The formated input array
         * @return array returns an array of key => values
         */
        private function sortMultiForm(array $input_array) :array{
            $response = [];

            if(is_array($input_array)){
                foreach($input_array as $input){
                    //ignore empty parts
                    if(empty($input)){
                        continue;
                    }

                    $input = str_replace("Content-Disposition: form-data;", "", $input);

                    if(str_contains($input, "filename")){
                        //get the file type
                        preg_match('/Content-Type: (.+)/', $input, $type);
                        $file_type = str_replace(["\n","\r"],"",$type[1]);

                        //get the input name and file name
                        preg_match('/name="(.+)"\s/', $input, $matches);
                        $input_name = str_replace("\"","",explode(";",$matches[1])[0]);
                        $file_name = str_replace("\"", "", explode("filename=", $matches[1])[1]);
                        
                        //process and save the file
                        $file_data = substr($input, strpos($input, "\r\n\r\n") + 4);
                        $file_size = file_put_contents($tmp_name = tempnam($_SERVER["TMP"], "tmp"), $file_data);

                        //add this file to the files superglobal array
                        $_FILES[$input_name] = [
                            "name"=> $file_name,
                            "full_path" => $file_name,
                            "type"=> $file_type,
                            "error"=> 0,
                            "size"=> $file_size,
                            "tmp_name"=> $tmp_name,
                        ];
                    }else if(!str_contains($input, "--")){
                        $input = str_replace(["\r","\n"], "", $input);
                        
                        //remove " name= from the string
                        $input = explode("\"", $input);

                        list($na, $name,$value) = $input;
                        $response[$name] = $value;
                    }                    
                }
            }

            return $response;
        }
    }