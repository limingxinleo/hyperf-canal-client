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

use Fan\Feishu\Factory;
use Han\Utils\Service;
use Hyperf\Di\Annotation\Inject;

class FeishuService extends Service
{
    #[Inject]
    protected Factory $feishu;

    public function alert(string $message): void
    {
        $chatId = env('FEISHU_CHAT_ID');
        if (empty($chatId)) {
            return;
        }

        $bot = $this->feishu->get('default')->robot;
        $bot->sendText(
            $chatId,
            'Canal: ' . $message
        );
    }
}
