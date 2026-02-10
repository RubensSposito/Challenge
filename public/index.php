<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Http\HttpKernel;

$kernel = HttpKernel::boot();
$response = $kernel->handleRequest();

http_response_code($response->getStatusCode());

foreach ($response->getHeaders() as $name => $values) {
    foreach ($values as $value) {
        header($name . ': ' . $value, false);
    }
}

echo (string) $response->getBody();
