<?php

function load_env_file($file_path) {
    if (!file_exists($file_path)) {
        return false;
    }

    $lines = file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Parse key-value pairs
        [$name, $value] = array_map('trim', explode('=', $line, 2));
        $value = trim($value, '"\''); // Remove quotes around values, if any

        // Set the value in the environment
        if (!array_key_exists($name, $_ENV)) {
            $_ENV[$name] = $value;
        }

        // Optional: Use `putenv()` for compatibility with `getenv()`
        putenv("$name=$value");
    }

    return true;
}
