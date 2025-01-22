<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- <link rel="stylesheet" href="assets/css/admin.css"> -->
    <link rel="stylesheet" href="assets/css/home.css">
    <title>UPDATE</title>
</head>
<body>
    <nav class="navbar">
        <h1>UPDATE</h1>
        <form action="upload?id=<?= $id ?>" method="post" enctype="multipart/form-data">
            <?= $upload ?>
            <?= $select ?>
            <?= $input ?>
            <input type="submit" value="Valid">
        </form>
    </nav>
</body>
</html>