<?php

namespace Eslym\Laravel\Log\DiscordWebhook\Tests;

use Illuminate\Support\Facades\Log;

class LogTest extends BaseTestCase
{
    const LOG_MESSAGE = "A quick brown fox jumps over the lazy fox";

    const TEST_CHANNELS = ['discord', 'discord-dumper'];
    const TEST_LEVELS = ['debug', 'info', 'warning', 'error', 'critical'];

    public function testBasicLog()
    {
        $this->log(static::LOG_MESSAGE);
    }

    protected function log($message, $context = [], $extra = [])
    {
        foreach (static::TEST_CHANNELS as $channel) {
            $log = Log::channel($channel);
            foreach (static::TEST_LEVELS as $level) {
                $log->{$level}($message, $context, $extra);
                $this->assertTrue(true, "Failed to log '$level'");
                sleep(1);
            }
        }
    }

    public function testExceptionLog()
    {
        $this->log(new \Exception());
    }

    public function testLogWithContext()
    {
        $this->log(static::LOG_MESSAGE, ['error' => new \Exception()]);
    }
}