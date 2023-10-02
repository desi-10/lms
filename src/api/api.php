<?php
// use App\Database;
    declare(strict_types=1);
    require("autoload.php");

    use App\Controller\ResponseController;
    use App\Error;

    set_exception_handler([Error::class, "errorHandler"]);
    // set_exception_handler([Error::class, "handleError"]);

    header("Content-type: application/json");

    $accepted = [
        "user","course","student", "instructor"
    ];
    $parts = explode("/",$_SERVER["REQUEST_URI"]);

    //remove any leading string and start with api/
    if(array_search("api",$parts) !== false){
        $parts = array_splice($parts, array_search("api", $parts)+1);
    }
    
    if(array_search($parts[0], $accepted) === false){
        http_response_code(404);
        exit(1);
    }

    $class = "\App\\".$parts[0];
    $id = $parts[1] ?? null;

    $response = new ResponseController;

    $response->processRequest($_SERVER["REQUEST_METHOD"], $class, $id);