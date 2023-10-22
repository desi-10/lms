<?php 
    declare(strict_types=1);

    namespace App;

use App\Traits\Table;

    class CourseMaterials
    {
        use Table;
        private static Database $connect;

        public function __construct(
            Database $db = new Database, private int $id = 0, private int $course_id = 0,
            private string $material_type = "", private string $material_path = ""
        ){
            self::$connect = $db;
            $this->set_defaults();
        }

        /**
         * This function is used to set the validation keys and also set up the class
         */
        protected function set_defaults(){
            $this->class_table = "coursematerials";
            $this->required_keys = [
                "course_id", "material_type", "material_path"
            ];
            static::$attributes = [
                "id" => "int", "course_id" => "int", "material_type" => "string", "material_path" => "string"
            ];
        }

        /**
         * This function is used to return an array format of the class details
         * @return array|string returns an array of the class attributes or a string error message
         */
        public function data() :array|string{
            $response = "No course material";

            if($this->id > 0){
                $response = [
                    "id" => $this->id,
                    "course_id" => $this->course_id,
                    "material_type"=> $this->material_type,
                    "material_path"=> $this->material_path
                ];
            }

            return $response;
        }

        /**
         * This function is used to fetch all course materials from the database
         * @return array|string|bool array of data or error string or false
         */
        public function all() :array|string|bool{
            $response = false;

            $where = [];

            if($this->course_id > 0){
                $where[] = "course_id={$this->course_id}";
            }

            $response = self::$connect->fetch("*",$this->class_table, $where);

            return $response;
        }

        /**
         * Search for a course material
         * @param string|int $material_id This is the id for the course material
         * @return self|bool returns a new course material or false
         */
        public static function find(string|int $material_id, Database &$connection = new Database) :self|bool{
            $response = false;

            if(empty(static::$connect))
                $instance = new self($connection);

            $search = self::$connect->fetch("*","coursematerials","id=$material_id");

            if(is_array($search)){
                $search = self::convertToConstruct($search);
                $response = new self(self::$connect, ...$search);
            }
            return $response;
        }

        /**
         * This function creates a new course material
         * @param array $details The details to be sent into the database
         * @return bool True for a successful create and error string for a fail
         */
        public function create(array $details) :bool|string{
            $response = false;

            Auth::authorize(["admin", "instructor"]);

            //check if the materials is a file
            if(($response = $this->checkFile("material_path")) === true){
                //work on the file and provide the path if its valid
                $response = $this->file("material_path", $details["material_path"]);

                if($response === true && $this->checkInsert($details, self::$connect)){
                    $response = $this->validate($details, "insert");
    
                    if($response === true){
                        list("db_path" => $db_path) = $this->removeKeys($details["material_path"], ["db_path"], true);
                        if($response = $this->upload_file(...$details["material_path"])){
                            //set the base path
                            $details["material_path"] = $db_path;
                            $response = self::$connect->insert($this->class_table, $details);
                        }
                    }
                }
            }

            return $response;
        }

        /**
         * Checks the existence of a file
         * @param string $name The input field name
         * @return bool|string True if found and a string if error
         */
        private function checkFile($name) :bool|string{
            if(file_exists($name) || file_exists($name) || file_exists($_SERVER["DOCUMENT_ROOT"].$name)){
                return true;
            }elseif(isset($_FILES[$name])){
                return true;
            }else{
                return "The material provided is not a file";
            }
        }

        /**
         * This is used to handle the file
         * @param string $name File input name
         * @param mixed $store_point This receives the variable to store the results in
         * @param bool $replace This is to determine if the file should be replaced or not
         * @return bool|string Returns the true if successful and string error if fail
         */
        private function file(string $name, &$store_point, bool $replace = false) :string|bool{
            if(isset($_FILES[$name])){
                list("name" => $filename, "type" => $type, "tmp_name" => $tmp_location) = $_FILES[$name];
                
                $type = str_replace("application/","", $type);
                
                //the path to store in the database
                $file_db_path = "public/coursematerials/$filename";
                
                //the direct path for checking and moving
                $final_location = $_SERVER["DOCUMENT_ROOT"].$file_db_path;

                //check if the file already exists
                if(file_exists($final_location) && !$replace){
                    $response = "The file '$filename' already exists";
                    self::$connect->setStatus($response, true);
                    return $response;
                }

                $store_point = [
                    "filename" => $filename,
                    "tmp_location" => $tmp_location,
                    "final_location" => $final_location,
                    "db_path" => $file_db_path
                ];

                return true;
            }

            return "No file exist";
        }

        private function upload_file(string $tmp_location, $final_location, $filename) :bool{
            if(($_SERVER["REQUEST_METHOD"] == "POST" && move_uploaded_file($tmp_location, $final_location)) ||
                ($_SERVER["REQUEST_METHOD"] == "PATCH" && rename($tmp_location, $final_location))
            ){
                $response = true;
                self::$connect->setStatus("'$filename' has been uploaded", true);
            }else{
                self::$connect->setStatus("The file '$filename' could not be uploaded", true);
                $response = false;
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

            Auth::authorize(["admin", "instructor"]);

            //replace with old material path if no new material is uploaded
            if(empty($details["material_path_old"])){
                return "Old path for the material was not specified";
            }

            if(empty($details["material_path"]) && !isset($_FILES["material_path"])){
                //store the old path into the new path
                $details["material_path"] = $this->removeKeys($details, ["material_path_old"], true)["material_path_old"];
            }

            //check if the materials is a file
            if(($response = $this->checkFile($details["material_path"] ?? "material_path")) === true){
                //work on the file and provide the path if its valid for new uploads    
                if(empty($details["material_path"])){
                    $response = $this->file("material_path", $details["material_path"], true);
                }

                if($response === true && $this->checkInsert($details, self::$connect)){
                    $response = $this->validate($details, "update");
    
                    if($response === true){
                        if($current_data = self::find($details["id"])){
                            $current_data = $current_data->data();

                            //a new upload would suggest that the material path should be an array
                            if(is_array($details["material_path"])){
                                list("db_path" => $db_path) = $this->removeKeys($details["material_path"], ["db_path"], true);
                                if($response = $this->upload_file(...$details["material_path"])){
                                    //set the base path
                                    $details["material_path"] = $db_path;

                                    //remove the old path
                                    $this->removeKeys($details, ["material_path_old"]);

                                    $response = self::$connect->update($current_data, $details, $this->class_table, ["id"]);
                                }
                            }else{
                                $response = self::$connect->update($current_data, $details, $this->class_table, ["id"]);
                            }
                        }else{
                            $response = "Course material was not found";
                        }
                    }
                }
            }

            return $response;
        }

        /**
         * This function is used to delete a course material
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
        protected function validate(array $data, string $mode) :string|bool{
            $response = true;
            
            $keys = [
                "course_id" => ["course", "int"],
                "material_type" => ["type of material", "string"],
                "material_path" => ["course material", "file"]
            ];

            if($mode == "update"){
                $keys["id"] = ["material", "int"];
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
            if(!Course::find($details["course_id"])){
                return "The specified course is invalid";
            }

            return true;
        }
    }