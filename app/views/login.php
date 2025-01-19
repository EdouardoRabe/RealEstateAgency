<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page de Connexion</title>
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <div class="container">
        <div class="form-container" id="loginForm" class="active">
            <h2>Connexion</h2>
            <?= $login ?>
            <button id="showSignUpBtn">Créer un compte</button>
        </div>

        <div class="form-container" id="signUpForm">
            <h2>Inscription</h2>
            <?= $signUp ?>
            <button id="showLoginBtn">Retour à la connexion</button>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
    const loginForm = document.getElementById("loginForm");
    const signUpForm = document.getElementById("signUpForm");
    const showSignUpBtn = document.getElementById("showSignUpBtn");
    const showLoginBtn = document.getElementById("showLoginBtn");

    showSignUpBtn.addEventListener("click", function() {
        loginForm.classList.remove("active");
        signUpForm.classList.add("active");
    });

    showLoginBtn.addEventListener("click", function() {
        signUpForm.classList.remove("active");
        loginForm.classList.add("active");
    });

    loginForm.classList.add("active");
    signUpForm.classList.remove("active");
});

    </script>
</body>
</html>
