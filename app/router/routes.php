<?php


$userRoutes = [
    "/" => "BookController@listBooks",
    "/search" => "BookController@searchBooks",
    "/borrow/{id}" => "BookController@borrowBook",
    "/return/{id}" => "BookController@returnBook",
    "/details/{id}" => "BookController@viewBookDetails",
    "/api/auth/register" => "AuthController@register",
    "/api/auth/login" => "AuthController@login",
    "/api/auth/logout" => "AuthController@logout",
    "/api/auth/me" => "AuthController@me",
];

$adminRoutes = [
    ...$userRoutes,
    "/api/books" => "BookController@createBook",
];