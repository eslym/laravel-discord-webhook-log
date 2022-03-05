<?php

namespace Eslym\Laravel\Log\DiscordWebhook\Builder;

use Carbon\Carbon;
use Eslym\Laravel\Log\DiscordWebhook\Contracts\EmbedBuilder;
use Eslym\Laravel\Log\DiscordWebhook\Utils;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Monolog\Logger as Monolog;

abstract class AbstractEmbedBuilder implements EmbedBuilder
{
    const PROPERTIES = ['color', 'title', 'description', 'url', 'author', 'thumbnail', 'fields', 'image', 'timestamp', 'footer'];

    function buildEmbed(array $record): array
    {
        $embed = [];

        foreach (static::PROPERTIES as $prop) {
            $func = [$this, 'get' . Str::ucfirst($prop)];
            if (is_callable($func)) {
                $embed[$prop] = call_user_func($func, $record);
            }
        }

        return $embed;
    }

    public function getTitle(array $record): string
    {
        return Config::get('app.name') . ' ' . $record['level_name'];
    }

    public function getColor(array $record): int
    {
        if ($record['level'] < Monolog::INFO) {
            return 0x1565C0;
        } else if ($record['level'] < Monolog::WARNING) {
            return 0x2E7D32;
        } else if ($record['level'] < Monolog::ERROR) {
            return 0xF9A825;
        } else if ($record['level'] < Monolog::CRITICAL) {
            return 0xC62828;
        } else {
            return 0xAD1457;
        }
    }

    function getFields(array $record): array
    {
        $values = [
            'Environment' => Utils::limitStr(Utils::escapeMarkdown(Config::get('app.env')), 1024),
        ];

        if (App::runningInConsole()) {
            if (!empty($_SERVER['argv']) && is_array($_SERVER['argv'])) {
                $values['Command'] = "```" .
                    Utils::limitStr(Utils::escapeMarkdown(join(' ', $_SERVER['argv']), ['`']), 1018) .
                    "```";
            }
        } else {
            $values['Request'] = "```" .
                Utils::limitStr(Utils::escapeMarkdown(Request::method() . ' ' . Request::fullUrl(), ['`']), 1018) .
                "```";
        }

        $fields = [];

        foreach ($values as $name => $value) {
            $fields[] = compact('name', 'value');
        }

        return $fields;
    }

    public function getFooter(array $record): array
    {
        return [
            'text' => join(" ", array_map('php_uname', preg_split('//u', "snr", 0, PREG_SPLIT_NO_EMPTY)))
        ];
    }

    public function getTimestamp(array $record): string
    {
        return Carbon::parse($record['datetime'])->toIso8601String();
    }
}