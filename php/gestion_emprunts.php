<?php
session_start();


if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'mediatheque') {
    header("Location: connexion.php");
    exit();
}


$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "mediatheque";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}


if (isset($_GET['retour']) && is_numeric($_GET['retour']) && isset($_GET['type'])) {
    $id_emprunt = intval($_GET['retour']);
    $type_emprunt = $conn->real_escape_string($_GET['type']);
    $date_retour = date("Y-m-d");

    $update = $conn->prepare("UPDATE emprunt SET retour_effectif = ? WHERE id = ? AND type = ?");
    $update->bind_param("sis", $date_retour, $id_emprunt, $type_emprunt);
    $update->execute();
    $update->close();
}
$type_filter = isset($_GET['type']) ? $_GET['type'] : 'tous';
$show_all = isset($_GET['tous']) ? true : false;


$where = "1=1";


if (!$show_all) {
    $where .= " AND emprunt.retour_effectif IS NULL";
}


if ($type_filter !== 'tous') {
    $type = $conn->real_escape_string($type_filter);
    $where .= " AND emprunt.type = '$type'";
}



$sql = "
    SELECT emprunt.id, 
           CASE 
               WHEN emprunt.type = 'livre' THEN livre.titre
               WHEN emprunt.type = 'cd' THEN cd.titre
               WHEN emprunt.type = 'dvd' THEN dvd.titre
               WHEN emprunt.type = 'titres_periodiques' THEN titres_periodiques.titre
           END AS titre,
           CASE 
               WHEN emprunt.type = 'livre' THEN livre.auteur
               WHEN emprunt.type = 'cd' THEN cd.auteur
               WHEN emprunt.type = 'dvd' THEN dvd.auteur
                WHEN emprunt.type = 'titres_periodiques' THEN titres_periodiques.auteur
           END AS auteur,
           emprunt.date_emprunt,
           emprunt.date_retour,
           emprunt.retour_effectif,
           emprunt.emprunte_par_email,
           emprunt.type
    FROM emprunt
    LEFT JOIN livre ON emprunt.id_livre = livre.id AND emprunt.type = 'livre'
    LEFT JOIN cd ON emprunt.id_livre = cd.id AND emprunt.type = 'cd'
    LEFT JOIN dvd ON emprunt.id_livre = dvd.id AND emprunt.type = 'dvd'
    LEFT JOIN titres_periodiques ON emprunt.id_livre = titres_periodiques.id AND emprunt.type = 'titres_periodiques'
    WHERE $where
    ORDER BY emprunt.date_emprunt DESC
";




$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des emprunts</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <h1>Gestion des emprunts</h1>
        <a href="dashboard.php" class="btn">Retour au Dashboard</a>
    </header>

    <main>
    <div class="filters">
        <strong>Filtrer par type :</strong>
        <a href="?type=tous">Tous</a>
        <a href="?type=livre">Livres</a>
        <a href="?type=cd">CDs</a>
        <a href="?type=dvd">DVDs</a>
        <a href="?type=titres_periodiques">titres_periodiques</a>
        <a href="?tous=1">Afficher aussi les rendus</a>
    </div>
        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Auteur</th>
                        <th>type</th>
                        <th>Email de l'emprunteur</th>
                        <th>Date d'emprunt</th>
                        <th>Date de retour prévue</th>
                        <th>Retour Effectif</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()):
                        $date_retour = new DateTime($row['date_retour']);
                        $aujourdhui = new DateTime();
                        $en_retard = !$row['retour_effectif'] && $date_retour < $aujourdhui;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($row['titre']) ?></td>
                        <td><?= htmlspecialchars($row['auteur']) ?></td>
                        <td><?= htmlspecialchars($row['type']) ?></td>
                        <td><?= htmlspecialchars($row['emprunte_par_email']) ?></td>
                        <td><?= htmlspecialchars($row['date_emprunt']) ?></td>
                        <td><?= htmlspecialchars($row['date_retour']) ?></td>
                        <td><?= $row['retour_effectif'] ? htmlspecialchars($row['retour_effectif']) : 'Non retourné' ?></td>
                        <td>
                            <?php if ($row['retour_effectif']): ?>
                                <span style="color: green;">Retourné</span>
                            <?php elseif ($en_retard): ?>
                                <span style="color: red; font-weight: bold;">En retard</span>
                            <?php else: ?>
                                <span style="color: orange;">En cours</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!$row['retour_effectif']): ?>
                                <a href="gestion_emprunts.php?retour=<?= $row['id'] ?>&type=<?= $row['type'] ?>" class="btn">Retourner</a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Aucun emprunt enregistré.</p>
        <?php endif; ?>
    </main>
</body>
</html>

<?php
$conn->close();
?>
