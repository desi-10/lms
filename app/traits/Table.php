<?php
    declare(strict_types=1);

    namespace App\Traits;

    trait Table
    {
        protected string|array $table;
        protected string|array $columns;
        protected string|array $where;

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
    }