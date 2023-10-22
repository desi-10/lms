<?php 
    
    spl_autoload_register(function($class){
        $class = str_replace("\\",DIRECTORY_SEPARATOR, $class);
        $class = str_replace("App","app", $class);
        $class = str_replace("Trait","trait", $class);
        require_once "../../$class.php";
    });