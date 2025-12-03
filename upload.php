<?php

session_start();

$dbName = "ao";
$dbHost = "127.0.0.1";
$dbUser = "root";
$dbPass = "";
$tableName = "job_offers";

// 重複時の動作（cancel: 重複行をスキップ, overwrite: 既存行を上書き）
$duplicateAction = $_POST["duplicate_action"] ?? "cancel";

if (!isset($_SESSION["validate_csv_session"])) {
	$validate_csv = true;
} else {
	$validate_csv = $_SESSION["validate_csv_session"];
}

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
		"validator" => fn($v) => empty($v) ? "空です" : null
	],
];

$jobOfferIdIndex = array_search("job_offer_id", $db_columns);

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

		// 重複時の挙動
		if ($duplicateAction === "overwrite") {
			$sql = "REPLACE INTO job_offers (`" . implode("`,`", $db_columns) . "`) VALUES (" . implode(",", $cols_sql) . ")";
		} else {
			$sql = "INSERT IGNORE INTO job_offers (`" . implode("`,`", $db_columns) . "`) VALUES (" . implode(",", $cols_sql) . ")";
		}

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
	print '<a class="btn btn-primary" href="./index.php" role="button">戻る</a>';
} catch (Exception $e) {
	$db->rollBack();
	fclose($fp);
	print '登録エラー: ' . $e->getMessage();
}

fclose($fp);

//header("Location: index.php", true, 301);
//exit;

/*
SET NAMES utf8mb4

CREATE TABLE job_offers (
	recruitment_year INT COMMENT '求人年度',
	job_offer_id VARCHAR(20) NOT NULL PRIMARY KEY COMMENT '求人番号',
	reception_center VARCHAR(50) COMMENT '受付安定所',
	office_number VARCHAR(20) COMMENT '事業所番号',
	is_public_internet VARCHAR(10) COMMENT 'インターネットへの公開',
	company_name_kana VARCHAR(255) COMMENT '事業所名 カナ',
	company_name VARCHAR(255) COMMENT '事業所名',
	postal_code VARCHAR(10) COMMENT '郵便番号（所在地）',
	address VARCHAR(255) COMMENT '住所（所在地）',
	access_info VARCHAR(255) COMMENT 'アクセス（所在地）',
	representative_name VARCHAR(100) COMMENT '代表者名',
	corporate_number VARCHAR(20) COMMENT '法人番号',
	website_url VARCHAR(2048) COMMENT 'ホームページ',
	employee_count_total INT COMMENT '従業員数（企業全体）',
	employee_count_office INT COMMENT '従業員数（就業場所）',
	established_date VARCHAR(50) COMMENT '設立',
	capital_stock VARCHAR(50) COMMENT '資本金',
	business_description TEXT COMMENT '事業内容',
	company_features TEXT COMMENT '会社の特長',
	employment_type VARCHAR(50) COMMENT '雇用形態',
	job_category VARCHAR(100) COMMENT '職種',
	job_description TEXT COMMENT '仕事の内容',
	work_postal_code VARCHAR(10) COMMENT '郵便番号（就業場所）',
	work_address VARCHAR(255) COMMENT '住所（就業場所）',
	work_access VARCHAR(255) COMMENT 'アクセス（就業場所）',
	vacancies_commute INT COMMENT '求人数（通勤）',
	vacancies_live_in INT COMMENT '求人数（住込）',
	vacancies_any INT COMMENT '求人数（不問）',
	required_skills TEXT COMMENT '必要な知識・技能等',
	transfer_possibility VARCHAR(50) COMMENT '転勤の可能性',
	housing_single VARCHAR(50) COMMENT '入居可能住宅（単身用）',
	housing_family VARCHAR(50) COMMENT '入居可能住宅（世帯用）',
	basic_salary INT COMMENT '基本給',
	monthly_salary_total INT COMMENT '月給（基本給+手当+残業代）',
	bonus_new_grad VARCHAR(10) COMMENT '賞与有無（新卒）',
	holidays VARCHAR(100) COMMENT '休日等',
	annual_holidays INT COMMENT '年間休日数',
	multi_application_acceptance VARCHAR(50) COMMENT '複数応募の受け入れ',
	multi_application_start_date VARCHAR(50) COMMENT '複数応募開始の日程',
	workplace_visit_acceptance VARCHAR(10) COMMENT '職場見学の受け入れ',
	workplace_visit_schedule VARCHAR(100) COMMENT '職場見学の日程',
	contact_department_role VARCHAR(100) COMMENT '課係名・役職名（担当者）',
	contact_name VARCHAR(50) COMMENT '氏名（担当者）',
	contact_name_kana VARCHAR(50) COMMENT '氏名カナ（担当者）',
	contact_phone VARCHAR(20) COMMENT '電話番号（担当者）',
	contact_fax VARCHAR(20) COMMENT 'FAX（担当者）',
	contact_email VARCHAR(255) COMMENT 'Eメール（担当者）',
	supplementary_notes TEXT COMMENT '補足事項',
	special_notes TEXT COMMENT '特記事項',
	industry_classification VARCHAR(10) COMMENT '産業分類',
	occupation_class_3digits VARCHAR(10) COMMENT '職業分類（3桁）',
	occupation_class_5digits VARCHAR(10) COMMENT '職業分類（5桁）',
	occupation_class2_3digits VARCHAR(10) COMMENT '職業分類2（3桁）',
	occupation_class2_5digits VARCHAR(10) COMMENT '職業分類2（5桁）',
	work_city VARCHAR(50) COMMENT '就業場所住所（市区町村）',
	work_city2 VARCHAR(50) COMMENT '就業場所住所2（市区町村）',
	work_city3 VARCHAR(50) COMMENT '就業場所住所3（市区町村）',
	management_number VARCHAR(50) COMMENT '管理番号',
	target_department VARCHAR(100) COMMENT '対象学科',
	area_name VARCHAR(50) COMMENT 'エリア',
	graduate_hiring_record VARCHAR(100) COMMENT '卒業生の入社実績',
	other_classification VARCHAR(100) COMMENT 'その他の分類',
	recruitment_count INT COMMENT '募集人数',
	school_visit VARCHAR(10) COMMENT '来校の有無',
	secondary_recruitment VARCHAR(10) COMMENT '二次募集の有無',
	visit_count INT COMMENT '職場見学人数',
	applicant_count INT COMMENT '応募人数',
	hiring_count INT COMMENT '入社人数',
	remarks TEXT COMMENT '備考',
	handover_notes TEXT COMMENT '申送り事項',
	publish_datetime VARCHAR(50) COMMENT '公開日時',
	last_updated_datetime VARCHAR(50) COMMENT '最終更新日時',
	registration_method VARCHAR(50) COMMENT '登録方法',
	original_filename VARCHAR(255) COMMENT '元ファイル名',
	hw_occ_class_large VARCHAR(100) COMMENT '職業分類（ハローワーク）大分類',
	hw_occ_class_middle VARCHAR(100) COMMENT '職業分類（ハローワーク）中分類',
	hw_occ_class_small VARCHAR(100) COMMENT '職業分類（ハローワーク）小分類',
	handy_occ_class_large VARCHAR(100) COMMENT '職業分類（handy）大分類',
	handy_occ_class_middle VARCHAR(100) COMMENT '職業分類（handy）中分類',
	hw_occ_class2_large VARCHAR(100) COMMENT '職業分類2（ハローワーク）大分類',
	hw_occ_class2_middle VARCHAR(100) COMMENT '職業分類2（ハローワーク）中分類',
	hw_occ_class2_small VARCHAR(100) COMMENT '職業分類2（ハローワーク）小分類',
	handy_occ_class2_large VARCHAR(100) COMMENT '職業分類2（handy）大分類',
	handy_occ_class2_middle VARCHAR(100) COMMENT '職業分類2（handy）中分類',
	favorite_count INT COMMENT 'お気に入り登録数'
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

*/
