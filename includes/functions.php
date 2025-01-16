<?php
function getEnvValue($key) {
    $envFile = __DIR__ . '/../.env';
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false) {
                list($envKey, $envValue) = explode('=', $line, 2);
                if (trim($envKey) === $key) {
                    return trim($envValue);
                }
            }
        }
    }
    return null;
}
