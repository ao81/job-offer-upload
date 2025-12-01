<?php

$dbName = "ao";
$dbHost = "127.0.0.1";
$dbUser = "root";
$dbPass = "";
$tableName = "job_offers";

//___________________//
$validate_csv = true;
//‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾//

try {
	$file = $_FILES["data"];
	$db = new PDO("mysql:dbname={$dbName}; host={$dbHost}; charset=utf8mb4", $dbUser, $dbPass, [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
	]);
} catch (PDOException $e) {
	print "db接続エラー: " . $e->getMessage();
	exit;
}

$db_columns = [
	"recruitment_year",
	"job_offer_id",
	"reception_center",
	"office_number",
	"is_public_internet",
	"company_name_kana",
	"company_name",
	"postal_code",
	"address",
	"access_info",
	"representative_name",
	"corporate_number",
	"website_url",
	"employee_count_total",
	"employee_count_office",
	"established_date",
	"capital_stock",
	"business_description",
	"company_features",
	"employment_type",
	"job_category",
	"job_description",
	"work_postal_code",
	"work_address",
	"work_access",
	"vacancies_commute",
	"vacancies_live_in",
	"vacancies_any",
	"required_skills",
	"transfer_possibility",
	"housing_single",
	"housing_family",
	"basic_salary",
	"monthly_salary_total",
	"bonus_new_grad",
	"holidays",
	"annual_holidays",
	"multi_application_acceptance",
	"multi_application_start_date",
	"workplace_visit_acceptance",
	"workplace_visit_schedule",
	"contact_department_role",
	"contact_name",
	"contact_name_kana",
	"contact_phone",
	"contact_fax",
	"contact_email",
	"supplementary_notes",
	"special_notes",
	"industry_classification",
	"occupation_class_3digits",
	"occupation_class_5digits",
	"occupation_class2_3digits",
	"occupation_class2_5digits",
	"work_city",
	"work_city2",
	"work_city3",
	"management_number",
	"target_department",
	"area_name",
	"graduate_hiring_record",
	"other_classification",
	"recruitment_count",
	"school_visit",
	"secondary_recruitment",
	"visit_count",
	"applicant_count",
	"hiring_count",
	"remarks",
	"handover_notes",
	"publish_datetime",
	"last_updated_datetime",
	"registration_method",
	"original_filename",
	"hw_occ_class_large",
	"hw_occ_class_middle",
	"hw_occ_class_small",
	"handy_occ_class_large",
	"handy_occ_class_middle",
	"hw_occ_class2_large",
	"hw_occ_class2_middle",
	"hw_occ_class2_small",
	"handy_occ_class2_large",
	"handy_occ_class2_middle",
	"favorite_count"
];

$check_num = [
	"recruitment_year",
	"employee_count_total",
	"employee_count_office",
	"vacancies_commute",
	"vacancies_live_in",
	"vacancies_any",
	"basic_salary",
	"monthly_salary_total",
	"annual_holidays",
	"recruitment_count",
	"visit_count",
	"applicant_count",
	"hiring_count",
	"favorite_count",
];

$check_management_number = [
	"management_number",
];

$check_null = [
	"recruitment_year",
	"job_offer_id",
	"reception_center",
	"office_number",
	"is_public_internet",
	"company_name_kana",
	"company_name",
	"postal_code",
	"address",
	"access_info",
	"representative_name",
	"corporate_number",
	"website_url",
	"employee_count_total",
	"employee_count_office",
	"established_date",
	"capital_stock",
	"business_description",
	"company_features",
	"employment_type",
	"job_category",
	"job_description",
	"work_postal_code",
	"work_address",
	"work_access",
	"vacancies_commute",
	"vacancies_live_in",
	"vacancies_any",
	"required_skills",
	"transfer_possibility",
	"housing_single",
	"housing_family",
	"basic_salary",
	"monthly_salary_total",
	"bonus_new_grad",
	"holidays",
	"annual_holidays",
	"multi_application_acceptance",
	"multi_application_start_date",
	"workplace_visit_acceptance",
	"workplace_visit_schedule",
	"contact_department_role",
	"contact_name",
	"contact_name_kana",
	"contact_phone",
	"contact_fax",
	"contact_email",
	"supplementary_notes",
	"special_notes",
	"industry_classification",
	"occupation_class_3digits",
	"occupation_class_5digits",
	"work_city",
	"management_number",
	"publish_datetime",
	"last_updated_datetime",
	"registration_method",
	"original_filename",
	"hw_occ_class_large",
	"hw_occ_class_middle",
	"hw_occ_class_small",
	"handy_occ_class_large",
	"handy_occ_class_middle",
	"favorite_count"
];

$validation_rules = [
	[
		"columns" => $check_num,
		"validator" => fn($v) => ($v !== "" && !is_numeric($v)) ? "数値ではありません" : null
	],
	[
		"columns" => $check_management_number,
		"validator" => fn($v) => ($v !== "" && !preg_match('/^[0-9]{3,4}-[0-9]$/', $v)) ? "不正です" : null
	],
	[
		"columns" => $check_null,
		"validator" => fn($v) => ($v === null || $v === "") ? "空です" : null
	],
];

function validate_csv_row($cols, $colsCnt, $db_columns, $check_list, $validator, $column_comments, &$errors)
{
	$rowHasError = false;

	foreach ($check_list as $colName) {
		$idx = array_search($colName, $db_columns);

		// カラムが見つからなければスキップ
		if ($idx === false || !isset($cols[$idx])) {
			continue;
		}

		$value = $cols[$idx];

		// エラーメッセージを受け取る
		$errorMessage = $validator($value);

		$comment = $column_comments[$colName] ?? $colName;

		if ($errorMessage !== null) {
			if ($errorMessage === "空です") {
				$errors[] = "{$colsCnt} 行目の {$comment} ({$colName}) の値が{$errorMessage}。";
			} else {
				$errors[] = "{$colsCnt} 行目の {$comment} ({$colName}) の値が{$errorMessage}。（値: {$value}）";
			}
			$rowHasError = true;
		}
	}
	return $rowHasError;
}

function fetchColumnComments(PDO $db, string $dbName, string $tableName): array
{
	$sql = "
        SELECT COLUMN_NAME, COLUMN_COMMENT 
        FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = '" . $dbName . "' AND TABLE_NAME = '" . $tableName . "'
    ";

	$result = $db->query($sql);

	if ($result === false) {
		return [];
	}

	$comments = [];

	while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
		// コメントがない場合はカラム名を返す
		$comments[$row['COLUMN_NAME']] = !empty($row['COLUMN_COMMENT']) ? $row['COLUMN_COMMENT'] : $row['COLUMN_NAME'];
	}

	return $comments;
}

if (!isset($file)) {
	print "CSVファイルなし";
	exit;
}

if ($file["error"] !== UPLOAD_ERR_OK) {
	print "ファイルアップロードエラーが発生しました:" . $file["error"];
	exit;
}

if ($file["type"] !== "text/csv") {
	print "ファイルがcsv形式ではありません。";
	exit;
}

$fp = fopen($file["tmp_name"], 'r');
if ($fp === false) {
	print "ファイルを読み込めませんでした";
	exit;
}

// ヘッダを読み飛ばす
$header = fgetcsv($fp);

$column_count = count($db_columns);

try {
	$column_comments = fetchColumnComments($db, $dbName, $tableName);
	$db->beginTransaction();

	$colsCnt = 1;
	$errors = [];

	while (($cols = fgetcsv($fp)) !== false) {
		$rowHasError = false;

		if (count($cols) === 1 && trim($cols[0]) === "") {
			continue;
		}

		$cols_sql = array_map(function ($val) {
			return ($val === "" ? "NULL" : "'$val'");
		}, $cols);

		if (count($cols) !== $column_count) {
			$errors[] = "列数が定義と一致しません: CSV列数: " . count($cols) . ", DB列数: " . $column_count;
			$rowHasError = true;
			continue;
		}

		if ($validate_csv) {
			foreach ($validation_rules as $rule) {
				$rowHasError = validate_csv_row(
					$cols,
					$colsCnt,
					$db_columns,
					$rule['columns'],
					$rule['validator'],
					$column_comments,
					$errors
				) || $rowHasError;
			}
		}

		$colsCnt++;

		if ($rowHasError) {
			continue;
		}

		$sql = "INSERT into job_offers (`" . implode("`,`", $db_columns) . "`) VALUES (" . implode(",", $cols_sql) . ")";

		$db->exec($sql);
	}

	if (!empty($errors)) {
		$db->rollBack();
		echo "<h3>登録エラー:</h3><br>";
		foreach ($errors as $err) {
			print $err . "<input type='checkbox'><br>";
		}
		fclose($fp);
		exit;
	}

	$db->commit();
	print "登録しました。";

} catch (Exception $e) {
	$db->rollBack();
	fclose($fp);
	print '登録エラー: ' . $e->getMessage();
}

fclose($fp);

//header("Location: index.php", true, 301);
//exit;
