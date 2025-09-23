<?php

$routes = [
    "/" => "BookController@listBooks",
    "/search" => "BookController@searchBooks",
    "/return/{id}" => "BookController@returnBook",
];