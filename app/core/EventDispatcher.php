<?php
class EventDispatcher
{
    private static $listeners = [];

    public static function listen(string $eventName, callable $listener)
    {
        if (!isset(self::$listeners[$eventName])) {
            self::$listeners[$eventName] = [];
        }
        self::$listeners[$eventName][] = $listener;
    }

    public static function dispatch(string $eventName, $payload = null)
    {
        if (empty(self::$listeners[$eventName])) {
            return;
        }
        foreach (self::$listeners[$eventName] as $listener) {
            try {
                call_user_func($listener, $payload);
            } catch (Exception $e) {
                error_log('Event listener error for ' . $eventName . ': ' . $e->getMessage());
            }
        }
    }
}
