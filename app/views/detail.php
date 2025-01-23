<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Habitations</title>
    <link rel="stylesheet" href="assets/css/detail.css">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/fontawesome-free-6.6.0-web/css/all.min.css">
   
</head>
<body>

    <nav class="navbar">
        <h1>DETAILS</h1>
    </nav>

    
    <div class="container">
        <?= $images ?>
        <div class="form-container">
            <h1>An habitation located at <?= $habitation["quartier"] ?></h1>
            <p style="text-align: center;"><?= $habitation["description"] ?></p>
            <?php if ($message != "" && $status != ""): ?>
                <div class="message-box <?= $status ?>">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <?= $form ?>
        </div>
    </div>

    <footer class="site-footer">
        <div class="footer-content">
            <p>&copy; 2025 | ETU003285 Edouardo, ETU003276 Mickaella</p>
        </div>
    </footer>

</body>
</html>

