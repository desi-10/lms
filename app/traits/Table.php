<?php
    declare(strict_types=1);

    namespace App\Traits;

use App\Database;

    trait Table
    {
        /** @var string|array $table The table for the class methods */
        private string|array $table;

        /** @var string|string[] $columns The columns from the table */
        private string|array $columns;

        /** @var string|string[] $where This is used for conditions in a search method */
        private string|array $where;

        /** @var string $class_table The general table for a class */
        private string $class_table;

        /** @var string[] $required_keys The necessary keys to be seen from input elements usually for insertion */
        private array $required_keys;

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
        private function setDefault(array $array, string|int $key, $default_value){
            return empty($array[$key]) || is_null($array[$key]) ? $default_value : $array[$key];
        }

        /**
         * This function is used to set values with default values if empty 
         * @param mixed $subject The subject to modify
         * @param mixed $default_value The default value to be entered
         * 
         * @return mixed The resulting value
         */
        private function set_default($subject, $default_value){
            return empty($subject) || is_null($subject) ? $default_value : $subject;
        }

        /**
         * This function is used to replace an array key with a new key and remove the old key
         * @param array $array The array to be worked on
         * @param string|int $old_key The name of the key to be replaced
         * @param string|int $new_key The name of the new key
         * @return void returns nothing, only makes the change
         */
        private function replaceKey(array &$array, int|string $old_key, int|string $new_key){
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
        private function checkInsert(array $input_array, Database &$connect) :bool{
            $response = true;

            //stop processing if there is actually no input
            if(empty($input_array)){
                $connect->setStatus("No processable input fields were provided", true);
                return false;
            }

            //make sure required keys are available
            foreach($this->required_keys as $key){
                if(!isset($input_array[$key])){
                    $response = false;
                    $connect->setStatus("The required field name '$key' has not been specified");
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
        private function check($data, $keys) :bool|string{
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
                    $value = (string) $value;
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

        /**
         * This function is used to remove keys from an array and also can spit out the values or not
         * @param array $array The array to be processed
         * @param array $keys The keys to be removed
         * @param bool $spit_values This is used to return an array of the values of the keys being removed
         * @return null|array doesnt return any value by default, but array if spit_values is true
         */
        private function removeKeys(array &$array, array $keys, bool $spit_values = false) :null|array{
            $response = null;
            $reserved_values = [];

            foreach($keys as $key){
                if(isset($array[$key])){
                    //reserve the value and remove the value from the array
                    $reserved_values[$key] = $array[$key];
                    unset($array[$key]);
                }
            }

            if($spit_values){
                $response = $reserved_values;
            }
            return $response;
        }

        /**
         * Check if a date string is in the right format
         * @param string $value The date value
         * @param bool $start This tells if its the start or end date
         * @return bool formats date on true if its a valid date or return false if otherwise
         */
        private function checkDate(string &$value, bool $start = true) :bool{
            $hasTime = preg_match('/\b(?:\d{1,2}:){1,2}\d{1,2}\b/', $value);

            if(strtotime($value)){
                //add time (+1hr) if it does not have one
                if(!$hasTime){
                    if($start){
                        $value .= date(" H:i:s");
                    }else{
                        $value .= date(" H:i:s", strtotime("1 hour"));
                    }
                }

                //format date in datetime format
                $value = date("Y-m-d H:i:s", strtotime($value));
                return true;
            }else{
                return false;
            }
        }
    }