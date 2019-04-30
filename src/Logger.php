<?php declare(strict_types=1);

namespace Microparts\Logger;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger as Monolog;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

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
    private $level;

    /**
     * Logger constructor.
     *
     * @param string $channel
     * @param string $level
     */
    public function __construct(string $channel = 'App', string $level = LogLevel::INFO)
    {
        $this->channel = $channel;
        $this->level   = $level;

        $this->monolog = new Monolog($channel);
    }

    /**
     * Return default and most-usable logger instance.
     *
     * @param string $channel
     * @param string $level
     * @return LoggerInterface
     */
    public static function new(string $channel = 'App', string $level = LogLevel::INFO): LoggerInterface
    {
        $logger = new Logger($channel, $level);
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
            $this->monolog->pushHandler($handler($this->level));
        }

        return $this->monolog;
    }

    /**
     * Create Monolog logger without fucking brackets -> [] []  [] []  [] []  [] []  [] []
     * if context and extra is empty.
     */
    public function addErrorLogHandler(): void
    {
        $this->addHandler(function (string $level) {
            $formatter = new LineFormatter(self::FORMAT);
            $formatter->ignoreEmptyContextAndExtra();

            $handler = new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, $level);
            $handler->setFormatter($formatter);

            return $handler;
        });
    }

    /**
     * @return Monolog
     */
    public function getMonolog(): Monolog
    {
        return $this->monolog;
    }
}
