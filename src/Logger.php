<?php declare(strict_types=1);

namespace Microparts\Logger;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger as Monolog;
use Psr\Log\LoggerInterface;

final class Logger
{
    public const FORMAT = '[%datetime%] %channel%.%level_name%: %message% %context% %extra%';

    /**
     * Pre-defined logger handlers.
     *
     * @var array
     */
    private $handlers = [];

    /**
     * @var string
     */
    private $channel;

    /**
     * @var Monolog
     */
    private $monolog;

    /**
     * @var bool
     */
    private $debug;

    /**
     * Logger constructor.
     *
     * @param string $channel
     * @param bool $debug
     */
    public function __construct(string $channel = 'App', bool $debug = false)
    {
        $this->channel = $channel;
        $this->debug   = $debug;

        $this->monolog = new Monolog($channel);
    }

    /**
     * Return default and most-usable logger instance.
     *
     * @param string $channel
     * @param bool $debug
     * @return LoggerInterface
     */
    public static function new(string $channel = 'App', bool $debug = false): LoggerInterface
    {
        $logger = new Logger($channel, $debug);
        $logger->addErrorLogHandler();

        return $logger->register();
    }

    /**
     * Add Monolog handler use callback.
     *
     * @param callable $callback
     * @return Logger
     */
    public function addHandler(callable $callback): self
    {
        $this->handlers[] = $callback;

        return $this;
    }

    /**
     * Register all handlers.
     *
     * @return \Psr\Log\LoggerInterface
     */
    public function register(): LoggerInterface
    {
        foreach ($this->handlers as $handler) {
            $this->monolog->pushHandler($handler($this->chooseLogLevel()));
        }

        return $this->monolog;
    }

    /**
     * Create Monolog logger without fucking brackets -> [] []  [] []  [] []  [] []  [] []
     * if context and extra is empty.
     */
    public function addErrorLogHandler(): void
    {
        $this->addHandler(function (int $level) {
            $formatter = new LineFormatter(self::FORMAT);
            $formatter->ignoreEmptyContextAndExtra();

            $handler = new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, $level);
            $handler->setFormatter($formatter);

            return $handler;
        });
    }

    /**
     * @return int
     */
    private function chooseLogLevel(): int
    {
        return $this->debug ? Monolog::DEBUG : Monolog::INFO;
    }

    /**
     * @return Monolog
     */
    public function getMonolog(): Monolog
    {
        return $this->monolog;
    }
}
