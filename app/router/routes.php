<?php

$userRoutes = [
    "/" => "BookController@listBooks",
    "/search" => "BookController@searchBooks",
    "/request/{id}" => "BookController@requestBook",
    "/return/{id}" => "BookController@returnBook",
    "/details/{id}" => "BookController@viewBookDetails",
    "/login" => "AuthController@showLogin",
    "/register" => "AuthController@showRegister",
    "/profile" => "AuthController@showProfile",
    "/api/auth/register" => "AuthController@register",
    "/api/auth/login" => "AuthController@login",
    "/api/auth/logout" => "AuthController@logout",
    "/api/auth/me" => "AuthController@me",
    
    
    "/api/notifications" => "NotificationsController@listForUser",
    "/api/notifications/unread-count" => "NotificationsController@unreadCount",
    "/api/notifications/{id}/read" => "NotificationsController@markAsRead",
    "/api/notifications/{id}/delete" => "NotificationsController@delete",
    "/api/notifications/mark-all-read" => "NotificationsController@markAllRead",
    "/api/stats/general" => "StatsController@getGeneralStats",
    "/api/stats/borrows-by-month" => "StatsController@getBorrowsByMonth",
    "/api/stats/top-books" => "StatsController@getTopBooks",
    "/api/stats/books-by-category" => "StatsController@getBooksByCategory",
    "/api/stats/recent-activities" => "StatsController@getRecentActivities",
    "/api/stats/user-profile" => "StatsController@getUserProfileStats",
];

$adminRoutes = [
    ...$userRoutes,
    "/api/books" => "BookController@createBook",
    "/api/approve/{id}" => "BookController@approveBorrow",
    "/api/reject/{id}" => "BookController@rejectRequest",
    "/dashboard" => "BookController@viewDashboard",
    "/historico" => "BookController@viewHistory",
];