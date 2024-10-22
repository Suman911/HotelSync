<?php
final class EnvLoader
{
    public static function load(): void
    {
        $filePath = __DIR__.'/.env';
        if (!file_exists($filePath)) {
            throw new Exception("Environment file not found: $filePath");
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            list($key, $value) = explode('=', $line, 2);

            $key = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}
