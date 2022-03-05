<?php

namespace Eslym\Laravel\Log\DiscordWebhook\Tests;

use Eslym\Laravel\Log\DiscordWebhook\Builder\DumperEmbedBuilder;
use Eslym\Laravel\Log\DiscordWebhook\DiscordWebhookHandler;
use Illuminate\Config\Repository as ConfigRepo;
use Monolog\Formatter\LineFormatter;
use Orchestra\Testbench\TestCase;

class BaseTestCase extends TestCase
{
    protected function defineEnvironment($app)
    {
        /** @var ConfigRepo $config */
        $config = $app->make('config');

        $config->set('logging', [
            'channels' => [
                'discord' => $this->buildChannelConfig(),
                'discord-dumper' => $this->buildChannelConfig([
                    'embed' => DumperEmbedBuilder::class,
                ])
            ]
        ]);
    }

    protected function buildChannelConfig($options = null): array
    {
        return [
            'driver' => 'monolog',
            'handler' => DiscordWebhookHandler::class,
            'with' => [
                'webhook' => env('DISCORD_LOG_WEBHOOK'),
                'message' => env('DISCORD_LOG_MESSAGE'),
                'options' => $options,
            ],
            'formatter' => LineFormatter::class,
            'formatter_with' => [
                'format' => '%message% %context% %extra%',
                'allowInlineLineBreaks' => true,
                'ignoreEmptyContextAndExtra' => true,
            ],
        ];
    }
}