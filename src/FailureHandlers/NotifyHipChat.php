<?php namespace KevBaldwyn\PhpFpmMonitor\FailureHandlers;

use HipChat\HipChat;
use KevBaldwyn\PhpFpmMonitor\OnFailureInterface;

class NotifyHipChat implements OnFailureInterface {

    public function handle(array $config, $lastSuccessStatus)
    {
        if($config['hipchat']['from'] == 'HOST_NAME') {
            $from = gethostname();
        }else{
            $from = $config['hipchat']['from'];
        }

        $from = substr($from, 0, 15);

        $hc = new HipChat($config['hipchat']['api_key']);
        $hc->message_room($config['hipchat']['room'], $from, '<b>[host:' . gethostname() . ']</b> - ' . $config['hipchat']['message'], true, HipChat::COLOR_RED);
        $hc->message_room($config['hipchat']['room'], $from, json_encode(json_decode($lastSuccessStatus), JSON_PRETTY_PRINT), false, HipChat::COLOR_RED, HipChat::FORMAT_TEXT);
    }


}