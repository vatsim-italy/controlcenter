<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class DiscordNotifier
{
    public static function send($title, $description, $type = 'info', $fields = [])
    {
        $webhook = config('services.discord.webhook');

        if (!$webhook) return;

        $payload = [
            'embeds' => [[
                'title' => $title,
                'description' => $description,
                'color' => self::color($type),
                'fields' => collect($fields)->map(fn ($v, $k) => [
                    'name' => $k,
                    'value' => $v,
                    'inline' => true
                ])->values(),
                'timestamp' => now()->toIso8601String(),
            ]]
        ];

        Http::post($webhook, $payload);
    }

    private static function color($type)
    {
        return match ($type) {
            'success' => 0x2ecc71,
            'warning' => 0xf1c40f,
            'danger' => 0xe74c3c,
            default => 0x3498db,
        };
    }
}
