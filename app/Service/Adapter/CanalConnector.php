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
namespace App\Service\Adapter;

use Exception;
use Hyperf\Engine\Socket;
use xingwenge\canal_php\adapter\CanalConnectorBase;

class CanalConnector extends CanalConnectorBase
{
    /**
     * @var Socket
     */
    protected $client;

    /**
     * @param string $host
     * @param int $port
     * @param int $connectionTimeout
     * @param int $readTimeout
     * @param int $writeTimeout
     * @throws Exception
     */
    protected function doConnect($host = '127.0.0.1', $port = 11111, $connectionTimeout = 10, $readTimeout = 30, $writeTimeout = 30)
    {
        $this->client = new Socket(AF_INET, SOCK_STREAM);
        if (! $this->client->connect($host, $port, $connectionTimeout)) {
            throw new Exception("connect failed. Error: {$this->client->errCode}");
        }
    }

    protected function readNextPacket()
    {
        $data = $this->client->recvAll($this->packetLen);
        $dataLen = unpack('N', $data)[1];
        return $this->client->recvAll($dataLen);
    }

    protected function writeWithHeader($data)
    {
        $this->client->sendAll(pack('N', strlen($data)));
        $this->client->sendAll($data);
    }
}
