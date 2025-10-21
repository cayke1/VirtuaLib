<?php
/**
 * Bootstrap file for PHPUnit tests
 */

// Define a mock Database class for testing
if (!class_exists('Database')) {
    class Database {
        public function getConnection() {
            return null;
        }
    }
}

// Define a mock EventDispatcher class for testing
if (!class_exists('EventDispatcher')) {
    class EventDispatcher {
        public static function dispatch($event, $data = []) {
            // Mock implementation - do nothing
        }
    }
}

// Load environment variables if needed
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Set default environment variables for testing
$_ENV['DB_HOST'] = $_ENV['DB_HOST'] ?? 'localhost';
$_ENV['DB_PORT'] = $_ENV['DB_PORT'] ?? '3306';
$_ENV['DB_NAME'] = $_ENV['DB_NAME'] ?? 'test_db';
$_ENV['DB_USER'] = $_ENV['DB_USER'] ?? 'test_user';
$_ENV['DB_PASSWORD'] = $_ENV['DB_PASSWORD'] ?? 'test_password';
