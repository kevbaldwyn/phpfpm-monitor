<?php namespace KevBaldwyn\PhpFpmMonitor;

interface OnFailureInterface {

    public function handle(array $config, $lastSuccessStatus);

}