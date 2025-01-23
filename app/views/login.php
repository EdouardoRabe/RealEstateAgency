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

            function switchForms(hideForm, showForm) {
                // Add slide-out effect to hiding form
                hideForm.style.animation = 'slideOut 0.5s forwards';
                
                // Wait for slide-out to complete before showing new form
                setTimeout(() => {
                    hideForm.classList.remove("active");
                    hideForm.style.animation = '';
                    
                    // Add slide-in effect to new form
                    showForm.classList.add("active");
                    showForm.style.animation = 'slideIn 0.5s forwards';
                }, 500);
            }

            showSignUpBtn.addEventListener("click", function() {
                switchForms(loginForm, signUpForm);
            });

            showLoginBtn.addEventListener("click", function() {
                switchForms(signUpForm, loginForm);
            });

            // Initial state
            loginForm.classList.add("active");
            signUpForm.classList.remove("active");

            // Add keyframe animations
            const styleSheet = document.createElement("style");
            styleSheet.type = "text/css";
            styleSheet.innerText = `
                @keyframes slideOut {
                    0% { transform: translateX(0); opacity: 1; }
                    100% { transform: translateX(-100%); opacity: 0; }
                }
                @keyframes slideIn {
                    0% { transform: translateX(100%); opacity: 0; }
                    100% { transform: translateX(0); opacity: 1; }
                }
            `;
            document.head.appendChild(styleSheet);
        });

    </script>
</body>
</html>
