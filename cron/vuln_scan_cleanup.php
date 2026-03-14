<?php

require_once __DIR__.'/../classes/Database.php';

$pdo = Database::getInstance()->getConnection();

$stmt = $pdo->prepare("
DELETE FROM vuln_scan_jobs
WHERE status IN ('completed','failed')
AND finished_at < NOW() - INTERVAL 30 DAY");

$stmt->execute();

?>
