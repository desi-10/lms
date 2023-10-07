<?php 
    declare(strict_types=1);
    namespace App;

    class Course
    {
        private int $course_id;
        private string $course_name;
        private string $course_code;

        public function __construct(private Database $connect, int $course_id = 0, string $course_name = "", string $course_code){
            $this->connect = $connect;
            $this->course_code = $course_code;
            $this->course_name = $course_name;
            $this->course_id = $course_id;
        }

        public function add() :bool{
            $success = false;
            
            return $success;
        }
    }