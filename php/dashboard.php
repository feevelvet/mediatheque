<?php
session_start();


if (!isset($_SESSION['user_id'])) {
    header("Location: ../connexion.php");
    exit();
}


$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "mediatheque";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$email_utilisateur = $_SESSION['email'];

$sql = "SELECT livre.titre, livre.auteur, emprunt.date_emprunt, emprunt.date_retour, emprunt.retour_effectif
        FROM emprunt
        JOIN livre ON emprunt.id_livre = livre.id
        WHERE emprunt.emprunte_par_email = ? AND emprunt.retour_effectif IS NULL";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email_utilisateur);
$stmt->execute();
$result = $stmt->get_result();

$livres_empruntes = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $livres_empruntes[] = $row;
    }
}

$stmt->close();


$messages = [];


foreach ($livres_empruntes as $livre) {
    $date_retour = new DateTime($livre['date_retour']);
    $aujourdhui = new DateTime();
    if ($date_retour < $aujourdhui) {
        $messages[] = "<span style='color: red; font-weight: bold;'>Votre livre '{$livre['titre']}' est en retard. Merci de le rendre dès que possible.</span>";

    }
}


$sql_abonnement = "SELECT * FROM abonne WHERE email = '$email_utilisateur' AND date_expiration < DATE_ADD(NOW(), INTERVAL 7 DAY)";
$res_abonnement = mysqli_query($conn, $sql_abonnement);
if (mysqli_num_rows($res_abonnement) > 0) {
    $messages[] = "<span style='color: red; font-weight: bold;'>Votre abonnement arrive à expiration dans les 7 prochains jours. Pensez à le renouveler.</span>";
}

$sql_precommande = "SELECT id_livre, type FROM precommande WHERE email = ?";
$stmt_precommande = $conn->prepare($sql_precommande);
$stmt_precommande->bind_param("s", $email_utilisateur);
$stmt_precommande->execute();
$result_precommande = $stmt_precommande->get_result();

while ($row = $result_precommande->fetch_assoc()) {
    $id_livre = $row['id_livre'];
    $type = $row['type'];

    
    switch ($type) {
        case 'livre':
            $sql_detail = "SELECT titre FROM livre WHERE id = ?";
            break;
        case 'cd':
            $sql_detail = "SELECT titre FROM cd WHERE id = ?";
            break;
        case 'dvd':
            $sql_detail = "SELECT titre FROM dvd WHERE id = ?";
            break;
        default:
            break; 
    }

    $stmt_detail = $conn->prepare($sql_detail);
    $stmt_detail->bind_param("i", $id_livre);
    $stmt_detail->execute();
    $result_detail = $stmt_detail->get_result();
    $detail = $result_detail->fetch_assoc();
    $titre_livre = $detail['titre'] ?? 'Inconnu';

    $stmt_detail->close();

    
    $sql_dispo = "SELECT * FROM emprunt WHERE id_livre = ? AND retour_effectif IS NULL";
    $stmt_dispo = $conn->prepare($sql_dispo);
    $stmt_dispo->bind_param("i", $id_livre);
    $stmt_dispo->execute();
    $result_dispo = $stmt_dispo->get_result();

    if ($result_dispo->num_rows == 0) {
        $messages[] = "<span style='color: green; font-weight: bold;'>Le {$type} que vous avez précommandé \"{$titre_livre}\" est maintenant disponible. 
               <a href='../categorie/confirmation_emprunt_{$type}.php?id_livre={$id_livre}' style='background-color: green; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px;'>Voir le {$type}</a>
               </span>";
    }

    $stmt_dispo->close();
}

$stmt_precommande->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Médiathèque</title>
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
        <header>
        <nav class="navbar">
        <h1>Médiathèque - Dashboard</h1>
    
            <ul class="menu">
                <li><a href="index.php">Accueil</a></li>
                <li><a href="../catalogue.php">Notre catalogue</a></li>
                <li><a href="contact.php">Nous contacter</a></li>
                <li><a href="deconnexion.php">Me deconnecter</a></li>
            </ul>
    
            <div class="burger">
                <div></div>
                <div></div>
                <div></div>
            </div>
        </nav>
    </header>
    

    <main class="main-content">
        <section class="dashboard-intro">
        <h2>Bienvenue, <?php echo $_SESSION['nom'] . ' ' . $_SESSION['prenom']; ?> !</h2>

        </section>

        <?php if ($_SESSION['user_role'] == 'admin'): ?>
            <a href="admin.php" class="btn">Ouvrir Test</a>
        <?php elseif ($_SESSION['user_role'] == 'mediatheque'): ?>
            <a href="gestion_emprunts.php" class="btn">Gérer les emprunts</a>
        <?php endif; ?>

        <?php if ($_SESSION['user_role'] == 'client'): ?>
            <section class="emprunts">
                <h3>Vos livres empruntés :</h3>
                <?php if (count($livres_empruntes) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Titre</th>
                                <th>Auteur</th>
                                <th>Date d'emprunt</th>
                                <th>Date de retour prévue</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($livres_empruntes as $livre): 
                                $date_retour = new DateTime($livre['date_retour']);
                                $aujourdhui = new DateTime();
                                $en_retard = $date_retour < $aujourdhui;
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($livre['titre']); ?></td>
                                    <td><?php echo htmlspecialchars($livre['auteur']); ?></td>
                                    <td><?php echo htmlspecialchars($livre['date_emprunt']); ?></td>
                                    <td><?php echo htmlspecialchars($livre['date_retour']); ?></td>
                                    <td>
                                        <?php if ($en_retard): ?>
                                            <span style="color: red; font-weight: bold;">En retard !</span>
                                        <?php else: ?>
                                            <span style="color: green;">Dans les délais</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Vous n'avez actuellement aucun livre emprunté.</p>
                <?php endif; ?>
            </section>
        <?php endif; ?>

        <section class="messagerie">
    <h3>Messagerie :</h3>
    <?php if (!empty($messages)): ?>
        <ul>
            <?php foreach ($messages as $message): ?>
                <li><?php echo $message; ?></li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Aucun message important pour le moment.</p>
    <?php endif; ?>
</section>

        <section class="actions">
            <h3>Actions rapides :</h3>
            <ul>
                <li><a href="../catalogue.php">Voir le catalogue</a></li>
                <li><a href="contact.php">Contactez-nous</a></li>
                <li><a href="abonnement.php">Gérer mon abonnement</a></li>
            </ul>
        </section>
    </main>

    <footer>
        <p>&copy; 2025 Médiathèque de Montpellier</p>
    </footer>
</body>
</html>
<script src="../js/burger.js"></script>