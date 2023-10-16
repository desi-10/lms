<?php 
    declare(strict_types = 1);

    namespace App;
    use App\Traits\Table;

    class Student extends User
    {
        use Table;
        private string $index_number;
        private int $level;
        private int $program_id;
        private const program_levels = [100, 200, 300, 400];
        
        public function __construct(Database $database, 
            int $user_id = 0, string $lname = '', 
            string $oname = '', string $username = '', int $user_role = 3,
            string $index_number = '', int $level = 0, int $program_id = 0){
                parent::__construct($database, $user_id, $lname, $oname, $username, $user_role);
                $this->index_number = $index_number;
                $this->level = $level;
                $this->program_id = $program_id;

                $this->set_class_defaults();
        }

        protected function set_class_defaults() :void{
            //attributes of the class
            self::$attributes = array_merge(parent::$attributes, [
                "index_number" => "string", "user_id" => "int",
                "level" => "int", "program_id" => "int"
            ]);

            //table for fetching data
            $this->table = [
                [
                    "join" => "users students", 
                    "alias" => "u s", 
                    "on" => "id user_id"
                ]
            ];

            $this->required_keys = [
                "lname", "oname", "user_role","index_number",
                "username", "level","program_id"
            ];

            $this->class_table = "student";
        }

        public function getIndexNumber() :string{
            return $this->index_number;
        }

        public function update(array $details) : string|bool{
            //allow only admin and student
            Auth::authorize(["admin", "student"]);

            if(!empty($details["index_number"])){
                if(($response = self::find($details["id"])) !== false){
                    //store student details
                    $student = ["index_number" => $details["index_number"], "user_id" => $details["id"]];

                    $response = static::$connect->update($response->data(), $student, $this->class_table, ["index_number","user_id"], "AND");

                    if($response === true){
                        //remove index_number from list
                        unset($details["index_number"]);

                        //update users table
                        $this->class_table = "users";
                        $response = parent::update($details);
                    }
                }else{
                    $response = "Student provided is not found";
                }
            }else{
                $response = "No index number found";
            }

            $this->resetClassTable();

            return $response;
        }

        private function resetClassTable(){
            $this->class_table = "students";
        }

        public function login() :string|array|bool{
            $response = false;
            try {
                list("index_number" => $index_number, "password" => $password) = $_POST;

                //search the index number
                $tables = $this->table;
                $column = ["u.username", "u.level", "u.program_id"];
                $where = "s.index_number='$index_number'";
                $no_result = "Student with index number '$index_number' not found";

                $found_index = static::$connect->fetch($column,$tables,$where, no_results:$no_result);
                
                if(is_array($found_index)){
                    //store index number, level and program
                    $this->index_number = $index_number;
                    $this->level = $found_index[0]["level"];
                    $this->program_id = $found_index[0]["program_id"];

                    //pass username to parent to login
                    $_POST["username"] = $found_index[0]["username"];
                    $response = parent::login();
                }elseif($found_index !== false){
                    //provide error response string
                    $response = $found_index;
                }else{
                    //false response returned as a result of an error
                    $response = false;
                }
            } catch (\Throwable $th) {
                $response = $th->getMessage();
            }

            return $response;
        }

        public function data() :string|array{
            $response = parent::data();

            //add index number to response data
            if(is_array($response)){
                $response["index_number"] = $this->index_number;
                $response["level"] = $this->level;
                $response["program_id"] = $this->program_id;
            }

            return $response;
        }

        /**
         * This function is used to find the user's program
         * @return Program|false returns the program object or a false
         */
        public function program() :Program|false{
            $response = "Program not found";

            if($this->program_id > 0){
                $response = Program::find($this->program_id);
            }

            return $response;
        }

        public static function find(int|string $user_id, $table = []) :static|false{
            $table = [
                "columns" => "u.*, s.index_number, s.level, s.program_id",
                "tables" => [
                    [
                        "join" => "users students", 
                        "alias" => "u s", 
                        "on" => "id user_id"
                    ]
                ],
                "where" => "u.id=$user_id OR s.index_number='$user_id'"
            ];

            $response = parent::find($user_id, $table);

            return $response;
        }
        
        public function create(array $details) :bool|string{
            $response = true;

            Auth::authorize("admin");

            //grab or create index number
            $index_number = $this->setDefault($details, "index_number", $this->createIndexNumber());

            //remove index number from details provided
            $this->removeKeys($details, ["index_number"]);

            //check for level and program then remove
            $level_program = $this->removeKeys($details, ["level","program_id"], true);
            $level = $level_program["level"] ?? null;
            $program_id = $level_program["program_id"] ?? null;
            
            //check the program
            if($this->checkProgram((int) $program_id)){
                //check if the level is defined or valid
                if(empty($level)){
                    $response = "Program level was not provided";
                    static::$connect->setStatus($response, true);
                }elseif(!in_array($level, self::program_levels)){
                    $response = "Program level does not match the desired program levels";
                    static::$connect->setStatus($response, true);
                }else{
                    //username should be the specified username or the index number
                    $details["username"] = $this->setDefault($details, "username", $index_number);
                    
                    //parse user info to users table
                    $this->class_table = "users";
                    $response = parent::create($details);

                    //switch to student class table
                    $this->resetClassTable();

                    //parse user into students table
                    if($response === true){
                        $student_data = [
                            "user_id" => $this->user_id,
                            "index_number" => $index_number,
                            "program_id" => $program_id,
                            "level" => $level
                        ];

                        $response = static::$connect->insert($this->class_table, $student_data);
                    }
                }
            }else{
                http_response_code(422);
                if(is_null($program_id)){
                    $response = "Program was not defined";
                }else{
                    $response = "Program defined was not found";
                }
                static::$connect->setStatus($response, true);
            }

            return $response;
        }

        private function checkProgram(int $program_id){
            $response = (bool) Program::find($program_id);

            if(!$response){
                static::$connect->setStatus("Program '$program_id' does not exist", true);
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

        public function all() :string|array|bool{
            $column = "id, index_number, lname, oname, username"; 
            $table = [
                [
                    "join" => "users students", 
                    "alias" => "u s", 
                    "on" => "id user_id"
                ]
            ]; 
            $where = "user_role={$this->user_role}";

            $response = static::$connect->fetch($column, $table, $where);

            return $response;
        }

        protected function validate(array $data, string $mode) :bool|string{
            $response = true;
            $student_check = [
                "index_number" => ["index number","string"],
                "program_id" => ["program", "int"],
                "level" => ["program level", "int"]
            ];

            //check if there are valid data
            if($this->class_table == "student"){
                $response = $this->check($data, $student_check);
            }

            //validate other keys
            if($response === true){
                $response = parent::validate($data, $mode);
            }

            if($response !== true)
                static::$connect->setStatus($response);

            return $response;
        }
    }