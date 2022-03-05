<?php

namespace Eslym\Laravel\Log\DiscordWebhook\Builder;

use Eslym\Laravel\Log\DiscordWebhook\Contracts\EmbedBuilder;
use Eslym\Laravel\Log\DiscordWebhook\Contracts\NeedFormatter;
use Eslym\Laravel\Log\DiscordWebhook\Utils;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;

class DefaultEmbedBuilder extends AbstractEmbedBuilder implements EmbedBuilder, NeedFormatter
{
    /** @var FormatterInterface */
    protected $formatter;

    public function getDescription(array $record): string
    {
        return Utils::limitStr(Utils::escapeMarkdown($this->getFormatter()->format($record)), 4096);
    }

    public function getFormatter(): FormatterInterface
    {
        if (!$this->formatter) {
            $this->setFormatter(new LineFormatter('%message% %context% %extra%', null, true, true));
        }
        return $this->formatter;
    }

    public function setFormatter(FormatterInterface $formatter): EmbedBuilder
    {
        $this->formatter = $formatter;
        if ($formatter instanceof LineFormatter) {
            $formatter->includeStacktraces(true);
        }
        return $this;
    }
}