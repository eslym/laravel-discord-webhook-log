<?php

namespace Eslym\Laravel\Log\DiscordWebhook\Builder;

use Eslym\Laravel\Log\DiscordWebhook\Utils;
use Illuminate\Contracts\Foundation\Application;
use Symfony\Component\VarDumper\Caster\Caster;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;

class DumperEmbedBuilder extends AbstractEmbedBuilder
{
    protected $app;
    protected $dumper;
    protected $cloner;

    public function __construct(Application $app)
    {
        $this->dumper = new CliDumper();
        $this->dumper->setColors(false);
        $this->cloner = new VarCloner();
    }

    public function getDescription(array $record): string
    {
        $message = Utils::escapeMarkdown($record['message']);
        $partials = [];
        if (!empty($record['context'])) {
            $partials["\n\n**Context:**"] = Utils::escapeMarkdown($this->dump($record['context']), ['`']);
        }

        if (!empty($record['extra'])) {
            $partials["**Extra:**"] = Utils::escapeMarkdown($this->dump($record['extra']), ['`']);
        }
        if (Utils::mbStrLen($message) > 4096) {
            return Utils::limitStr($message, 4096);
        }
        foreach ($partials as $title => $content) {
            $len = Utils::mbStrLen($message . $title);
            if ($len > 4090) {
                break; // ignore parts, not enough length
            }
            $message = $message . $title;
            $limit = 4090 - $len;
            $message .= "```" . Utils::mbSubStr($content, $limit) . "```";
        }
        return $message;
    }

    protected function dump($value): string
    {
        $var = $this->cloner->cloneVar($value, Caster::EXCLUDE_PRIVATE | Caster::EXCLUDE_PROTECTED);
        return $this->dumper->dump($var, true);
    }
}