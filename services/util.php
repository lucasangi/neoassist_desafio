<?php

function response( $data, $code = 200 ){
    header("Content-Type: application/json; charset=utf-8");
    http_response_code($code);
    echo json_encode( $data );
    die();  
}

function validateDate($date, $format = 'Y-m-d'){
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}