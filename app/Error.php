<?php 
    namespace App;

    use ErrorException;
    use Throwable;

    class Error
    {
        public static function errorHandler(Throwable $th) :void{
            http_response_code(500);
            echo json_encode([
                "code" => $th->getCode(),
                "file" => $th->getFile(),
                "line" => $th->getLine(),
                "message" => $th->getMessage()
            ]);
        }

        public static function handleError(int $errno, string $errstr, string $errfile, int $errline){
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        }
    }