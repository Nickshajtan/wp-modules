<?php

namespace HCC\Events\Queue;

use HCC\Events\Queue\Interfaces\QueueEngineInterface;
use HCC\Events\Interfaces\JobInterface;
use \PDO;
use \PDOStatement;
use \Psr\Log\LoggerInterface;

class DatabaseQueueEngine implements QueueEngineInterface
{
    public function __construct(readonly private PDO $pdo, readonly private LoggerInterface $logger) {
        $this->createTableIfNotExists();
    }

    public function push(JobInterface $job): void
    {
        try {
            $stmt = $this->pdo->prepare('INSERT INTO job_queue (job_data) VALUES (:job_data)');
            $stmt->execute(['job_data' => serialize($job)]);
        } catch (\PDOException $e) {
            $this->logger->error('Error inserting job into queue: ' . $e->getMessage());
        }
    }

    public function process(int $batchSize = 100, int $maxJobs = 200): void
    {
        $totalProcessed = 0;

        do {
            $jobsProcessed = 0;
            $stmt = $this->prepareJobStatement($batchSize);
            if (!$stmt) {
                break;
            }

            foreach ($this->iterateJobs($stmt) as $job) {
                ['job' => $job, 'id' => $id] = $job;
                $job->handle();
                $this->markAsProcessed($id);
                $jobsProcessed++;
                $totalProcessed++;

                if ($totalProcessed >= $maxJobs) {
                    return;
                }
            }

        }  while ($jobsProcessed > 0);
    }

    protected function createTableIfNotExists(): void
    {
        $createTableQuery = "
            CREATE TABLE IF NOT EXISTS job_queue (
                id INT AUTO_INCREMENT PRIMARY KEY,
                job_data TEXT NOT NULL,
                processed TINYINT(1) DEFAULT 0
            )
        ";

        $this->pdo->exec($createTableQuery);
    }

    protected function markAsProcessed(int $id): void
    {
        try {
            $updateStmt = $this->pdo->prepare('UPDATE job_queue SET processed = 1 WHERE id = :id');
            $updateStmt->execute(['id' => $id]);
        } catch (\PDOException $e) {
            $this->logger->error('Error marking job as processed (ID ' . $id . '): ' . $e->getMessage());
        }
    }

    protected function prepareJobStatement(int $batchSize): false|PDOStatement
    {
        try {
            $stmt = $this->pdo->prepare('SELECT * FROM job_queue WHERE processed = 0 LIMIT :limit');
            $stmt->bindValue(':limit', $batchSize, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt;
        } catch (\PDOException $e) {
            $this->logger->error('Error preparing job queue query: ' . $e->getMessage());
            return false;
        }
    }

    protected function iterateJobs(PDOStatement $stmt): \Generator
    {
        try {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                yield [
                    'job' => unserialize($row['job_data']),
                    'id' => $row['id'],
                ];
            }
        } catch (\PDOException $e) {
            $this->logger->error('Error fetching jobs from queue: ' . $e->getMessage());
        }
    }
}
