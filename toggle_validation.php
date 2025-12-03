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
print json_encode($res);
exit;