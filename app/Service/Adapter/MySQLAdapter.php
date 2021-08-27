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

use xingwenge\canal_php\Message;

class MySQLAdapter implements AdapterInterface
{
    public function handle(Message $message): bool
    {
        return true;
    }
}
