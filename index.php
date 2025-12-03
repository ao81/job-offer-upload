<?php

session_start();

if (isset($_SESSION["validate_csv_session"])) {
	$validate_csv = $_SESSION["validate_csv_session"];
} else {
	$validate_csv = true;
}

?>

<!DOCTYPE html>
<html lang="ja">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>アップロード</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
</head>

<style>
	body {
		background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
		min-height: 100vh;
		display: flex;
		align-items: center;
		justify-content: center;
	}

	.card {
		border: none;
		border-radius: 1rem;
	}
</style>

<body>
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-md-8 col-lg-6 col-xl-5">

				<div class="card card-body p-4">

					<div class="text-center mb-4">
						<h3 class="fw-bold">アップロード</h3>
					</div>

					<form action="upload.php" method="post" enctype="multipart/form-data">
						<div class="mb-4">
							<label for="csvFile" class="form-label fw-semibold">ファイル選択</label>
							<div class="input-group">
								<input class="form-control" type="file" id="csvFile" name="data" accept=".csv" required>
							</div>
							<div class="form-text text-end">※.csv形式のみ</div>
						</div>

						<div class="d-grid gap-2">
							<button type="submit" class="btn btn-primary btn-lg shadow-sm">送信する</button>
						</div>
					</form>

					<hr class="my-5 opacity-25" />

					<div class="text-center d-grid">
						<p class="text-danger small fw-bold mb-2">管理用メニュー</p>

						<form action="truncate.php" method="post" onsubmit="return confirm('本当に初期化しますか？');">
							<div class="d-grid">
								<button type="submit" class="btn btn-outline-danger btn-sm">テーブルを初期化</button>
							</div>
						</form>

						<button type="button" id="toggle-validation-btn" class="btn btn-outline-danger btn-sm mt-2" data-status="<?php print $validate_csv ? 'enabled' : 'disabled'; ?>">
							<?php if ($validate_csv): ?>
								データの検問を無効化
							<?php else: ?>
								データの検問を有効化
							<?php endif; ?>
						</button>
					</div>

				</div>
			</div>
		</div>

		<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js" integrity="sha384-fbbOQedDUMZZ5KreZpsbe1LCZPVmfTnH7ois6mU1QK+m14rQ1l2bGBq41eYeM/fS" crossorigin="anonymous"></script>
</body>

<script>
	document.addEventListener("DOMContentLoaded", function() {
		const toggleButton = document.getElementById("toggle-validation-btn");

		toggleButton.addEventListener("click", function() {
			fetch("toggle_validation.php", {
					method: "POST",
				})
				.then(res => res.json())
				.then(data => {
					if (data.validation_enabled) {
						toggleButton.textContent = "データの検問を無効化";
						toggleButton.dataset.status = "enabled";
					} else {
						toggleButton.textContent = "データの検問を有効化";
						toggleButton.dataset.status = "disabled";
					}
				})
				.catch(error => {
					console.error("エラー: ", error);
				});
		});
	});
</script>

</html>
