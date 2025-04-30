<?php
session_start();
require_once 'db_config.php';


if (!isset($_SESSION['email']) || $_SESSION['user_role'] != 'admin') {
    echo "Accès interdit. Vous devez être connecté en tant qu'administrateur.";
    exit;
}


function getTableByType($type) {
    $type = strtolower($type);
    $tables = ['livre', 'cd', 'dvd','titres_periodiques'];
    return in_array($type, $tables) ? $type : null;
}


function remettreAZero($conn, $id_media, $type) {
    $table = getTableByType($type);
    if (!$table) {
        echo "Type de média invalide.";
        return;
    }


    $sql_reset = "UPDATE $table SET statut = 'disponible', emprunte_par_email = NULL, date_retour = NULL WHERE id = $id_media";
    if (mysqli_query($conn, $sql_reset)) {
        
        $sql_delete = "DELETE FROM emprunt WHERE id_livre = $id_media AND type = '$type'";
        if (mysqli_query($conn, $sql_delete)) {
            echo ucfirst($type) . " réinitialisé avec succès.";
        } else {
            echo "Erreur lors de la suppression de l'emprunt : " . mysqli_error($conn);
        }
    } else {
        echo "Erreur lors de la mise à jour du statut de $type : " . mysqli_error($conn);
    }
}

function emprunterMedia($conn, $id_media, $email_utilisateur, $type) {
    $table = getTableByType($type);
    if (!$table) {
        echo "Type de média invalide.";
        return;
    }

    $date_retour = date('Y-m-d', strtotime('+15 days'));

    $sql_update = "UPDATE $table 
                   SET statut = 'emprunte', emprunte_par_email = '$email_utilisateur', date_retour = '$date_retour' 
                   WHERE id = $id_media";
    if (mysqli_query($conn, $sql_update)) {
        
        $sql_insert = "INSERT INTO emprunt (id_livre, type, emprunte_par_email, date_retour) 
                       VALUES ($id_media, '$type', '$email_utilisateur', '$date_retour')";
        if (mysqli_query($conn, $sql_insert)) {
            echo ucfirst($type) . " emprunté avec succès ! Retour prévu le $date_retour.";
        } else {
            echo "Erreur lors de l'ajout dans la table emprunt : " . mysqli_error($conn);
        }
    } else {
        echo "Erreur lors de la mise à jour du statut de $type : " . mysqli_error($conn);
    }
}

function retirerMedia($conn, $id_media, $type) {
    $table = getTableByType($type);
    if (!$table) {
        echo "Type de média invalide.";
        return;
    }


    $sql_delete_media = "DELETE FROM $table WHERE id = $id_media";
    if (mysqli_query($conn, $sql_delete_media)) {

        $sql_delete_emprunt = "DELETE FROM emprunt WHERE id_livre = $id_media AND type = '$type'";
        mysqli_query($conn, $sql_delete_emprunt); 
        echo ucfirst($type) . " retiré avec succès.";
    } else {
        echo "Erreur lors de la suppression de $type : " . mysqli_error($conn);
    }
}



function retourEffectifMedia($conn, $id_media, $type) {
    $table = getTableByType($type);
    if (!$table) {
        echo "Type de média invalide.";
        return;
    }

    $date_retour_effectif = date('Y-m-d');


    $stmt = mysqli_prepare($conn, "UPDATE emprunt SET retour_effectif = ? WHERE id_livre = ? AND type = ?");
    mysqli_stmt_bind_param($stmt, "sis", $date_retour_effectif, $id_media, $type);

    if (mysqli_stmt_execute($stmt)) {

        $sql = "UPDATE $table 
                SET statut = 'disponible', emprunte_par_email = NULL, date_retour = NULL 
                WHERE id = $id_media";
        if (mysqli_query($conn, $sql)) {
            echo ucfirst($type) . " ID $id_media retourné avec succès.";
        } else {
            echo "Erreur lors de la mise à jour du $type : " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "Erreur lors de la mise à jour de l'emprunt : " . mysqli_error($conn);
    }
}
function modifierDateRetourMedia($conn, $id_media, $type, $date_retour) {
    if (empty($date_retour)) {
        echo "La date de retour est invalide.";
        return;
    }

    $table = getTableByType($type);
    if (!$table) {
        echo "Type de média invalide.";
        return;
    }


    $stmt = mysqli_prepare($conn, "UPDATE emprunt SET date_retour = ? WHERE id_livre = ? AND type = ?");
    mysqli_stmt_bind_param($stmt, "sis", $date_retour, $id_media, $type);

    if (mysqli_stmt_execute($stmt)) {

        $stmt_table = mysqli_prepare($conn, "UPDATE $table SET date_retour = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt_table, "si", $date_retour, $id_media);

        if (mysqli_stmt_execute($stmt_table)) {
            echo "Date de retour mise à jour avec succès pour le " . $type . ".";
        } else {
            echo "Erreur lors de la mise à jour de la table $type : " . mysqli_error($conn);
        }

        mysqli_stmt_close($stmt_table);
    } else {
        echo "Erreur lors de la mise à jour de la table emprunt : " . mysqli_error($conn);
    }

    mysqli_stmt_close($stmt);
}



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? 'livre'; 
    $id_media = intval($_POST['id_livre']); 

    if (isset($_POST['remettre_a_zero'])) {
        remettreAZero($conn, $id_media, $type);
    } elseif (isset($_POST['emprunter'])) {
        $email_utilisateur = 'test@example.com'; 
        emprunterMedia($conn, $id_media, $email_utilisateur, $type);
    } elseif (isset($_POST['retirer'])) {
        retirerMedia($conn, $id_media, $type);
    } elseif (isset($_POST['modifier_date_retour'])) {
        $date_retour = $_POST['date_retour'];
        modifierDateRetourMedia($conn, $id_media, $type, $date_retour);
    } elseif (isset($_POST['retour_effectif'])) {
        retourEffectifMedia($conn, $id_media, $type);
    }
}


?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/gestion.css?v=<?php echo time(); ?>">
    <title>Gestion des Médias - Médiathèque</title>
</head>
<body>
    <header>
        <h1>Espace administrateur</h1>
        <nav>
            <ul>
                <li><a href="dashboard.php">Retour au Dashboard</a></li>
                <li><a href="gestion_abonnements.php"><button>Gestion des Abonnements</button></a></li>
                <li><a href="verifier_demandes.php"><button>Gestion des demandes</button></a></li>
            </ul>
        </nav>
    </header>

    <main>
    <h2>Liste des Médias</h2>

    <?php
    $types = ['livre', 'cd', 'dvd','titres_periodiques'];

    foreach ($types as $type) {
        echo "<h3>" . ucfirst($type) . "s</h3>";

        $sql = "SELECT * FROM $type";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            echo "<table border='1'>
                    <tr>
                        <th>ID</th>
                        <th>Titre</th>
                        <th>Auteur</th>
                        <th>Éditeur</th>
                        <th>Statut</th>
                        <th>Emprunté par</th>
                        <th>Date de retour</th>
                        <th>Action</th>
                    </tr>";

            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>
                        <td>" . $row['id'] . "</td>
                        <td>" . htmlspecialchars($row['titre']) . "</td>
                        <td>" . htmlspecialchars($row['auteur']) . "</td>
                        <td>" . htmlspecialchars($row['editeur']) . "</td>
                        <td>" . ($row['statut'] === 'emprunte' ? 'Emprunté' : 'Disponible') . "</td>
                        <td>" . ($row['emprunte_par_email'] ? htmlspecialchars($row['emprunte_par_email']) : '-') . "</td>
                        <td>" . ($row['date_retour'] ? htmlspecialchars($row['date_retour']) : '-') . "</td>
                        <td>
                            <form action='admin.php' method='POST'>
                                <input type='hidden' name='id_livre' value='" . $row['id'] . "'>
                                <input type='hidden' name='type' value='" . $type . "'>

                                <button type='submit' name='remettre_a_zero'>supprimer</button>
                                <button type='submit' name='emprunter'>Emprunter</button>
                                <button type='submit' name='retirer'>Retirer</button>
                                <button type='submit' name='retour_effectif'>Retour Effectif</button>

                                <br>
                                <label>Modifier la date de retour :</label>
                                <input type='date' name='date_retour' value='" . ($row['date_retour'] ? $row['date_retour'] : '') . "'>
                                <button type='submit' name='modifier_date_retour'>Modifier</button>
                            </form>
                        </td>
                    </tr>";
            }

            echo "</table><br>";
        } else {
            echo "<p>Aucun(e) " . $type . " trouvé(e).</p>";
        }
    }
    ?>
</main>


    <footer>
        <p>&copy; 2025 Médiathèque de Montpellier. Tous droits réservés.</p>
    </footer>

</body>
</html>

<?php mysqli_close($conn); ?>
