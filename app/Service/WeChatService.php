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

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Han\Utils\Service;

class WeChatService extends Service
{
    public function alert(string $message)
    {
        $key = env('WECHAT_ROBOT_KEY');
        if (empty($key)) {
            return;
        }

        $url = 'https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=' . $key;

        $client = new Client([
            'base_uri' => 'https://qyapi.weixin.qq.com',
            'timeout' => 2,
        ]);

        $client->post($url, [
            RequestOptions::JSON => [
                'msgtype' => 'text',
                'text' => [
                    'content' => $message,
                ],
            ],
        ]);
    }
}
