<?php namespace KevBaldwyn\PhpFpmMonitor;

use GuzzleHttp\Client;
use Illuminate\Filesystem\Filesystem;
use KevBaldwyn\PhpFpmMonitor\SuccessHandlers\SaveTmpResponse;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use GuzzleHttp\Exception\ServerException;

class Check extends Command {

    const DEFAULT_PING_HOST = 'http://localhost';
    const DEFAULT_PING_PORT = '9000';
    const DEFAULT_PING_URL = '/ping';
    const DEFAULT_PING_RESPONSE = 'pong';
    const DEFAULT_STATUS_URL = '/status';

    const ARG_CONFIG = 'config';

    /**
     * @var Client
     */
    private $httpClient;

    /**
     * @var Yaml
     */
    private $yaml;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var array
     */
    private $config;


    public function __construct($name = 'Php5 Fpm-Monitor', Client $httpClient = null, Yaml $yaml = null, Filesystem $filesystem = null)
    {
        if(is_null($httpClient)) {
            $httpClient = new Client();
        }
        if(is_null($yaml)) {
            $yaml = new Yaml();
        }
        if(is_null($filesystem)) {
            $filesystem = new Filesystem();
        }

        $this->httpClient = $httpClient;
        $this->yaml = $yaml;
        $this->filesystem = $filesystem;

        parent::__construct($name);
    }


    protected function configure()
    {
        $this
            ->setName('check')
            ->setDescription('Check php5-fpm process')
            ->addArgument(
                self::ARG_CONFIG,
                InputArgument::REQUIRED,
                'What is the path to the config file?'
            )->addOption(
                'pinghost',
                'l',
                InputOption::VALUE_OPTIONAL,
                'What is the host?',
                self::DEFAULT_PING_HOST
            )->addOption(
                'pingport',
                'p',
                InputOption::VALUE_OPTIONAL,
                'What is the port?',
                self::DEFAULT_PING_PORT
            )->addOption(
                'response',
                'r',
                InputOption::VALUE_OPTIONAL,
                'What ping response do you expect?',
                self::DEFAULT_PING_RESPONSE
            )->addOption(
                'pingurl',
                'u',
                InputOption::VALUE_OPTIONAL,
                'What is the relative url to ping?',
                self::DEFAULT_PING_URL
            )->addOption(
                'statusurl',
                's',
                InputOption::VALUE_OPTIONAL,
                'What is the relative url to get the status?',
                self::DEFAULT_STATUS_URL
            );
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input  = $input;
        $this->output = $output;

        // get the configuration
        $this->getConfig();

        $pingUrl = $this->pingRequestUrl();

        if($this->output->isVerbose()) {
            $this->output->write('Pinging ' . $pingUrl . '...');
        }

        try {
            $httpRes = $this->httpClient->get($pingUrl);

            if($this->output->isVerbose()) {
                $this->output->writeLn('Done');
            }

            // check the response matches what we expect
            // probably a bit superfluous
            $res = $httpRes->getBody();
            if($res == $this->pingResponse()) {
                if ($this->output->isVerbose()) {
                    $this->output->writeLn('Response ok');
                }

                $statusUrl = $this->statusRequestUrl();

                if ($this->output->isVerbose()) {
                    $this->output->writeln('Getting status: ' . $statusUrl);
                }

                // get the status response
                $httpRes = $this->httpClient->get($statusUrl);

                // save the status to a tmp file
                $saveHandler = new SaveTmpResponse($httpRes);
                $saveHandler->handle();

            }else{
                // if the response doesn't match what we expect then execute the handlers
                $this->handleFailure();
            }

        }catch(ServerException $e) {
            // server error then execute the handlers
            $this->handleFailure();
        }
    }

    private function handleFailure()
    {
        if ($this->output->isVerbose()) {
            $this->output->writeLn('Expected Response: ' . $this->pingResponse());
        }

        // get last status before handling
        $lastSuccessStatus = $this->filesystem->get(SaveTmpResponse::TMP_PATH);

        // get handlers
        foreach($this->config['restart_handlers'] as $handlerClass) {
            $handler = $this->getHandlerClass($handlerClass);
            $handler->handle($this->config, $lastSuccessStatus);
        }
    }

    /**
     * @return OnFailureInterface
     */
    private function getHandlerClass($className)
    {
        return new $className();
    }

    private function getConfig()
    {
        $configPath = $this->input->getArgument(self::ARG_CONFIG);

        if($this->output->isVerbose()) {
            $this->output->writeLn('Reading config file: ' . $configPath);
        }

        $this->config = $this->yaml->parse($this->filesystem->get($configPath));
    }

    private function statusRequestUrl()
    {
        return $this->pingHost() . ':' . $this->pingPort() .  $this->statusUrl() . '?json';
    }


    private function pingRequestUrl()
    {
        return $this->pingHost() . ':' . $this->pingPort() .  $this->pingUrl();
    }


    private function pingResponse()
    {
        $response = $this->input->getOption('response');
        if ($response) {
            return $response;
        }
        return self::DEFAULT_PING_RESPONSE;
    }


    private function pingUrl()
    {
        $url = $this->input->getOption('pingurl');
        if ($url) {
            return $url;
        }
        return self::DEFAULT_PING_URL;
    }


    private function pingHost()
    {
        $host = $this->input->getOption('pinghost');
        if ($host) {
            return $host;
        }
        return self::DEFAULT_PING_HOST;
    }


    private function pingPort()
    {
        $port = $this->input->getOption('pingport');
        if ($port) {
            return $port;
        }
        return self::DEFAULT_PING_PORT;
    }


    private function statusUrl()
    {
        $url = $this->input->getOption('statusurl');
        if ($url) {
            return $url;
        }
        return self::DEFAULT_STATUS_URL;
    }

}