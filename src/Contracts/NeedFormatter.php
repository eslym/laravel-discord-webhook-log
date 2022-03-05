<?php

namespace Eslym\Laravel\Log\DiscordWebhook\Contracts;

use Monolog\Formatter\FormatterInterface;

interface NeedFormatter
{
    public function setFormatter(FormatterInterface $formatter): EmbedBuilder;

    public function getFormatter(): FormatterInterface;
}