<?php 
    
    spl_autoload_register(function($class){
        // $class_name = explode("\\", $class)[1];
        require_once "../../$class.php";
    });