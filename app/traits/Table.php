<?php
    declare(strict_types=1);

    namespace App\Traits;

    trait Table
    {
        protected string|array $table;
        protected string|array $columns;
        protected string|array $where;

        protected string $class_table;

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
    }