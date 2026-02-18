<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsGateway
{
    // ✅ Render {{placeholders}} and {placeholders} in SMS template
    public function renderTemplate(string $template, array $data = []): string
    {
        $message = (string) $template;

        foreach ($data as $key => $value) {
            $val = (string) ($value ?? '');
            $message = str_replace('{{' . $key . '}}', $val, $message);
            $message = str_replace('{' . $key . '}', $val, $message);
        }

        // remove leftover placeholders like {{something}} or {something}
        $message = preg_replace('/{{\s*[^}]+\s*}}/', '', $message);
        $message = preg_replace('/{\s*[^}]+\s*}/', '', $message);

        // normalize spaces
        $message = trim(preg_replace('/\s+/', ' ', $message));

        return $message;
    }

    public function send(string $to, string $message, array $settings = []): array
    {
        $gatewayUrl = trim((string) ($settings['sms_gateway_url'] ?? ''));
        $apiToken = trim((string) ($settings['sms_api_token'] ?? '')); // Text.lk token
        $apiKey   = trim((string) ($settings['sms_api_key'] ?? ''));   // optional

        if ($gatewayUrl === '' && $apiToken !== '') {
            $gatewayUrl = 'https://app.text.lk/api/v3/';
        }

        if ($gatewayUrl === '') {
            return ['ok' => false, 'error' => 'SMS gateway URL not configured.'];
        }

        $method = strtoupper(trim((string) ($settings['sms_http_method'] ?? 'POST')));
        if (!in_array($method, ['GET', 'POST'], true)) {
            $method = 'POST';
        }

        // Your system fields (keep generic)
        $paramTo       = trim((string) ($settings['sms_param_to'] ?? 'to')) ?: 'to';
        $paramMessage  = trim((string) ($settings['sms_param_message'] ?? 'message')) ?: 'message';
        $paramSenderId = trim((string) ($settings['sms_param_sender_id'] ?? 'sender_id')) ?: 'sender_id';

        $senderId = trim((string) ($settings['sms_sender_id'] ?? ''));

        // Extra params (key=value lines)
        $extra = $this->parseExtraParams((string) ($settings['sms_extra_params'] ?? ''));

        $isTextlk = str_contains($gatewayUrl, 'app.text.lk');

        // -----------------------------------------
        // ✅ TEXT.LK V3 MODE
        // -----------------------------------------
        if ($isTextlk) {
            $sendUrl = 'https://app.text.lk/api/v3/sms/send';

            if ($apiToken === '') {
                return ['ok' => false, 'error' => 'Text.lk API Token is missing (sms_api_token).'];
            }
            if ($senderId === '') {
                return ['ok' => false, 'error' => 'Text.lk Sender ID is missing (sms_sender_id).'];
            }

            $payload = [
                'recipient' => $to,
                'sender_id' => $senderId,
                'type'      => $extra['type'] ?? 'plain',
                'message'   => $message,
            ];

            if (!empty($extra['schedule_time'])) {
                $payload['schedule_time'] = $extra['schedule_time'];
            }
            if (!empty($extra['dlt_template_id'])) {
                $payload['dlt_template_id'] = $extra['dlt_template_id'];
            }

            Log::info('SMS gateway request (Text.lk v3)', [
                'url' => $sendUrl,
                'method' => 'POST',
                'payload_keys' => array_keys($payload),
            ]);

            try {
                $response = Http::timeout(15)
                    ->withToken($apiToken)
                    ->acceptJson()
                    ->asJson()
                    ->post($sendUrl, $payload);
            } catch (\Throwable $e) {
                Log::error('SMS send exception (Text.lk v3)', [
                    'url' => $sendUrl,
                    'error' => $e->getMessage(),
                ]);
                return ['ok' => false, 'error' => $e->getMessage()];
            }

            if ($response->successful()) {
                return ['ok' => true, 'status' => $response->status(), 'body' => $response->body()];
            }

            Log::error('SMS send failed (Text.lk v3)', [
                'url' => $sendUrl,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return ['ok' => false, 'status' => $response->status(), 'body' => $response->body()];
        }

        // -----------------------------------------
        // GENERIC HTTP MODE
        // -----------------------------------------
        $gatewayUrl = rtrim($gatewayUrl, '/');

        if (str_contains($gatewayUrl, '/api/http') && !str_ends_with($gatewayUrl, '/send')) {
            $gatewayUrl .= '/send';
        }

        $payload = [
            $paramTo      => $to,
            $paramMessage => $message,
        ];

        if ($senderId !== '') {
            $payload[$paramSenderId] = $senderId;
        }

        if ($apiKey !== '') {
            $payload['api_key'] = $apiKey;
        }

        foreach ($extra as $k => $v) {
            if ($k !== '') {
                $payload[$k] = $v;
            }
        }

        Log::info('SMS gateway request (generic)', [
            'url' => $gatewayUrl,
            'method' => $method,
            'payload_keys' => array_keys($payload),
        ]);

        try {
            if ($method === 'GET') {
                $response = Http::timeout(15)->get($gatewayUrl, $payload);
            } else {
                $response = Http::timeout(15)->asForm()->post($gatewayUrl, $payload);
            }
        } catch (\Throwable $e) {
            Log::error('SMS send exception (generic)', [
                'url' => $gatewayUrl,
                'method' => $method,
                'error' => $e->getMessage(),
            ]);
            return ['ok' => false, 'error' => $e->getMessage()];
        }

        if ($response->successful()) {
            return ['ok' => true, 'status' => $response->status(), 'body' => $response->body()];
        }

        Log::error('SMS send failed (generic)', [
            'url' => $gatewayUrl,
            'method' => $method,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return ['ok' => false, 'status' => $response->status(), 'body' => $response->body()];
    }

    private function parseExtraParams(string $raw): array
    {
        $rows = preg_split('/\r\n|\r|\n/', $raw) ?: [];
        $params = [];

        foreach ($rows as $row) {
            $line = trim($row);
            if ($line === '' || !str_contains($line, '=')) continue;

            [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
            $key = trim($key);
            $value = trim($value);

            if ($key !== '') $params[$key] = $value;
        }

        return $params;
    }
}
