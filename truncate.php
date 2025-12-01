<?php

$dbName = "ao";
$dbHost = "127.0.0.1";
$dbUser = "root";
$dbPass = "";

try {
	$db = new PDO("mysql:dbname={$dbName}; host={$dbHost}; charset=utf8mb4", $dbUser, $dbPass, [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_EMULATE_PREPARES => false,
	]);
} catch (PDOException $e) {
	print "db接続エラー: " . $e->getMessage();
	exit;
}

$db->exec("TRUNCATE TABLE job_offers;");

header("Location: index.php", true, 301);
exit;
