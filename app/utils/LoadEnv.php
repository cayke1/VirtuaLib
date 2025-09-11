<?php
class LoadEnv
{
    private static $loaded = false;

    public static function loadAll($path): void
    {
        if (self::$loaded) {
            return;
        }

        if ($path === null) {
            $path = dirname(__DIR__, 2) . '/.env';
        }

        if (!file_exists($path)) {
            throw new RuntimeException(".env não encontrado em: $path");
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {

            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            [$name, $value] = array_map('trim', explode('=', $line, 2));

            $value = trim($value, "\"'");

            $_ENV[$name] = $value;
            putenv("$name=$value");
        }

        self::$loaded = true;
    }

    public static function get(string $name): ?string
    {
        return $_ENV[$name] ?? getenv($name) ?: null;
    }
}
