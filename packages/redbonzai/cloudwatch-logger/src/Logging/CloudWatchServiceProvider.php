<?php

namespace Redbonzai\Logging;

use Illuminate\Support\ServiceProvider;
use Maxbanton\Cwh\Handler\CloudWatch;
use Monolog\Logger;

class CloudWatchServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app['log']->extend('cloudwatch', function ($app, $config) {
            $handler = new CloudWatch($app['config']['aws'], $config['streamName'], $config['groupName'], $config['retentionDays']);
            $logger = new Logger('cloudwatch');
            $handler->setFormatter(new CustomJsonFormatter());
            $logger->pushHandler($handler);

            return $logger;
        });
    }
}
