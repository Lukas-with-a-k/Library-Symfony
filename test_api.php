<?php

use Symfony\Component\HttpClient\HttpClient;

require 'vendor/autoload.php';

$httpClient = HttpClient::create();

$url = 'https://www.googleapis.com/books/v1/volumes?q=fiction&startIndex=0&maxResults=40';

try {
    $response = $httpClient->request('GET', $url, [
        'timeout' => 60,
    ]);
    $data = $response->toArray();
    echo "Response received: " . count($data['items']) . " books\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
