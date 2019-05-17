<?php declare(strict_types=1);

namespace Microparts\Logger\Tests;

use Microparts\Logger\Logger;
use Monolog\Handler\AbstractHandler;
use Monolog\Handler\HandlerInterface;
use PHPUnit\Framework\TestCase;


class LoggerTest extends TestCase
{
    public function testHowLoggerWorks()
    {
        $unique = uniqid();
        ini_set('error_log', $this->getFilename($unique));

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

        $string = file_get_contents($this->getFilename($unique));
        $this->assertStringEndsWith('App.INFO: test how simple logger wrapper works', trim($string));
    }

    public function testHowWorksStaticMethods()
    {
        $unique = uniqid();
        ini_set('error_log', $this->getFilename($unique));

        $log = Logger::default();
        $log->info('log 1');

        $log = Logger::new();
        $log->register();

        $log->getMonolog()->info('log 2');

        $string = file_get_contents($this->getFilename($unique));

        $matches = [];
        preg_match_all('/log\s(\d{1})/i', $string, $matches);

        $this->assertCount(2, $matches[0]);
    }

    /**
     * @param string $unique
     * @return string
     */
    private function getFilename(string $unique): string
    {
        return sys_get_temp_dir() . "/test_logger_error_log_$unique.txt";
    }
}
