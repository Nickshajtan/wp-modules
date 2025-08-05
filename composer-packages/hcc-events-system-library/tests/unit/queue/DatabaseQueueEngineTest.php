<?php

use HCC\Events\Interfaces\JobInterface;
use HCC\Events\Queue\DatabaseQueueEngine;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class DatabaseQueueEngineTest extends TestCase
{
    public function testPushInsertsJobIntoDatabase(): void
    {
        $job = $this->createMock(JobInterface::class);
        $pdo = $this->createMock(\PDO::class);
        $stmt = $this->createMock(\PDOStatement::class);
        $logger = $this->createMock(LoggerInterface::class);

        $pdo->expects($this->once())
            ->method('prepare')
            ->with('INSERT INTO job_queue (job_data) VALUES (:job_data)')
            ->willReturn($stmt);

        $stmt->expects($this->once())
            ->method('execute')
            ->with(['job_data' => serialize($job)]);

        $pdo->expects($this->once())
            ->method('exec') // for createTableIfNotExists()
            ->with($this->stringContains('CREATE TABLE'));

        $engine = new DatabaseQueueEngine($pdo, $logger);
        $engine->push($job);
    }

    public function testPushLogsOnException(): void
    {
        $job = $this->createMock(JobInterface::class);
        $pdo = $this->createMock(\PDO::class);
        $logger = $this->createMock(LoggerInterface::class);

        $pdo->method('exec')->willReturn(1);
        $pdo->method('prepare')->willThrowException(new \PDOException("insert error"));
        $logger->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Error inserting job'));

        $engine = new DatabaseQueueEngine($pdo, $logger);
        $engine->push($job);
    }

    public function testProcessCallsHandleAndMarksProcessed(): void
    {
        $job = $this->createMock(JobInterface::class);
        $job->expects($this->once())->method('handle');
        $pdo = $this->createMock(\PDO::class);
        $logger = $this->createMock(LoggerInterface::class);
        $stmt = $this->createMock(\PDOStatement::class);
        $fetchCalls = 0;
        $stmt->method('fetch')->willReturnCallback(function () use (&$fetchCalls, $job) {
            return match ($fetchCalls++) {
                0 => ['id' => 42, 'job_data' => $job],
                default => false
            };
        });
        $markStmt = $this->createMock(\PDOStatement::class);

        $stmt->expects($this->atLeastOnce())->method('bindValue');
        $stmt->expects($this->atLeastOnce())->method('execute');
        $stmt->expects($this->exactly(3))->method('fetch');
        $markStmt->expects($this->once())->method('execute')->with(['id' => 42]);

        $pdo->expects($this->exactly(3))
            ->method('prepare')
            ->willReturnCallback(function ($sql) use($stmt, $markStmt) {
                if (str_starts_with($sql, 'SELECT')) {
                    return $stmt;
                }
                if (str_starts_with($sql, 'UPDATE')) {
                    return $markStmt;
                }
                throw new Exception("Unexpected SQL: $sql");
            });

        $engine = new class($pdo, $logger) extends DatabaseQueueEngine {
            protected function iterateJobs(PDOStatement $stmt): \Generator
            {
                while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                    yield [ 'job' => $row['job_data'], 'id' => $row['id'] ];
                }
            }
        };
        $engine->process(batchSize: 100, maxJobs: 2);
    }

    public function testGetJobsLogsOnException(): void
    {
        $pdo = $this->createMock(\PDO::class);
        $logger = $this->createMock(LoggerInterface::class);
        $pdo->method('exec')->willReturn(1);
        $pdo->method('prepare')->willThrowException(new \PDOException("fetch error"));

        $logger->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Error preparing job queue query:'));

        $engine = new DatabaseQueueEngine($pdo, $logger);
        $engine->process();
    }

    public function testMarkAsProcessedLogsOnException(): void
    {
        $pdo = $this->createMock(\PDO::class);
        $logger = $this->createMock(LoggerInterface::class);

        $pdo->method('exec')->willReturn(1);
        $pdo->method('prepare')->willThrowException(new \PDOException("update error"));
        $logger->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Error marking job as processed'));

        $engine = new class($pdo, $logger) extends DatabaseQueueEngine {
            public function tryMark(): void
            {
                $this->markAsProcessed(7);
            }
        };

        $engine->tryMark();
    }
}
