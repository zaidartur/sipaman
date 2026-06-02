<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class StarSenderClient
{
    public function isConfigured(): bool
    {
        return filled($this->apiKey());
    }

    public function sendText(string $to, string $body): array
    {
        $apiKey = $this->apiKey();

        if (! $apiKey) {
            throw new RuntimeException('API key StarSender belum diisi.');
        }

        $response = Http::timeout((int) config('services.starsender.timeout', 15))
            ->acceptJson()
            ->withHeaders([
                'Authorization' => $apiKey,
            ])
            ->post((string) config('services.starsender.endpoint'), [
                'messageType' => 'text',
                'to' => $to,
                'body' => $body,
            ]);

        $payload = $response->json();
        $success = is_array($payload) ? (bool) ($payload['success'] ?? false) : $response->successful();

        if ($response->failed() || ! $success) {
            $message = is_array($payload) ? ($payload['message'] ?? null) : null;

            throw new RuntimeException('StarSender gagal mengirim pesan: ' . ($message ?: $response->body()));
        }

        return [
            'status' => $response->status(),
            'body' => $payload ?? $response->body(),
        ];
    }

    private function apiKey(): ?string
    {
        $apiKey = config('services.starsender.api_key');

        return is_string($apiKey) && trim($apiKey) !== '' ? trim($apiKey) : null;
    }
}
