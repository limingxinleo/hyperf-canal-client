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
namespace App\Service;

use App\Service\Adapter\AdapterInterface;
use Han\Utils\Service;
use Psr\Container\ContainerInterface;
use xingwenge\canal_php\CanalClient;
use xingwenge\canal_php\CanalConnectorFactory;

class CanalService extends Service
{
    public function __construct(ContainerInterface $container, protected Canal $canal)
    {
        parent::__construct($container);
    }

    public function run(AdapterInterface $adapter)
    {
        retry(INF, function () use ($adapter) {
            try {
                $this->logger->info('Start Canal Client...');

                $client = CanalConnectorFactory::createClient(CanalClient::TYPE_SWOOLE);

                $client->connect($this->canal->host, $this->canal->port);
                $client->checkValid();
                $client->subscribe($this->canal->clientId, $this->canal->destination, $this->canal->filter);

                while (true) {
                    $adapter->handle($client->get(100));
                }

                $client->disConnect();
            } catch (\Throwable $exception) {
                $this->logger->error((string) $exception);
                throw $exception;
            }
        });
    }
}
