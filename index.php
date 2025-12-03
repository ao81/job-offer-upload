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

						<div class="mb-4">
							<label class="form-label fw-semibold">重複データの扱い</label>
							<div class="form-text mx-1 mt-0 mb-2 small">
								同じ主キー（求人番号）が既に登録されている行があった場合の動作を選択してください。
							</div>
							<div class="form-check">
								<input class="form-check-input" type="radio" name="duplicate_action" id="duplicate_cancel" value="cancel" checked>
								<label class="form-check-label" for="duplicate_cancel">
									重複行はスキップ（既存データを残す）
								</label>
							</div>
							<div class="form-check">
								<input class="form-check-input" type="radio" name="duplicate_action" id="duplicate_overwrite" value="overwrite">
								<label class="form-check-label" for="duplicate_overwrite">
									既存データを上書きする
								</label>
							</div>
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

						<button type="button" id="toggle-validation-btn" class="btn btn-sm mt-2 <?= $validate_csv ? "btn-outline-success" : "btn-outline-danger" ?>" data-status="<?= $validate_csv ? 'enabled' : 'disabled'; ?>">
							<div class="d-grid">
								<?php if ($validate_csv): ?>
									データの検問：有効
								<?php else: ?>
									データの検問：無効
								<?php endif; ?>
							</div>
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
						toggleButton.textContent = "データの検問：有効";
						toggleButton.dataset.status = "enabled";

						toggleButton.classList.remove("btn-outline-danger");
						toggleButton.classList.add("btn-outline-success");
					} else {
						toggleButton.textContent = "データの検問：無効";
						toggleButton.dataset.status = "disabled";

						toggleButton.classList.remove("btn-outline-success");
						toggleButton.classList.add("btn-outline-danger");
					}
				})
				.catch(error => {
					console.error("エラー: ", error);
				});
		});
	});
</script>

</html>