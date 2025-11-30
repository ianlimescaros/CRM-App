<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../services/Logger.php';

class AiService
{
    private array $config;

    public function __construct()
    {
        $this->config = require __DIR__ . '/../config/config.php';
    }

    public function summarizeNotes(string $notes): array
    {
        $prompt = "Summarize these lead notes into clear bullet points:\n\n" . $notes;
        return $this->callModel($prompt);
    }

    public function suggestFollowup(string $leadName, string $context): array
    {
        $prompt = "Draft a friendly professional follow-up message for {$leadName} with this context:\n\n" . $context . "\n\nReturn only the message.";
        return $this->callModel($prompt);
    }

    private function callModel(string $prompt): array
    {
        if (!function_exists('curl_init')) {
            Logger::error('cURL extension is missing for AI service');
            return ['error' => 'Server missing cURL extension'];
        }

        $url = $this->config['llm']['url'] ?? '';
        $key = $this->config['llm']['key'] ?? '';
        $model = $this->config['llm']['model'] ?? 'gpt-4';

        if (!$url || !$key) {
            return ['error' => 'LLM configuration missing'];
        }

        $payload = [
            'model' => $model,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.4,
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $key,
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 20,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($response === false) {
            $err = curl_error($ch);
            curl_close($ch);
            Logger::error('AI call failed', ['error' => $err]);
            return ['error' => 'AI request failed'];
        }
        curl_close($ch);

        $data = json_decode($response, true);
        if ($httpCode >= 400 || !$data) {
            Logger::error('AI call bad response', ['status' => $httpCode, 'body' => $response]);
            return ['error' => 'AI service error'];
        }

        $text = $data['choices'][0]['message']['content'] ?? '';
        if (!$text) {
            Logger::error('AI call missing content', ['status' => $httpCode]);
            return ['error' => 'AI response empty'];
        }

        return ['text' => trim($text)];
    }
}
