<?php
session_start();


if (!isset($_SESSION['email'])) {
    echo "<div class='alert'>Vous devez être connecté.</div>";
    exit();
}
$isConnected = isset($_SESSION['user_id']);
$email = $_SESSION['email'];


include('db_config.php');


$sql = "SELECT * FROM abonne WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$abonne = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Abonnement</title>
    <!-- LIEN CSS ICI -->
    <link rel="stylesheet" href="../css/abonnement.css">
</head>
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
    <?php endif; ?>
                <li><a href="../php/contact.php">Contact</a></li>
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
<body>
    <div class="container">
<?php
if ($abonne) {
    
    echo "<h2>Bonjour " . htmlspecialchars($abonne['prenom']) . " " . htmlspecialchars($abonne['nom']) . "</h2>";
    echo "<p>Vous êtes déjà abonné.</p>";
    echo "<p>Type : <strong>" . htmlspecialchars($abonne['type_abonnement']) . "</strong></p>";
    echo "<p>Statut : <strong>" . htmlspecialchars($abonne['statut_abonnement']) . "</strong></p>";
    $statut = strtolower($abonne['statut_abonnement']);

    
    $date_expiration = new DateTime($abonne['date_expiration']);
    $today = new DateTime();

    if (in_array($statut, ['expiré', 'refusé', 'inactif']))  {
        echo "<p style='color:red;'>Votre abonnement est <strong>$statut</strong>. Vous pouvez le renouveler.</p>";

        
        $date_naissance = new DateTime($abonne['date_naissance']);
        $age = $date_naissance->diff($today)->y;

        
        if ($age < 18) {
            echo "<p>L'abonnement est <strong>gratuit</strong> pour les moins de 18 ans.</p>";
            echo '<form method="post" action="mettre_a_jour_abonnement.php" enctype="multipart/form-data">';
            echo '<input type="hidden" name="email" value="' . htmlspecialchars($email) . '">';
            echo '<input type="hidden" name="type_abonnement" value="gratuit">';
            echo '<label for="pdf_upload">Téléchargez un fichier PDF :</label>';
            echo '<input type="file" name="pdf_file" id="pdf_upload" accept=".pdf" required><br><br>';
            echo '<input type="submit" name="final_submit" value="Renouveler l\'abonnement gratuit" class="button">';
            echo '</form>';
        } else {
            echo "<p>Choisissez un type d'abonnement pour renouveler :</p>";
            echo '<form method="post" action="mettre_a_jour_abonnement.php">';
            echo '<input type="hidden" name="email" value="' . htmlspecialchars($email) . '">';
            echo '<label><input type="radio" name="type_abonnement" value="mensuel" required> Mensuel - 10,50 €</label><br>';
            echo '<label><input type="radio" name="type_abonnement" value="annuel" required> Annuel - 22 €</label><br><br>';
            echo '<input type="submit" name="final_submit" value="Souscrire" class="button">';
            echo '</form>';
        }
    } else {
        
        echo "<p>Votre abonnement est valide jusqu'au : <strong>" . $date_expiration->format('d/m/Y') . "</strong></p>";
    }

} else {
    
    if (!isset($_POST['submit_infos'])) {
        
        echo '<h2>Informations pour abonnement</h2>';
        echo '<form method="post" action="">';
        echo '<label>Nom : <input type="text" name="nom" required></label><br>';
        echo '<label>Prénom : <input type="text" name="prenom" required></label><br>';
        echo '<label>Date de naissance : <input type="date" name="date_naissance" required></label><br><br>';
        echo '<input type="submit" name="submit_infos" value="Continuer" class="button">';
        echo '</form>';
    } else {
        
        $nom = $_POST['nom'];
        $prenom = $_POST['prenom'];
        $date_naissance = $_POST['date_naissance'];

        $dateNaissanceObj = new DateTime($date_naissance);
        $today = new DateTime();
        $age = $dateNaissanceObj->diff($today)->y;

        echo "<h3>Bonjour $prenom $nom</h3>";
        echo "<p>Vous avez $age ans.</p>";

        if ($age < 18) {
            echo "<p>L'abonnement est <strong>gratuit</strong> pour les moins de 18 ans.</p>";
            
            echo '<form method="post" action="mettre_a_jour_abonnement.php" enctype="multipart/form-data">';
            echo '<input type="hidden" name="nom" value="'.htmlspecialchars($nom).'">';
            echo '<input type="hidden" name="prenom" value="'.htmlspecialchars($prenom).'">';
            echo '<input type="hidden" name="date_naissance" value="'.htmlspecialchars($date_naissance).'">';
            echo '<input type="hidden" name="type_abonnement" value="gratuit">';
            echo '<label for="pdf_upload">Téléchargez un fichier PDF :</label>';
            echo '<input type="file" name="pdf_file" id="pdf_upload" accept=".pdf" required><br><br>';
            echo '<input type="submit" name="final_submit" value="Valider abonnement gratuit" class="button">';
            echo '</form>';
        } else {
            
            echo '<form method="post" action="mettre_a_jour_abonnement.php">';
            echo '<p>Choisissez un type d\'abonnement :</p>';
            echo '<input type="hidden" name="nom" value="'.htmlspecialchars($nom).'">';
            echo '<input type="hidden" name="prenom" value="'.htmlspecialchars($prenom).'">';
            echo '<input type="hidden" name="date_naissance" value="'.htmlspecialchars($date_naissance).'">';
            echo '<label><input type="radio" name="type_abonnement" value="mensuel" required> Mensuel - 10,50 €</label><br>';
            echo '<label><input type="radio" name="type_abonnement" value="annuel" required> Annuel - 22 €</label><br><br>';
            echo '<input type="submit" name="final_submit" value="Souscrire" class="button">';
            echo '</form>';
        }
    }
}

echo "</div>";


$stmt->close();
$conn->close();
?>

    </div>
</body>
</html>
<script src="../js/burger.js"></script>