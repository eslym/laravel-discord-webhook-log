<?php

namespace Eslym\Laravel\Log\DiscordWebhook;

use Eslym\Laravel\Log\DiscordWebhook\Builder\DefaultEmbedBuilder;
use Eslym\Laravel\Log\DiscordWebhook\Contracts\EmbedBuilder;
use Eslym\Laravel\Log\DiscordWebhook\Contracts\NeedFormatter;
use GuzzleHttp\Client;
use Illuminate\Contracts\Config\Repository as ConfigRepo;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Collection;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\AbstractHandler;
use Monolog\Handler\FormattableHandlerInterface;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger as Monolog;

class DiscordWebhookHandler extends AbstractHandler implements FormattableHandlerInterface
{
    /** @var Application */
    protected $app;

    /**
     * @var string
     */
    protected $webhook;

    /**
     * @var string|null
     */
    protected $fallbackChannel;

    /**
     * @var string|null
     */
    protected $message;

    /**
     * @var Client
     */
    protected $http;

    /**
     * @var ConfigRepo
     */
    protected $config;

    /** @var EmbedBuilder */
    protected $embedBuilder = null;

    public function __construct(Application $app, string $webhook, string $message = null, ?array $options = [], ?string $fallbackChannel = null, $level = Monolog::DEBUG, bool $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->app = $app;
        $this->webhook = $webhook;
        $this->fallbackChannel = $fallbackChannel;
        $this->message = $message;
        $this->http = new Client();
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->config = $app->make('config');
        $this->configureEmbedBuilder(collect($options));
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    protected function configureEmbedBuilder(Collection $options)
    {
        if ($options->has('embed')) {
            $this->embedBuilder = $this->app->make($options->get('embed'), $options->get('embed_with'));
        } else {
            $this->embedBuilder = $this->app->make(DefaultEmbedBuilder::class);
        }
    }

    public function handle(array $record): bool
    {
        $payload = ['embeds' => [$this->embedBuilder->buildEmbed($record)]];

        if (!empty($this->message)) {
            $payload['content'] = $this->message;
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->http->post($this->webhook, [
            'json' => $payload,
            'headers' => ['Content-Type' => 'application/json']
        ]);

        return true;
    }

    public function setFormatter(FormatterInterface $formatter): HandlerInterface
    {
        if ($this->embedBuilder instanceof NeedFormatter) {
            $this->embedBuilder->setFormatter($formatter);
        }
        return $this;
    }

    public function getFormatter(): FormatterInterface
    {
        if ($this->embedBuilder instanceof NeedFormatter) {
            return $this->embedBuilder->getFormatter();
        }
        return new LineFormatter();
    }
}