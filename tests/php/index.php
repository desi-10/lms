<?php
    require("../../autoloader.php");

    //handle exceptions
    set_exception_handler("Error::handleError");
    set_exception_handler("Error::handleException");

    use App\Student;
    
    $_POST["index_number"] = "0323080224";
    $_POST["password"] = "Password@1";
    $_POST["submit"] = "student_login";

    //student_login
    $student = new Student();

    echo "<pre>";
    var_dump($student->login(), $student->logs());
    echo "</pre>";