<?php

class NotificationService {
    private $model;

    public function __construct() {
        $this->model = new NotificationModel();
    }

    public function notify(int $userId, string $title, string $message, ?array $data = null) {
        return $this->model->create($userId, $title, $message, $data);
    }

    public function notifyBorrowed(array $payload) {
        $userId = (int)($payload['user_id'] ?? 0);
        if (!$userId) { 
            return false; 
        }
        
        $bookTitle = $payload['book_title'] ?? 'um livro';
        $title = 'Empréstimo realizado';
        $message = "Você emprestou: $bookTitle.";
        $data = ['book_id' => $payload['book_id'] ?? null, 'type' => 'borrowed'];
        
        return $this->notify($userId, $title, $message, $data);
    }

    public function notifyReturned(array $payload) {
        $userId = (int)($payload['user_id'] ?? 0);
        if (!$userId) { 
            return false; 
        }
        
        $bookTitle = $payload['book_title'] ?? 'um livro';
        $title = 'Devolução registrada';
        $message = "Você devolveu: $bookTitle.";
        $data = ['book_id' => $payload['book_id'] ?? null, 'type' => 'returned'];
        
        return $this->notify($userId, $title, $message, $data);
    }

    public function notifyRequested(array $payload) {
        $userId = (int)($payload['user_id'] ?? 0);
        if (!$userId) { 
            return false; 
        }
        
        $bookTitle = $payload['book_title'] ?? 'um livro';
        $title = 'Solicitação enviada';
        $message = "Sua solicitação para emprestar '$bookTitle' foi enviada. Aguarde a aprovação.";
        $data = ['book_id' => $payload['book_id'] ?? null, 'type' => 'requested'];
        
        return $this->notify($userId, $title, $message, $data);
    }

    public function notifyApproved(array $payload) {
        $userId = (int)($payload['user_id'] ?? 0);
        if (!$userId) { 
            return false; 
        }
        
        $bookTitle = $payload['book_title'] ?? 'um livro';
        $title = 'Solicitação aprovada';
        $message = "Sua solicitação para '$bookTitle' foi aprovada! Você pode retirar o livro.";
        $data = ['book_id' => $payload['book_id'] ?? null, 'type' => 'approved'];
        
        return $this->notify($userId, $title, $message, $data);
    }

    public function notifyRejected(array $payload) {
        $userId = (int)($payload['user_id'] ?? 0);
        if (!$userId) { 
            return false; 
        }
        
        $bookTitle = $payload['book_title'] ?? 'um livro';
        $title = 'Solicitação rejeitada';
        $message = "Sua solicitação para '$bookTitle' foi rejeitada. Entre em contato para mais informações.";
        $data = ['book_id' => $payload['book_id'] ?? null, 'type' => 'rejected'];
        
        return $this->notify($userId, $title, $message, $data);
    }

    public function processEvent(array $eventData) {
        $eventType = $eventData['type'] ?? '';
        
        switch ($eventType) {
            case 'book.borrowed':
                return $this->notifyBorrowed($eventData);
            case 'book.returned':
                return $this->notifyReturned($eventData);
            case 'book.requested':
                return $this->notifyRequested($eventData);
            case 'book.approved':
                return $this->notifyApproved($eventData);
            case 'book.rejected':
                return $this->notifyRejected($eventData);
            default:
                return false;
        }
    }
}