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

use Carbon\Carbon;
use Com\Alibaba\Otter\Canal\Protocol\Column;
use Com\Alibaba\Otter\Canal\Protocol\Entry;
use Com\Alibaba\Otter\Canal\Protocol\EntryType;
use Com\Alibaba\Otter\Canal\Protocol\EventType;
use Com\Alibaba\Otter\Canal\Protocol\RowChange;
use Com\Alibaba\Otter\Canal\Protocol\RowData;
use Fan\PDOExceptionChecker;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Throwable;
use xingwenge\canal_php\Message;

class MySQLAdapter implements AdapterInterface
{
    #[Inject]
    protected PDOExceptionChecker $checker;

    public function __construct(public string $pool)
    {
    }

    public function handle(Message $message): bool
    {
        if ($entries = $message->getEntries()) {
            foreach ($entries as $entry) {
                $this->handleEntry($entry);
            }

            return true;
        }

        return false;
    }

    protected function handleEntry(Entry $entry)
    {
        switch ($entry->getEntryType()) {
            case EntryType::TRANSACTIONBEGIN:
            case EntryType::TRANSACTIONEND:
                return;
        }

        $rowChange = new RowChange();
        $rowChange->mergeFromString($entry->getStoreValue());
        $evenType = $rowChange->getEventType();
        $header = $entry->getHeader();
        $schema = $header->getSchemaName();
        $table = $header->getTableName();
        $count = 0;

        if (in_array($schema, ['information_schema', 'mysql', 'performance_schema', 'sys'])) {
            echo sprintf('[%s] logfile: %s, offset: %s, skip: %s', Carbon::now()->toDateTimeString(), $header->getLogfileName(), $header->getLogfileOffset(), $schema) . PHP_EOL;
            return;
        }

        /** @var RowData $rowData */
        foreach ($rowChange->getRowDatas() as $rowData) {
            switch ($evenType) {
                case EventType::INSERT:
                    $this->insertColumn($rowData->getAfterColumns(), $schema, $table, true);
                    ++$count;
                    break;
                case EventType::UPDATE:
                    $this->updateColumn($rowData->getAfterColumns(), $schema, $table);
                    ++$count;
                    break;
                case EventType::DELETE:
                    // $this->deleteColumn($rowData->getBeforeColumns(), $schema, $table);
                    break;
            }
        }

        echo sprintf('[%s] logfile: %s, offset: %s, count: %d', Carbon::now()->toDateTimeString(), $header->getLogfileName(), $header->getLogfileOffset(), $count) . PHP_EOL;
        if ($count > 10 && $rowChange->getSql()) {
            di()->get(StdoutLoggerInterface::class)->warning(sprintf('SQL: %s, count: %d', $rowChange->getSql(), $count));
        }
    }

    protected function updateColumn($columns, string $schema, string $table)
    {
        $item = [];
        $query = Db::connection($this->pool)->table("{$schema}.{$table}");
        /** @var Column $column */
        foreach ($columns as $column) {
            if ($column->getIsKey()) {
                $query->where($column->getName(), $column->getValue());
            } else {
                $item[$column->getName()] = $column->getIsNull() ? null : $column->getValue();
            }
        }

        try {
            $ret = $query->update($item);
            if ($ret === 0) {
                $this->insertColumn($columns, $schema, $table);
            }
        } catch (Throwable $exception) {
            di()->get(StdoutLoggerInterface::class)->error($exception->getMessage());
        }
    }

    protected function deleteColumn($columns, string $schema, string $table)
    {
        /** @var Column $column */
        foreach ($columns as $column) {
            if ($column->getIsKey()) {
                Db::connection($this->pool)->table("{$schema}.{$table}")->delete($column->getValue());
            }
        }
    }

    protected function insertColumn($columns, string $schema, string $table, bool $orUpdate = false): void
    {
        $item = [];
        /** @var Column $column */
        foreach ($columns as $column) {
            $item[$column->getName()] = $column->getIsNull() ? null : $column->getValue();
        }

        try {
            Db::connection($this->pool)->table("{$schema}.{$table}")->insert($item);
        } catch (Throwable $exception) {
            if ($orUpdate && $this->checker->isDuplicateEntryForPrimaryKey($exception)) {
                $this->updateColumn($columns, $schema, $table);
            }
            di()->get(StdoutLoggerInterface::class)->error($exception->getMessage());
        }
    }
}
