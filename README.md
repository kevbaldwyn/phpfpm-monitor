# Php Fpm Monitor
This package can be configured to monitor php5-fpm through its built in ping and status pages. It can then restart php5-fpm if it detects a failure and also optionally notify you of the failure with the last successful status output.

It is not designed as a permanent solution for problems with php-fpm, more a tool to help you handle failure while the problems can be interrogated by notifying you of the state of the last success. Typically the problems might be a mis-configured fpm pool and the log of the last status before failure will hopefully help diagnose those issues.

## Instalation
Install with composer:

1) Add to composer:

    "require": {
        ...
        "kevbaldwyn/php-fpm-monitor":"dev-master"
        ...
    }

2) Composer Update:

    $ composer update kevbaldwyn/php-fpm-monitor

3) Copy the yaml `config.example.yml` file to a convenient location and configure the options as you require. It should work without notifications out of the box.

## Useage
The package provides a binary which will be located in `vendor/bin/monitor`. Basic useage requires passing the config yaml file path to the command as the first argument. All other options are optional.

    $ vendor/bin/monitor check /var/www/config.yml

Passing the help `-h` flag shows all options. To set the host and port to ping php-fpm for example:

    $ vendor/bin/monitor check /var/www/config.yml -l http://example.com -p 80

The idea is that this command is then run from cron periodically to check the state of php-fpm and will automatically restart the service if it is down.

## Php5-Fpm status and ping pages
This tool requires the status and ping pages to be correctly setup. More info on this can be found here: https://rtcamp.com/tutorials/php/fpm-status-page/

## Failure Handlers
There are 2 built in failure handlers : `KevBaldwyn\PhpFpmMonitor\FailureHandlers\PhpFpmRestart` which restarts php-fpm and `KevBaldwyn\PhpFpmMonitor\FailureHandlers\NotifyHipChat`. Both of these can be further configured in the yaml config.

To specify which FailureHandlers run you simply list the fully namespaced classes in the `restart_handlers` config array.

You can write your own handlers by implementing `KevBaldwyn\PhpFpmMonitor\OnFailureInterface`. This receives the following method call which contains the full config, as defined in the yaml file, and the last successful status response in json format.

    public function handle(array $config, $lastSuccessStatus);