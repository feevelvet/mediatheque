<?php $erreur = isset($_GET['erreur']) && $_GET['erreur'] == 1; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - Médiathèque</title>
    <link rel="stylesheet" href="css/connexion.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <h1 class="logo">📚 Médiathèque</h1>
            <ul class="menu">
                <li><a href="index.php">Accueil</a></li>
                <li><a href="inscription.php">S'inscrire</a></li>
                <li><a href="catalogue.php">Notre catalogue</a></li>
                <li><a href="php/contact.php">Nous contacter</a></li>
            </ul>
            <div class="burger">
                <div></div>
                <div></div>
                <div></div>
            </div>
        </nav>
    </header>

    <main class="main-content">
        <form action="php/connexion.php" method="POST" class="<?php echo $erreur ? 'shake' : ''; ?>">
            <h2>Se connecter</h2>

            <?php if ($erreur): ?>
                <p class="error-message">❌ Email ou mot de passe incorrect</p>
            <?php endif; ?>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required class="<?php echo $erreur ? 'error' : ''; ?>">

            <label for="motdepasse">Mot de passe:</label>
            <input type="password" id="motdepasse" name="motdepasse" required class="<?php echo $erreur ? 'error' : ''; ?>">

            <button type="submit">Se connecter</button>
        </form>
    </main>

    <footer>
        <p>&copy; 2025 Médiathèque de Montpellier</p>
    </footer>

    <script src="js/burger.js"></script>
    <script src="js/erreur.js"></script>
</body>
</html>
