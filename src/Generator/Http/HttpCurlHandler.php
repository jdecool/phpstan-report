<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Generator\Http;

use RuntimeException;

class HttpCurlHandler
{
    /**
     * @param array<mixed> $data
     * @param array<string> $headers
     * @return array{http_code: int, response: string}
     */
    public function send(string $url, array $data, array $headers = []): array
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge(
            ['Content-Type: application/json'],
            $headers,
        ));

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);

            throw new RuntimeException("HTTP request failed: $error");
        }

        return [
            'http_code' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
            'response' => $response,
        ];
    }
}
