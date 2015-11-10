<?php namespace KevBaldwyn\PhpFpmMonitor\SuccessHandlers;

use Illuminate\Filesystem\Filesystem;
use Psr\Http\Message\ResponseInterface;

class SaveTmpResponse {

    const TMP_PATH = '/tmp/php5-fpm-monitor-response';

    private $response;
    private $filesystem;

    public function __construct(ResponseInterface $response, Filesystem $filesystem = null)
    {
        if(is_null($filesystem)) {
            $filesystem = new Filesystem();
        }

        $this->response   = $response;
        $this->filesystem = $filesystem;
    }

    public function handle()
    {
        $json = $this->response->getBody();
        $this->filesystem->put(self::TMP_PATH, $json);
    }

}