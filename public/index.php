<?php 
    require_once "../autoloader.php";

    use App\Student;

    $_POST["index_number"] = "0123456789";
    $_POST["password"] = "password";

    // if(isset($_POST["login"])){
        $student = new Student();
    // }
    
    echo "<pre>";
    var_dump($student->login());
    echo "</pre>";