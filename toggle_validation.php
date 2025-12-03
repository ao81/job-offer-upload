<?php

session_start();

if (!isset($_SESSION["validate_csv_session"])) {
	$_SESSION["validate_csv_session"] = true;
}

$_SESSION["validate_csv_session"] = !$_SESSION["validate_csv_session"];

$res = [
	"validation_enabled" => $_SESSION["validate_csv_session"]
];

header("Content-Type: application/json");
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
print json_encode($res);
exit;