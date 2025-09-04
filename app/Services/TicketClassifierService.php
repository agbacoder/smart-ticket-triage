<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Ticket;

class TicketClassifierService
{
    public function classify(Ticket $ticket): array
    {
        if (!config('services.openai.classify_enabled', false)) {
            return $this->dummyClassification();
        }

        $client = \OpenAI::client(config('services.openai.api_key'));
        $prompt = "Classify the following support ticket into JSON with keys: category, explanation, confidence (0-1).
        Subject: {$ticket->subject}
        Body: {$ticket->body}";

        $response = $client->chat()->create([
            'model' => 'gpt-4.1',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a ticket classifier. Return valid JSON only.'],
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        $content = $response->choices[0]->message->content ?? '{}';
        $parsed = json_decode($content, true);

        return [
            'category'    => $parsed['category'] ?? null,
            'explanation' => $parsed['explanation'] ?? null,
            'confidence'  => isset($parsed['confidence']) ? (float) $parsed['confidence'] : null,
        ];
    }

    private function dummyClassification(): array
    {
        $categories = ['billing', 'technical', 'general'];
        $category = $categories[array_rand($categories)];

        return [
            'category'    => $category,
            'explanation' => "Dummy explanation for {$category} issue.",
            'confidence'  => round(mt_rand(50, 95) / 100, 2), 
        ];
    }
}
