<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Command;

use App\Service\Adapter\MySQLAdapter;
use App\Service\Canal;
use App\Service\CanalService;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputOption;

#[Command]
class SyncToMySQLCommand extends HyperfCommand
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('sync:mysql');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('同步数据到 MySQL');
        $this->addOption('pool', 'P', InputOption::VALUE_OPTIONAL, '连接池', 'default');
        $this->addOption('destination', 'D', InputOption::VALUE_OPTIONAL, 'destination', 'example');
    }

    public function handle()
    {
        $pool = $this->input->getOption('pool');
        $dest = $this->input->getOption('destination');
        $host = env('CANAL_HOST', '127.0.0.1');
        $port = (int) env('CANAL_PORT', 11111);

        $canal = new Canal(host: $host, port: $port, destination: $dest);
        $service = new CanalService($this->container, $canal);
        $service->run(new MySQLAdapter($pool));
    }
}
