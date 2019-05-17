<?php declare(strict_types=1);

namespace Microparts\Logger\Tests;

use Microparts\Logger\Logger;
use Monolog\Handler\AbstractHandler;
use Monolog\Handler\HandlerInterface;
use PHPUnit\Framework\TestCase;


class LoggerTest extends TestCase
{
    private const ERROR_LOG_FILENAME = '/test_logger_error_log.txt';

    public function testHowLoggerWorks()
    {
        ini_set('error_log', sys_get_temp_dir() . self::ERROR_LOG_FILENAME);

        $log = new Logger();
        $log->addErrorLogHandler();
        $log->addHandler(function (): HandlerInterface {
            return new class extends AbstractHandler implements HandlerInterface {
                public function handle(array $record) {
                    fwrite(STDOUT, 'EXAMPLE FOR TESTS Write to stdout');
                }
            };
        });
        $log->register();

        $log->getMonolog()->info('test how simple logger wrapper works');

        $string = file_get_contents(sys_get_temp_dir() . self::ERROR_LOG_FILENAME);
        $this->assertStringEndsWith('App.INFO: test how simple logger wrapper works', trim($string));
    }

    public function testHowWorksStaticMethod()
    {
        ini_set('error_log', sys_get_temp_dir() . self::ERROR_LOG_FILENAME);

        $log = Logger::default();
        $log->info('test how simple logger wrapper works');

        $string = file_get_contents(sys_get_temp_dir() . self::ERROR_LOG_FILENAME);
        $this->assertStringEndsWith('App.INFO: test how simple logger wrapper works', trim($string));
    }
}
