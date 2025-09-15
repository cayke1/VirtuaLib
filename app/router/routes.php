<?php

$routes = [
    "/" => "HomeController@index",
    "/books" => "BookController@index",
    "/books/{id}" => "BookController@show",
    "/books/search" => "BookController@search",
];