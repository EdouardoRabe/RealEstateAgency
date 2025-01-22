<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- <link rel="stylesheet" href="assets/css/admin.css"> -->
    <link rel="stylesheet" href="assets/css/create.css">
    <title>UPDATE</title>
</head>
<body>
    <nav class="navbar">
        <?php if (isset($_GET["error"])) { ?>
            <div class="error-message">
                <?= htmlspecialchars($_GET["error"]) ?>
            </div>
        <?php } ?>

        <?php if (isset($id)) { ?>
            <form action="upload?id=<?= htmlspecialchars($id) ?>" method="post" enctype="multipart/form-data">
                <?= $upload ?>
                <?= $select ?>
                <?= $input ?>
                <input type="submit" value="Valid">
            </form>
        <?php } else { ?>
            <form action="creation" method="post" enctype="multipart/form-data">
                <?= $upload ?>
                <?= $select ?>
                <?= $input ?>
                <input type="submit" value="Valid">
            </form>
        <?php } ?>
    </nav>
</body>
</html>
