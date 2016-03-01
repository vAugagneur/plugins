<?php
// Just mirror requests received.

$ret = json_encode(array(
        'headers' => apache_request_headers(),
        'method'  => $_SERVER['REQUEST_METHOD'],
        'request' => $_SERVER['REQUEST_URI'],
        'body'    => file_get_contents('php://input')
    ),
    JSON_PRETTY_PRINT
);

header('Content-Type: application/json; charset=utf-8');
header('Content-Length: ' . strlen($ret));
echo $ret;
