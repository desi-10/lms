<?php
    declare(strict_types=1);

    namespace App\Traits;

use App\Database;

    trait Table
    {
        /** @var string|array $table The table for the class methods */
        protected string|array $table;

        /** @var string|string[] $columns The columns from the table */
        protected string|array $columns;

        /** @var string|string[] $where This is used for conditions in a search method */
        protected string|array $where;

        /** @var string $class_table The general table for a class */
        protected string $class_table;

        /** @var string[] $required_keys The necessary keys to be seen from input elements */
        protected array $required_keys;

        /** @var string[] $attributes This is the attributes and their data types */
        protected static array $attributes = [];

        /**
         * This function is used to set array values with default values if empty 
         * @param array $array The array to search through
         * @param string|int $key The key or index of the array
         * @param mixed $default_value The default value to be entered
         * 
         * @return mixed The resulting value
         */
        protected function setDefault(array $array, string|int $key, $default_value){
            return empty($array[$key]) || is_null($array[$key]) ? $default_value : $array[$key];
        }

        /**
         * This function is used to set values with default values if empty 
         * @param mixed $subject The subject to modify
         * @param mixed $default_value The default value to be entered
         * 
         * @return mixed The resulting value
         */
        protected function set_default($subject, $default_value){
            return empty($subject) || is_null($subject) ? $default_value : $subject;
        }

        /**
         * This function is used to replace an array key with a new key and remove the old key
         * @param array $array The array to be worked on
         * @param string|int $old_key The name of the key to be replaced
         * @param string|int $new_key The name of the new key
         * @return void returns nothing, only makes the change
         */
        protected function replaceKey(array &$array, int|string $old_key, int|string $new_key){
            if(isset($array[$old_key])){
                $array[$new_key] = $array[$old_key];
                unset($array[$old_key]);
            }
        }

        /**
         * Function to make keys from an array
         * @param array $data Array of data to make the keys from
         * @return array An array of keys 
         */
        private function makeKeys(array $data) :array{
            return array_key_exists(0,$data) ? 
                    $data : array_keys($data);
        }

        /**
         * Used during insert statements. Its used to check if a user data is set or not
         * @param array $input_array The input array
         * @param Database $connect The connection database, used to log a status
         * @return bool True if everything matches up, false if things fail
         */
        protected function checkInsert(array $input_array, Database &$connect) :bool{
            $response = true;
            $keys = $this->makeKeys($input_array);

            //loop through input array for the value
            foreach($keys as $key){
                if(array_search($key, $this->required_keys) === false){
                    $response = false;
                    $connect->setStatus("The field named '$key' was considered an invalid key for the request", true);
                    break;
                }
            }
            
            return $response;
        }

        /**
         * This is used to make checks on a user data to be processed. usually used
         * in the validate function
         * @param array $data The data to be checked
         * @param array $keys The keys is a list of array key names and their names in errors ["key" => ["name", "type"]]
         * @return bool|string returns true for a successful check and an error string for a non successful check
         */
        protected function check($data, $keys) :bool|string{
            //keys are in format ["key" => ["name", "type"]]
            $response = true;

            if(($response = $this->checkExistence($data, $keys)) === true){
                $response = $this->checkEmpty($data, $keys);                
            }

            return $response;
        }

        /**
         * This is to check if a key is present in an input data
         * @param array $data The data to be verified
         * @param array $keys The keys to be checked
         * @return bool|string returns true if no error or an error string
         */
        private function checkExistence($data, $keys){
            $data_keys = array_keys($keys);
            $response = true;

            foreach($data_keys as $key){
                $name = $keys[$key][0];

                if(!isset($data[$key])){
                    $response = "Your $name was not specified";
                    break;
                }
            }

            return $response;
        }

        /**
         * This is used to check if key was provided values
         */
        private function checkEmpty($data, $keys) :bool|string{
            $data_keys = array_keys($keys);
            $response = true;

            foreach($data_keys as $key){
                list($name, $type) = $keys[$key];

                if(empty($data[$key])){
                    $response = "Your $name was not provided";
                    break;
                }

                if($response === true && ($response = $this->checkType($data[$key], $name, $type)) !== true){
                    break;
                }
            }

            return $response;
        }

        private function checkType($value, $name, $type) :bool|string{
            $response = true;

            switch(strtolower($type)){
                case "int":
                    if(!ctype_digit($value)){
                        $response = ucfirst($name)." provided is not a number";
                    }
                    break;
                case "bool":
                    if(!ctype_digit($value) || $value > 1){
                        $response = ucfirst($name)." is supposed to be true/false";
                    }
            }

            return $response;
        }

        /**
         * Convert an array to suit the constructor
         * @param array $search_results The data to be converted
         * @return array The formated array
         */
        protected static function convertToConstruct(array $search_results) :array{
            if(isset($search_results[0]) && is_array($search_results[0])){
                $search_results = $search_results[0];
            }

            //attributes are in the format [key => type]
            foreach(static::$attributes as $attribute => $data_type){
                if(isset($search_results[$attribute])){
                    $search_results[$attribute] = static::convertValue($search_results[$attribute], $data_type);
                }
            }

            return $search_results;
        }

        /**
         * This is used in connection with the convertoconstruct to format types of an attribute
         * @param mixed $value This is the value passed
         * @param string $data_type This is the datatype of the value
         * @return mixed The formated value
         */
        private static function convertValue($value, string $data_type){
            switch(strtolower($data_type)){
                case "int":
                    $value = (int) $value; break;
                case "float":
                    $value = (float) $value; break;
                case "double":
                    $value = (double) $value; break;
                case "bool":
                    $value = (bool) $value; break;
                case "array":
                    $value = (array) $value; break;
                default:
                    $value = (string) $value;
            }

            return $value;
        }
    }