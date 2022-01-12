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

use Fan\Feishu\Factory;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Psr\Container\ContainerInterface;

#[Command]
class ShowFeishuCommand extends HyperfCommand
{
    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('show:feishu');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('展示飞书信息');
    }

    public function handle()
    {
        $robot = di()->get(Factory::class)->get('default')->robot;

        var_dump($robot->groupList());
    }
}
