<?php
// use App\Database;
    
    declare(strict_types=1);
    require("api_access.php");
    require("autoload.php");

    use App\Controller\ResponseController;
    use App\Error;
    use App\Auth;

    set_error_handler([Error::class, "handleError"]);
    set_exception_handler([Error::class, "errorHandler"]);

    header("Content-type: application/json");

    //enable authorization
    Auth::auth();

    $accepted = [
        "user","course","student", "instructor", "program",
        "quiz", "question", "questionoptions", "assignment"
    ];
    
    $parts = explode("/",$_SERVER["REQUEST_URI"]);

    //remove any leading string and start with api/
    if(array_search("api",$parts) !== false){
        $parts = array_splice($parts, array_search("api", $parts)+1);
    }
    
    if(in_array($parts[0], $accepted) === false){
        http_response_code(404);
        exit(1);
    }

    $class = "\App\\".$parts[0];
    $id = $parts[1] ?? null;
    $additional = $parts[2] ?? null;

    $response = new ResponseController;

    $response->processRequest($_SERVER["REQUEST_METHOD"], $class, $id, $additional);