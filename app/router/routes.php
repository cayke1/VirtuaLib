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
    "/dashboard" => "BookController@viewDashboard",
    "/api/notifications" => "NotificationsController@listForUser",
    "/api/notifications/unread-count" => "NotificationsController@unreadCount",
    "/api/notifications/{id}/read" => "NotificationsController@markAsRead",
    "/api/notifications/{id}/delet" => "NotificationsController@delete",
    "/api/notifications/mark-all-read" => "NotificationsController@markAllRead",
    "/api/stats/general" => "StatsController@getGeneralStats",
    "/api/stats/borrows-by-month" => "StatsController@getBorrowsByMonth",
    "/api/stats/top-books" => "StatsController@getTopBooks",
    "/api/stats/books-by-category" => "StatsController@getBooksByCategory",
    "/api/stats/recent-activities" => "StatsController@getRecentActivities",
];

$adminRoutes = [
    ...$userRoutes,
    "/api/books" => "BookController@createBook",
];