<?php

include 'vendor/autoload.php';

use Monolog\Logger;

use Monolog\Handler\StreamHandler;

use Monolog\Handler\ErrorLogHandler;

use Monolog\Formatter\JsonFormatter;

use Monolog\Processor\UidProcessor;

use Monolog\Processor\ProcessIdProcessor;

$logger = new Logger('my_logger');

$stream_handler = new StreamHandler(__DIR__.'/my_app.log', Logger::INFO);

$stream_handler->setFormatter(new JsonFormatter());

$logger->pushHandler($stream_handler);

$logger->pushHandler(new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, Logger::INFO));

$logger->pushProcessor(new UidProcessor);

$logger->pushProcessor(new ProcessIdProcessor);

$logger->info('码王教育——可能是最具含金量的IT培训', ['aa' => 111]);