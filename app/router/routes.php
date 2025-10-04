<?php


$userRoutes = [
    "/" => "BookController@listBooks",
    "/search" => "BookController@searchBooks",
    "/borrow/{id}" => "BookController@borrowBook",
    "/return/{id}" => "BookController@returnBook",
    "/details/{id}" => "BookController@viewBookDetails",
    "/login" => "AuthController@showLogin",
    "/register" => "AuthController@showRegister",
    "/profile" => "AuthController@showProfile",
    "/api/auth/register" => "AuthController@register",
    "/api/auth/login" => "AuthController@login",
    "/api/auth/logout" => "AuthController@logout",
    "/api/auth/me" => "AuthController@me",
    "/historico" => "BookController@viewHistory",
];

$adminRoutes = [
    ...$userRoutes,
    "/api/books" => "BookController@createBook",
];