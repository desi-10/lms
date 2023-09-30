<?php 
    require_once "../autoloader.php";

    use App\Student;

    //response variables
    $message = null; $error = true;

    if(isset($_POST["submit"])){
        $submit = $_POST["submit"];

        if($submit == "student_login"){
            $student = new Student;

            //create a session variable if student is logged in
            $message = $student->login();

            if($message === true){
                $error = false;
            }
        }
    }else{
        $message = "No submission was detected";
    }

    //grab the response messages
    $response = [
        "error" => $error, "message" => $message
    ];

    //send a json encoded response
    // header("Content-Type: appliction/json");
    echo json_encode($response);