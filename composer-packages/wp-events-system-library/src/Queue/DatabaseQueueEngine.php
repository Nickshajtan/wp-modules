<?php

namespace HCC\Events\Queue;

use HCC\Events\Queue\Interfaces\QueueEngineInterface;
use HCC\Events\Interfaces\JobInterface;
use \PDO;

class DatabaseQueueEngine implements QueueEngineInterface
{
    public function __construct(readonly private PDO $pdo) {}

    public function push(JobInterface $job): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO job_queue (job_data) VALUES (:job_data)');
        $stmt->execute(['job_data' => serialize($job)]);
    }

    public function process(): void
    {
        $stmt = $this->pdo->query('SELECT * FROM job_queue WHERE processed = 0 LIMIT 10');
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $job = unserialize($row['job_data']);
            $job->handle();

            $updateStmt = $this->pdo->prepare('UPDATE job_queue SET processed = 1 WHERE id = :id');
            $updateStmt->execute(['id' => $row['id']]);
        }
    }
}
