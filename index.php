
<!DOCTYPE html>
<html lang="ja">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>アップロード</title>
</head>

<body>
	<h2>アップロード</h2>

	<form action="upload.php" method="post" enctype="multipart/form-data">
		<p><input type="file" name="data" accept=".csv" required></p>
		<p><button type="submit">送信</button></p>
	</form>

	<br><br>

	<form action="truncate.php" method="post">
		<p><button type="submit">テーブル初期化</button></p>
	</form>
</body>

</html>
