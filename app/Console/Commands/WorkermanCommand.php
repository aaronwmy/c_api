<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GatewayWorker\BusinessWorker;
use GatewayWorker\Gateway;
use GatewayWorker\Register;
use Workerman\Worker;

class WorkermanCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'workman {action} {--d}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '开启workerman服务';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        global $argv;
        $action = $this->argument('action');

        $argv[0] = 'wk';
        $argv[1] = $action;
        $argv[2] = $this->option('d') ? '-d' : '';

        $this->start();
    }

    private function start()
    {
        $this->startGateWay();
        $this->startBusinessWorker();
        $this->startRegister();
        Worker::runAll();
    }

    private function startBusinessWorker()
    {
        $worker = new BusinessWorker();
        $worker->name = 'BusinessWorker';
        $worker->count = 2;
        $worker->registerAddress = '127.0.0.1:' . env('WORKERMAN_REGISTER_PORT');
        $worker->eventHandler = \App\Workerman\Events::class;
    }

    private function startGateWay()
    {
        $context = array(
            'ssl' => array(
                'local_cert' => env('SSL_CERT_PATH'),
                'local_pk' => env('SSL_KEY_PATH'),
                'verify_peer' => false
            )
        );
        $gateway = new Gateway("websocket://0.0.0.0:" . env('WORKERMAN_GATEWAY_PORT'), $context);
        $gateway->transport = 'ssl';
        $gateway->name = 'Gateway';
        $gateway->count = 1;
        $gateway->lanIp = '127.0.0.1';
        $gateway->startPort = env('WORKERMAN_START_PORT');
        $gateway->pingInterval = 55;
        $gateway->pingNotResponseLimit = 1;
        $gateway->pingData = '';
        $gateway->registerAddress = '127.0.0.1:' . env('WORKERMAN_REGISTER_PORT');
    }

    private function startRegister()
    {
        new Register('text://0.0.0.0:' . env('WORKERMAN_REGISTER_PORT'));
    }
}
