<?php
session_start();
$isConnected = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil - Médiathèque</title>
    <link rel="stylesheet" href="css/index.css?v=<?php echo time(); ?>">
</head>
<body>
    <header>
        <nav class="navbar">
            <h1 class="logo">📚 Médiathèque</h1>
            <ul class="menu">
                <li><a href="catalogue.php">Catalogue</a></li>
                <li><a href="php/contact.php">Contact</a></li>
            </ul>
            <div class="auth-buttons">
                <?php if ($isConnected): ?>
                    <a href="php/deconnexion.php" class="btn">Se déconnecter</a>
                <?php else: ?>
                    <a href="connexion.php" class="btn">Connexion</a>
                    <a href="inscription.html" class="btn btn-outline">Inscription</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <main class="main-content">
    <section class="intro">
    <div class="overlay">
        <h2>Bienvenue à la Médiathèque</h2>
        <p>📖 Découvrez notre collection en ligne</p>
        <a href="catalogue.php" class="cta">Voir le catalogue</a>
    </div>
</section>
    </main>

    <footer>
        <p>&copy; 2025 Médiathèque de Montpellier — Tous droits réservés.</p>
    </footer>
</body>
</html>
