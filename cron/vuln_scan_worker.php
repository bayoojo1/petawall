<?php

require_once __DIR__.'/../classes/Database.php';
require_once __DIR__.'/../classes/ScheduledScanManager.php';

$pdo = Database::getInstance()->getConnection();
$scanner = new ScheduledScanManager();

$workerId = gethostname()."-".getmypid();

$maxJobs=200;
$batchSize=20;
$jobsProcessed=0;
$idleTime=1;

while(true){

    if($jobsProcessed >= $maxJobs){
        error_log("Worker restart after $jobsProcessed jobs");
        exit(0);
    }

    if(memory_get_usage(true) > 512*1024*1024){
        error_log("Worker memory limit reached");
        exit(0);
    }

    try{

        $pdo->beginTransaction();

        $stmt=$pdo->prepare("
        SELECT id,scan_id
        FROM vuln_scan_jobs
        WHERE status='pending'
        ORDER BY id
        LIMIT $batchSize
        FOR UPDATE SKIP LOCKED
        ");

        $stmt->execute();

        $jobs=$stmt->fetchAll(PDO::FETCH_ASSOC);

        if(!$jobs){
            $pdo->commit();
            sleep($idleTime);
            $idleTime=min($idleTime*2,15);
            continue;
        }

        $idleTime=1;

        $jobIds=array_column($jobs,'id');

        $in=implode(',',array_fill(0,count($jobIds),'?'));

        $update=$pdo->prepare("
        UPDATE vuln_scan_jobs
        SET status='running',
        started_at=NOW(),
        worker_id=?
        WHERE id IN ($in)
        ");

        $update->execute(array_merge([$workerId],$jobIds));

        $pdo->commit();

    }
    catch(Exception $e){

        $pdo->rollBack();
        error_log($e->getMessage());
        sleep(2);
        continue;
    }

    foreach($jobs as $job){

        try{

            error_log("Worker $workerId executing scan ".$job['scan_id']);

            $scanner->executeScanById($job['scan_id']);

            $pdo->prepare("
            UPDATE vuln_scan_jobs
            SET status='completed',
            finished_at=NOW()
            WHERE id=?
            ")->execute([$job['id']]);

        }
        catch(Exception $e){

            $pdo->prepare("
            UPDATE vuln_scan_jobs
            SET status='failed'
            WHERE id=?
            ")->execute([$job['id']]);

            error_log("Scan failed ".$e->getMessage());
        }

        $jobsProcessed++;

        if($jobsProcessed >= $maxJobs){
            exit(0);
        }
    }
}