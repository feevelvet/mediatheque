<?php
session_start();
require_once 'php/db_config.php';


$isConnected = isset($_SESSION['user_id']);
$user_email = $_SESSION['email'] ?? null;



?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Catalogue - Médiathèque</title>
    <link rel="stylesheet" href="css/catalogue.css">
</head>
<body>

<header>
    <nav class="navbar">
        <h1 class="logo">📚 Médiathèque</h1>
        <ul class="menu">
    <li><a href="index.php">Acceuil</a></li>
    <li><a href="php/contact.php">Contact</a></li>
    <?php if ($isConnected): ?>
        <li><a href="php/dashboard.php">Dashboard</a></li>
    <?php endif; ?>
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

<div class="content-wrapper">
    <main>
        <div class="categories">
            <a href="categorie/livre.php">
                <div class="categorie">
                    <img src="assets/livre.jpg" alt="Livres">
                    <span>Livres</span>
                </div>
            </a>
            <a href="categorie/cd.php">
                <div class="categorie">
                    <img src="assets/cd.jpg" alt="CD">
                    <span>CD</span>
                </div>
            </a>
            <a href="categorie/dvd.php">
                <div class="categorie">
                    <img src="assets/dvd.jpg" alt="DVD">
                    <span>DVD</span>
                </div>
            </a>
            <a href="categorie/titres_periodiques.php">
                <div class="categorie">
                    <img src="assets/titres_periodiques.jpeg" alt="titres_periodiques">
                    <span>Titres périodiques</span>
                </div>
            </a>
        </div>
    </main>
</div>

<footer>
    <p>&copy; 2025 Médiathèque de Montpellier — Tous droits réservés.</p>
</footer>

</body>
</html>
