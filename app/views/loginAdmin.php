<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/admin.css">
    <title>Document</title>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Connexion</h1>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="error-message">
                <?= $error ?>
            </div>
        <?php endif; ?>
        
            <?= $login ?>
        
    </div>
</body>
</html>