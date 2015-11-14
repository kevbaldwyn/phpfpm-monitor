# Php Fpm Monitor
This package can be configured to monitor php5-fpm through its built in ping and status pages. It can then restart php5-fpm if it detects a failure and also optionally notify you of the failure with the last successful status output.

## Instalation
Install as any other Laravel 4 package:

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

    $ vendor/bin/monitor /var/www/config.yml

Passing the help `-h` flag shows all options. To set the host and port to ping php-fpm for example:

    $ vendor/bin/monitor /var/www/config.yml -l http://example.com -p 80

## Php5-Fpm status and ping pages
This tool requires the status and ping pages to be correctly setup. More info on this can be found here: https://rtcamp.com/tutorials/php/fpm-status-page/