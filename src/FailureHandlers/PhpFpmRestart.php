<?php namespace KevBaldwyn\PhpFpmMonitor\FailureHandlers;

use KevBaldwyn\PhpFpmMonitor\OnFailureInterface;

class PhpFpmRestart implements OnFailureInterface {

    public function handle(array $config, $lastSuccessStatus)
    {
        $command = $config['php']['restart_command'];
        exec($command);
    }

}