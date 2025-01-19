<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Habitations</title>
    <link rel="stylesheet" href="assets/css/home.css">
    <link rel="stylesheet" href="assets/css/fontawesome-free-6.6.0-web/css/all.min.css">
   
</head>
<body>

    <nav class="navbar">
        <h1>Home</h1>
        <form action="home" method="get">
            <?= $select ?>
            <?= $input ?>
            <input type="submit" value="Search">
        </form>
    </nav>

    <div class="container">
        <h1>Liste des Habitations</h1>
        <div class="card-container">
            <?= $habitations ?>
        </div>
    </div>

</body>
</html>
