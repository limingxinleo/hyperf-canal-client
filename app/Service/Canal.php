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

class Canal
{
    public function __construct(
        public string $host,
        public int $port,
        public string $clientId = '1001',
        public string $destination = 'example',
        public string $filter = '.*\\..*'
    ) {
    }
}
