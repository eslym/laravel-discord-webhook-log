<?php

namespace Eslym\Laravel\Log\DiscordWebhook;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Contracts\Config\Repository as ConfigRepo;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\AbstractHandler;
use Monolog\Handler\FormattableHandlerInterface;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger as Monolog;
use Throwable;

class DiscordWebhookHandler extends AbstractHandler implements FormattableHandlerInterface
{
    /** @var Application */
    protected $app;

    /**
     * @var FormatterInterface
     */
    protected $formatter;

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

    public function __construct(Application $app, string $webhook, string $message = null, ?string $fallbackChannel = null, $level = Monolog::DEBUG, bool $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->app = $app;
        $this->webhook = $webhook;
        $this->fallbackChannel = $fallbackChannel;
        $this->message = $message;
        $this->http = new Client();
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->config = $app->make('config');
    }

    public function handle(array $record): bool
    {
        $title = $this->config->get('app.name') . ' ' . $record['level_name'];
        $description = $this->limitStr($this->escapeMarkdown($this->getFormatter()->format($record)), 4096);

        $timestamp = Carbon::parse($record['datetime'])->toIso8601String();

        if($record['level'] < Monolog::INFO){
            $color = 0x1565C0;
        } else if($record['level'] < Monolog::WARNING){
            $color = 0x2E7D32;
        } else if ($record['level'] < Monolog::ERROR){
            $color = 0xF9A825;
        } else if ($record['level'] < Monolog::CRITICAL){
            $color = 0xC62828;
        } else {
            $color = 0xAD1457;
        }

        $values = [
            'Server' => join(" ", array_map('php_uname', preg_split('//u', "snr", 0, PREG_SPLIT_NO_EMPTY))),
            'Environment' => $this->config->get('app.env'),
        ];

        if($this->app->runningInConsole()){
            if(!empty($_SERVER['argv']) && is_array($_SERVER['argv'])){
                $values['Command'] = join(' ', $_SERVER['argv']);
            }
        } else {
            /** @var Request $request */
            /** @noinspection PhpUnhandledExceptionInspection */
            $request = $this->app->make('request');
            $values['Request'] = $request->method() . ' ' . $request->fullUrl();
        }

        $fields = [];

        foreach ($values as $name => $value){
            $value = $this->limitStr($this->escapeMarkdown($value), 2014);
            $fields[]= compact('name', 'value');
        }

        $embed = compact('title', 'description', 'timestamp', 'color', 'fields');
        $payload = ['embeds' => [$embed]];

        if(!empty($this->message)){
            $payload['content'] = $this->message;
        }

        try{
            $this->http->post($this->webhook, [
                'json' => $payload,
                'headers' => ['Content-Type' => 'application/json']
            ]);
        } catch (Throwable $exception){
            return false;
        }

        return true;
    }

    public function setFormatter(FormatterInterface $formatter): HandlerInterface
    {
        $this->formatter = $formatter;
        if($formatter instanceof LineFormatter){
            $formatter->includeStacktraces(true);
        }
        return $this;
    }

    public function getFormatter(): FormatterInterface
    {
        if(!$this->formatter){
            $this->setFormatter(new LineFormatter('%message% %context% %extra%', null, true, true));
        }
        return $this->formatter;
    }

    protected function escapeMarkdown($string): string
    {
        return join(array_map(function ($char){
            return in_array($char, ['`','|','*', '_', '~', '\\', '>']) ?
                '\\'.$char : $char;
        }, preg_split('//u', $string, 0, PREG_SPLIT_NO_EMPTY)));
    }

    protected function limitStr($string, $len): string
    {
        if($this->mbStrLen($string) > $len){
            return $this->mbSubStr($string, $len - 3).'...';
        }
        return $string;
    }

    protected function mbStrLen($string){
        if(function_exists('mb_strlen')){
            return mb_strlen($string);
        }
        return count(preg_split('//u', $string, 0, PREG_SPLIT_NO_EMPTY));
    }

    protected function mbSubStr($string, $length): string
    {
        if(function_exists('mb_substr')){
            return mb_substr($string, 0, $length);
        }
        return join(array_slice(preg_split('//u', $string, 0, PREG_SPLIT_NO_EMPTY), 0, $length));
    }
}