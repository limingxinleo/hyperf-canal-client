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
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Psr\Container\ContainerInterface;
use xingwenge\canal_php\CanalClient;
use xingwenge\canal_php\CanalConnectorFactory;

class CanalService extends Service
{
    protected FeishuService $feishu;

    protected int $syncTimestamp;

    protected bool $listening = false;

    public function __construct(ContainerInterface $container, protected Canal $canal)
    {
        parent::__construct($container);

        $this->feishu = $container->get(FeishuService::class);

        $this->syncTimestamp = time();
    }

    public function listen()
    {
        if ($this->listening) {
            return;
        }

        $this->listening = true;

        go(function () {
            try {
                while (true) {
                    if (CoordinatorManager::until(Constants::WORKER_EXIT)->yield(60)) {
                        break;
                    }

                    echo '监听中, At: ' . $this->syncTimestamp . PHP_EOL;

                    if (! $this->listening) {
                        break;
                    }

                    if ($this->syncTimestamp < time() - 3600) {
                        echo '同步失败' . PHP_EOL;
                        $this->syncTimestamp = time();
                        $this->feishu->alert('同步失败!');
                    }
                }
            } catch (\Throwable) {
                echo '监听结束' . PHP_EOL;
                $this->listening = false;
            }
        });
    }

    public function run(AdapterInterface $adapter)
    {
        $this->listen();

        retry(INF, function () use ($adapter) {
            try {
                $this->logger->info('Start Canal Client...');

                $client = CanalConnectorFactory::createClient(CanalClient::TYPE_SWOOLE);

                $client->connect($this->canal->host, $this->canal->port);
                $client->checkValid();
                $client->subscribe($this->canal->clientId, $this->canal->destination, $this->canal->filter);

                while (true) {
                    $this->syncTimestamp = time();
                    if (! $adapter->handle($client->get(100))) {
                        sleep(1);
                    }
                }

                $client->disConnect();
            } catch (\Throwable $exception) {
                $this->logger->error((string) $exception);
                throw $exception;
            }
        }, 100);

        $this->listening = false;
    }
}
