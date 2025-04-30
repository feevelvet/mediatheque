<?php
session_start();
require_once 'db_config.php';

/* Vérifie si l'utilisateur est connecté et possède les droits nécessaires
if (!isset($_SESSION['email']) || $_SESSION['user_role'] != 'admin') {
    echo "Accès interdit. Vous devez être connecté en tant qu'administrateur.";
    exit;
}*/


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modifier_abonnement'])) {
    $abonne_id = $_POST['abonne_id'];
    $type_abonnement = $_POST['type_abonnement'];
    $statut_abonnement = $_POST['statut_abonnement'];
    $date_expiration = $_POST['date_expiration'];

    
    $sql = "UPDATE abonne SET type_abonnement = ?, statut_abonnement = ?, date_expiration = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssss", $type_abonnement, $statut_abonnement, $date_expiration, $abonne_id);

    if (mysqli_stmt_execute($stmt)) {
        echo "Les informations de l'abonné ont été mises à jour avec succès.";
    } else {
        echo "Erreur lors de la mise à jour des informations : " . mysqli_error($conn);
    }

    mysqli_stmt_close($stmt);
}


$sql = "SELECT * FROM abonne";
$abonne_result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/gestion.css?v=<?php echo time(); ?>">
    <title>Gestion des Abonnements - Médiathèque</title>
</head>
<body>
    <header>
        <h1>Gestion des Abonnements</h1>
        <nav>
            <ul>
                <li><a href="dashboard.php">Retour au Dashboard</a></li>
                <li><a href="admin.php">Retour à la gestion des livres</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h2>Liste des Abonnés</h2>
        <table border="1">
            <tr>
                <th>Prénom et Nom</th>
                <th>Email</th>
                <th>Type d'abonnement</th>
                <th>Statut</th>
                <th>Date d'expiration</th>
                <th>Modifier</th>
            </tr>
            <?php while ($abonne_row = mysqli_fetch_assoc($abonne_result)) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($abonne_row['prenom'] ?? '') . ' ' . htmlspecialchars($abonne_row['nom'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($abonne_row['email'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($abonne_row['type_abonnement'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($abonne_row['statut_abonnement'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($abonne_row['date_expiration'] ?? ''); ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="abonne_id" value="<?php echo $abonne_row['id']; ?>">
                            <select name="type_abonnement" required>
                                <option value="gratuit" <?php echo $abonne_row['type_abonnement'] == 'gratuit' ? 'selected' : ''; ?>>Gratuit</option>
                                <option value="mensuel" <?php echo $abonne_row['type_abonnement'] == 'mensuel' ? 'selected' : ''; ?>>Mensuel - 10,50 €</option>
                                <option value="annuel" <?php echo $abonne_row['type_abonnement'] == 'annuel' ? 'selected' : ''; ?>>Annuel - 22 €</option>
                            </select>
                            <select name="statut_abonnement" required>
                                <option value="actif" <?php echo $abonne_row['statut_abonnement'] == 'actif' ? 'selected' : ''; ?>>Actif</option>
                                <option value="suspendu" <?php echo $abonne_row['statut_abonnement'] == 'suspendu' ? 'selected' : ''; ?>>Suspendu</option>
                                <option value="expiré" <?php echo $abonne_row['statut_abonnement'] == 'expiré' ? 'selected' : ''; ?>>Expiré</option>
                                <option value="inactif" <?php echo $abonne_row['statut_abonnement'] == 'inactif' ? 'selected' : ''; ?>>inactif</option>
                            </select>
                            <input type="date" name="date_expiration" value="<?php echo htmlspecialchars($abonne_row['date_expiration'] ?? ''); ?>" required>
                            <button type="submit" name="modifier_abonnement">Modifier</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </main>

    <footer>
        <p>&copy; 2025 Médiathèque de Montpellier. Tous droits réservés.</p>
    </footer>
</body>
</html>

<?php mysqli_close($conn); ?>
