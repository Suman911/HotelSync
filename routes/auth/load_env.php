<?php

$filePath = "./ErE7AB0.z_2m7rWH10XkBkW-kufaGyrzcSX81qnbBWzZk9xTvX.env";

if (!file_exists($filePath)) {
    throw new Exception(".env file not found at: $filePath");
}

$lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

foreach ($lines as $line) {
    // Ignore comments
    if (strpos(trim($line), '#') === 0) {
        continue;
    }

    // Split the line into key and value
    [$name, $value] = explode('=', $line, 2);

    // Remove quotes if present
    $name = trim($name);
    $value = trim($value);

    // Handle values wrapped in double/single quotes
    $value = trim($value, "'\"");

    // Set the environment variable
    $_ENV[$name] = $value;
}

var_dump($_ENV);