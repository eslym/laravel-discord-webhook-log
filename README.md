# A Discord webhook handler for monolog in laravel
```php
<?php
# config/logging.php

return [
// ...

    'channels' => [

// ...

        'discord' => [
            'driver' => 'monolog',
            'handler' => \Eslym\Laravel\Log\DiscordWebhook\DiscordWebhookHandler::class,
            'with' => [
                'webhook' => env('DISCORD_LOG_WEBHOOK'),
                'message' => env('DISCORD_LOG_MESSAGE'),
            ],
            'formatter' => \Monolog\Formatter\LineFormatter::class,
            'formatter_with' => [
                'format' => '%message% %context% %extra%',
                'allowInlineLineBreaks' => true,
                'ignoreEmptyContextAndExtra' => true,
            ],
        ],

// ...

    ],

// ...
]
```

```dotenv
# .env
DISCORD_LOG_WEBHOOK="webhook url"
DISCORD_LOG_MESSAGE="Hey <@ (discord user id here) >! here is your log."
```