<?php 
    require_once "../autoloader.php";

    use App\Student;
    use App\Database;

    // $_POST["index_number"] = "0123456789";
    $_POST["index_number"] = "0323080224";
    $_POST["password"] = "Password@1";

    // if(isset($_POST["submit"])){
        $student = new Student;
        $db = new Database;
        $users = [
            "username" => "MatrixMe",
            "lname" => "Afosah",
            "oname" => "Seth Boye",
            "user_role" => 3
        ];
    // }
    
    echo "<pre>";
    // var_dump($student->find(1));
    var_dump($student->login(), $student->logs());
    // var_dump($student->create($users), $student->logs());
    echo "</pre>";