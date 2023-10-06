<?php

    function enableCORS() {
        // Allow requests from any origin
        header("Access-Control-Allow-Origin: *");

        // Allow credentials to be sent with the request (e.g., cookies)
        header("Access-Control-Allow-Credentials: true");

        // Set the maximum age (in seconds) for the preflight request to be cached
        header("Access-Control-Max-Age: 1000");

        // Handle preflight requests (OPTIONS request)
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            handlePreflightRequest();
        }
    }

    function handlePreflightRequest() {
        // Allow the following HTTP methods
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE, PATCH");

        // Allow the following headers in the request
        header("Access-Control-Allow-Headers: Accept, Content-Type, Content-Length, Accept-Encoding, X-CSRF-Token, Authorization");

        // Respond to the preflight request with a 200 status
        http_response_code(200);
        exit;
    }

    // Check if the request has an HTTP Origin header
    if (isset($_SERVER["HTTP_ORIGIN"])) {
        enableCORS();
    }
?>