<?php

require_once __DIR__.'/../classes/Database.php';

$pdo = Database::getInstance()->getConnection();

error_log("Scheduler started ".date('Y-m-d H:i:s'));

$stmt = $pdo->prepare("
SELECT id
FROM scheduled_vuln_scans
WHERE is_active=1
AND next_run <= NOW()
ORDER BY next_run
LIMIT 500
");

$stmt->execute();
$scans = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($scans as $scan) {

    $insert = $pdo->prepare("
    INSERT INTO vuln_scan_jobs (scan_id,status,created_at)
    VALUES (?, 'pending', NOW())
    ");

    $insert->execute([$scan['id']]);

    $nextRun = calculateNextRun($pdo,$scan['id']);

    $update = $pdo->prepare("
    UPDATE scheduled_vuln_scans
    SET next_run=?, last_run=NOW()
    WHERE id=?
    ");

    $update->execute([$nextRun,$scan['id']]);

}

error_log("Scheduler queued ".count($scans)." scans");


function calculateNextRun($pdo,$scanId)
{
    $stmt = $pdo->prepare("SELECT schedule_type FROM scheduled_vuln_scans WHERE id=?");
    $stmt->execute([$scanId]);
    $scan=$stmt->fetch();

    switch($scan['schedule_type']){

        case 'daily':
            return date('Y-m-d H:i:s',strtotime('+1 day'));

        case 'weekly':
            return date('Y-m-d H:i:s',strtotime('+1 week'));

        case 'monthly':
            return date('Y-m-d H:i:s',strtotime('+1 month'));

        default:
            return date('Y-m-d H:i:s',strtotime('+1 day'));
    }
}