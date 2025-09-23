<?php

$routes = [
    "/" => "BookController@listBooks",
    "/search" => "BookController@searchBooks",
    "/borrow" => "BookController@borrowBook",
    "/return" => "BookController@returnBook",
];