<?php 
    require_once "../autoloader.php";

    use App\Student;
    use App\Database;

    $_POST["index_number"] = "0123456789";
    $_POST["password"] = "password";

    // if(isset($_POST["submit"])){
        $student = new Student();
        $db = new Database;
        $users = [
            "username" => "MatrixMe",
            "lname" => "Afosah",
            "oname" => "Seth Boye",
            "user_role" => 1
        ];
    // }
    
    echo "<pre>";
    var_dump($student->login(), $student->createIndexNumber());
    // var_dump($db->insert("users",$users), $db->getLogs(), $db->status());
    // var_dump($db->fetch("id","users"), $db->getLogs());
    echo "</pre>";