<?php 
    declare(strict_types=1);
    require("api_access.php");
    require("autoload.php");

    use App\Controller\ResponseController;
    use App\Error;

    set_error_handler([Error::class, "handleError"]);
    set_exception_handler([Error::class, "errorHandler"]);

    header("Content-type: application/json");

    //request types
    $fetch = ["GET"];
    $create = ["POST"];
    $update = ["PATCH"];
    $delete = ["DELETE"];
    $all = array_merge($fetch, $create, $update, $delete);

    //server request data
    $request_method = $_SERVER["REQUEST_METHOD"];
    $request_uri = $_SERVER["REQUEST_URI"];

    //api endpoints
    $endpoints = [
        "/api/users" => [
            "requests" => $all,
            "class_name" => "user"
        ],
        "/api/student" => [
            "requests" => $all,
            "class_name" => "student"
        ],
        "/api/instructor" => [
            "requests" => $all,
            "class_name" => "instructor"
        ]
    ];