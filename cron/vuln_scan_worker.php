<?php
require_once __DIR__.'/../classes/Database.php';
require_once __DIR__.'/../classes/ScheduledScanManager.php';

// Don't initialize $pdo globally - we'll get fresh connections when needed
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

    // Get a fresh connection for each iteration
    $pdo = Database::getInstance()->getConnection();

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
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Error claiming jobs: " . $e->getMessage());
        sleep(2);
        continue;
    }

    foreach($jobs as $job){
        try{
            error_log("Worker $workerId executing scan ".$job['scan_id']);
            
            $scanner->executeScanById($job['scan_id']);
            
            // IMPORTANT: Get a NEW connection after the long-running scan
            $pdo = Database::getInstance()->getConnection();
            
            // Update with error checking
            $stmt = $pdo->prepare("
            UPDATE vuln_scan_jobs
            SET status='completed',
            finished_at=NOW()
            WHERE id=? AND status='running'
            ");
            
            if (!$stmt->execute([$job['id']])) {
                error_log("Failed to update job {$job['id']} to completed status");
                
                // Log the actual PDO error
                $errorInfo = $stmt->errorInfo();
                if ($errorInfo[0] != '00000') {
                    error_log("PDO Error: " . json_encode($errorInfo));
                }
                
            } else if ($stmt->rowCount() === 0) {
                error_log("Job {$job['id']} was not in 'running' state when trying to complete");
                
                // Check what state it's actually in
                $checkStmt = $pdo->prepare("SELECT status FROM vuln_scan_jobs WHERE id = ?");
                $checkStmt->execute([$job['id']]);
                $currentStatus = $checkStmt->fetchColumn();
                error_log("Job {$job['id']} current status: " . $currentStatus);
            } else {
                error_log("Worker $workerId successfully completed scan ".$job['scan_id']);
            }
            
        }
        catch(Exception $e){
            error_log("Scan failed for job {$job['id']}: " . $e->getMessage());
            
            // Get a fresh connection for error handling too
            try {
                $pdo = Database::getInstance()->getConnection();
                
                $stmt = $pdo->prepare("
                UPDATE vuln_scan_jobs
                SET status='failed',
                finished_at=NOW(),
                error_message=?
                WHERE id=?
                ");
                
                if (!$stmt->execute([substr($e->getMessage(), 0, 255), $job['id']])) {
                    error_log("Failed to update job {$job['id']} to failed status");
                    
                    $errorInfo = $stmt->errorInfo();
                    if ($errorInfo[0] != '00000') {
                        error_log("PDO Error: " . json_encode($errorInfo));
                    }
                } else {
                    error_log("Worker $workerId marked scan ".$job['scan_id']." as failed");
                }
                
            } catch (Exception $updateError) {
                error_log("Critical: Could not update job status at all: " . $updateError->getMessage());
            }
        }
        
        $jobsProcessed++;
        
        if($jobsProcessed >= $maxJobs){
            error_log("Worker $workerId processed $jobsProcessed jobs, restarting");
            exit(0);
        }
    }
}