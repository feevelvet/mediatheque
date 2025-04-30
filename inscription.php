<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription - Médiathèque</title>
    <link rel="stylesheet" href="css/connexion.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <h1 class="logo">📚 Médiathèque</h1>
            <ul class="menu">
                <li><a href="index.php">Accueil</a></li>
                <li><a href="connexion.php">Se connecter</a></li>
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
        <h2>Créer un compte</h2>
        <form action="php/inscription.php" method="POST">
            <label for="nom">Nom:</label>
            <input type="text" id="nom" name="nom" required>

            <label for="prenom">Prénom:</label>
            <input type="text" id="prenom" name="prenom" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="motdepasse">Mot de passe:</label>
            <input type="password" id="motdepasse" name="motdepasse" required>

            <!-- Retirer le champ du rôle -->
            <input type="hidden" name="role" value="client"> <!-- Attribuer un rôle par défaut -->

            <button type="submit">S'inscrire</button>
        </form>
    </main>
    <?php if (isset($_GET['erreur'])): ?>
    <p style="color: red;">
        <?php
        if ($_GET['erreur'] == 'email_existe') {
            echo "L'email est déjà utilisé. Veuillez choisir un autre email.";
        } elseif ($_GET['erreur'] == 'inscription') {
            echo "Une erreur est survenue lors de l'inscription. Veuillez réessayer.";
        } elseif ($_GET['erreur'] == 'preparation_sql') {
            echo "Une erreur de base de données est survenue. Veuillez réessayer.";
        }
        ?>
    </p>
<?php endif; ?>


    <footer>
        <p>&copy; 2025 Médiathèque de Montpellier</p>
    </footer>
</body>
</html>
<script src="js/burger.js"></script>