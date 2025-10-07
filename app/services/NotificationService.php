<?php
require_once __DIR__ . '/../models/NotificationModel.php';
require_once __DIR__ . '/../core/EventDispatcher.php';

class NotificationService
{
    private $model;

    public function __construct()
    {
        $this->model = new NotificationModel();
    }

    public function notify(int $userId, string $title, string $message, ?array $data = null)
    {
        return $this->model->create($userId, $title, $message, $data);
    }

    public function notifyBorrowed(array $payload)
    {
        // payload expected: ['user_id'=>int, 'book_id'=>int, 'book_title'=>string]
        $userId = (int)($payload['user_id'] ?? 0);
        if (!$userId) { return false; }
        $bookTitle = $payload['book_title'] ?? 'um livro';
        $title = 'Empréstimo realizado';
        $message = "Você emprestou: $bookTitle.";
        $data = ['book_id' => $payload['book_id'] ?? null];
        return $this->notify($userId, $title, $message, $data);
    }

    public function notifyReturned(array $payload)
    {
        $userId = (int)($payload['user_id'] ?? 0);
        if (!$userId) { return false; }
        $bookTitle = $payload['book_title'] ?? 'um livro';
        $title = 'Devolução registrada';
        $message = "Você devolveu: $bookTitle.";
        $data = ['book_id' => $payload['book_id'] ?? null];
        return $this->notify($userId, $title, $message, $data);
    }
}

// Register default listeners
$notifService = new NotificationService();
EventDispatcher::listen('book.borrowed', function ($payload) use ($notifService) {
    $notifService->notifyBorrowed($payload);
});

EventDispatcher::listen('book.returned', function ($payload) use ($notifService) {
    $notifService->notifyReturned($payload);
});
