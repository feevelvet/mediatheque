<?php
session_start(); 
$erreurs = [];
$succes = "";
$isConnected = isset($_SESSION['user_id']);
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($nom)) $erreurs[] = "Le nom est obligatoire.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $erreurs[] = "L'email est invalide.";
    if (empty($message)) $erreurs[] = "Le message est obligatoire.";

    if (empty($erreurs)) {
        
        
        
        
        

        $succes = "Votre message a bien été envoyé. Merci !";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Contact - Médiathèque</title>
    <link rel="stylesheet" href="../css/contact.css">
    <style>
        form { max-width: 600px; margin: auto; }
        .error { color: red; }
        .success { color: green; }
        label { display: block; margin-top: 1em; }
        input, textarea { width: 100%; padding: 8px; margin-top: 5px; }
        .btn { margin-top: 1em; }
        .contact-info { margin-top: 50px; text-align: center; }
        iframe { width: 100%; height: 300px; border: 0; margin-top: 20px; }
    </style>
</head>
<body>
<header>
        <nav class="navbar">
            <h1 class="logo">📚 Médiathèque</h1>
            <div class="burger">
                <div></div>
                <div></div>
                <div></div>
            </div>
            <ul class="menu"style="display: none;">
                <li><a href="../index.php">Acceuils</a></li>
                <?php if ($isConnected): ?>
        <li><a href="../php/dashboard.php">Dashboard</a></li>
        <li><a href="../catalogue.php">Catalogue</a></li>
    <?php endif; ?>
            </ul>
            <div class="auth-buttons">
                <?php if ($isConnected): ?>
                    <a href="../php/deconnexion.php" class="btn">Se déconnecter</a>
                <?php else: ?>
                    <a href="../connexion.php" class="btn">Connexion</a>
                    <a href="../inscription.html" class="btn btn-outline">Inscription</a>
                <?php endif; ?>
            </div>
            
        </nav>
    </header>
    <h1>Contactez-nous</h1>

    <?php if (!empty($erreurs)): ?>
        <div class="error">
            <ul>
                <?php foreach ($erreurs as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php elseif ($succes): ?>
        <div class="success"><?= $succes ?></div>
    <?php endif; ?>

    <form method="post">
        <label for="nom">Nom *</label>
        <input type="text" id="nom" name="nom" required value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">

        <label for="email">Email *</label>
        <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

        <label for="message">Message *</label>
        <textarea id="message" name="message" rows="6" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>

        <button type="submit" class="btn">Envoyer</button>
    </form>

    <div class="contact-info">
        <h2>Nos coordonnées</h2>
        <p><strong>Médiathèque de Montpellier</strong></p>
        <p>123 rue de la Lecture, 34000 Montpellier</p>
        <p>Téléphone : 04 67 00 00 00</p>
        <p>Email : contact@mediatheque-montpellier.fr</p>

        <!-- Optionnel : Carte Google Maps -->
        <iframe
            src="https:
            allowfullscreen=""
            loading="lazy">
        </iframe>
    </div>
</body>
</html>
<script src="../js/burger.js"></script>