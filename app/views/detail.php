<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/detail.css">
    <link rel="stylesheet" href="assets/css/home.css">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <title>Document</title>
</head>
<div class="container">
        <!-- Les images à gauche -->
        <?= $images ?>

        <!-- Le formulaire à droite -->
        <div class="form-container">
            <h1>An habitation located at <?= $habitation["quartier"]?></h1>
            <p style="text-align: center;"><?= $habitation["description"]?></p>
            <?php if ($message!="" && $status!=""): ?>
                <div class="message-box <?= $status ?>">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <?= $form ?>
        </div>
    </div>
</html>