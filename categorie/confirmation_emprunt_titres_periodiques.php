<?php

session_start();
require_once '../php/db_config.php';
mysqli_set_charset($conn, 'utf8mb4');


$isConnected = isset($_SESSION['user_id']);
$email = $isConnected ? $_SESSION['email'] : '';


$hasLateEmprunt = false;
$nbEmpruntsActifs = 0;
$today = date('Y-m-d');

if ($isConnected) {
    $sql_verif = "
        SELECT * FROM emprunt
        WHERE emprunte_par_email = '$email'
        AND retour_effectif IS NULL
    ";
    $res_verif = mysqli_query($conn, $sql_verif);

    if ($res_verif) {
        while ($row = mysqli_fetch_assoc($res_verif)) {
            $nbEmpruntsActifs++;
            if ($row['date_retour'] < $today) {
                $hasLateEmprunt = true;
            }
        }
    }
}




if ($nbEmpruntsActifs > 4) {
    $disableButton = true;
    $buttonMessage = "Trop d'emprunts actifs";
} elseif ($hasLateEmprunt) {
    $disableButton = true;
    $buttonMessage = "Emprunt en retard détecté";
}else {
    $disableButton = false;
    $buttonMessage = "Emprunter";
}
if (isset($_GET['id_livre'])) {
    $id_livre = mysqli_real_escape_string($conn, $_GET['id_livre']);
    
    
    $sql = "SELECT * FROM titres_periodiques WHERE id = '$id_livre'";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        $titres_periodiques = mysqli_fetch_assoc($result);

        if ($titres_periodiques['statut'] == 'emprunte') {
            $disableButton = true;
            $buttonMessage = "Veuillez nous excuser ce titres_periodiques semble déjà avoir été emprunté !";
        }
    } else {
        echo "titres_periodiques non trouvé.";
        exit;
    }
} else {
    echo "Aucun titres_periodiques sélectionné.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($message_erreur)) {
    $sql_check_emprunt = "SELECT * FROM emprunt WHERE id_livre = '$id_livre' AND retour_effectif IS NULL AND type = 'livre'";

    $result_check_emprunt = mysqli_query($conn, $sql_check_emprunt);

    if (mysqli_num_rows($result_check_emprunt) > 0) {

        header("Location: titres_periodiques.php");
        exit(); 
    }
    $sql_abonne = "SELECT id FROM abonne WHERE email = '$email'";
    $result_abonne = mysqli_query($conn, $sql_abonne);
    
    if (mysqli_num_rows($result_abonne) > 0) {
        $abonne = mysqli_fetch_assoc($result_abonne);
        $id_abonne = $abonne['id'];

        $sql_precommande = "
        SELECT * FROM precommande
        WHERE type = 'titres_periodiques'
        AND id_livre = '$id_livre'
        AND email = '$email'
        AND est_notifie = 0
    ";
    $result_precommande = mysqli_query($conn, $sql_precommande);

    if (mysqli_num_rows($result_precommande) > 0) {
        
        $update_precommande = "
            UPDATE precommande
            SET est_notifie = 1
            WHERE type = 'titres_periodiques'
            AND id_livre = '$id_livre'
            AND email = '$email'
        ";
        mysqli_query($conn, $update_precommande);
    }

        
        $date_emprunt = date('Y-m-d');
        $date_retour = date('Y-m-d', strtotime('+14 days')); 

        
        $sql_emprunt = "
        INSERT INTO emprunt (id_livre, id_abonne, emprunte_par_email, date_emprunt, date_retour, type)
        VALUES ('$id_livre', '$id_abonne', '$email', '$date_emprunt', '$date_retour', 'titres_periodiques')
        ";
        
        if (mysqli_query($conn, $sql_emprunt)) {
            $sql_update = "
                UPDATE titres_periodiques
                SET statut = 'emprunte',
                    emprunte_par_email = '$email',
                    date_retour = '$date_retour'
                WHERE id = '$id_livre'
            ";
            mysqli_query($conn, $sql_update);

            $sql_precommande = "SELECT id FROM precommande WHERE id_livre = '$id_livre' AND email = '$email'";
            $result_precommande = mysqli_query($conn, $sql_precommande);

            if (mysqli_num_rows($result_precommande) > 0) {
                $delete_sql = "DELETE FROM precommande WHERE id_livre = '$id_livre' AND email = '$email'";
                mysqli_query($conn, $delete_sql);
            }

            $message_confirmation = "L'emprunt a été confirmé avec succès.";
        } else {
            echo "Erreur lors de l'emprunt.";
        }
    }
}



mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation d'emprunt</title>
    <link rel="stylesheet" href="../css/confirmation_emprunt.css?v=<?php echo time(); ?>">
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="../index.php">Accueil</a></li>
                <li><a href="../catalogue.php">Catalogue</a></li>
                <li><a href="../contact.php">Contact</a></li>
                <?php if ($isConnected): ?>
                    <li><a href="php/dashboard.php">Dashboard</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main>
        <h1>Confirmation de l'emprunt</h1>

        <div class="livre-details">
            <h2><?php echo htmlspecialchars($titres_periodiques['titre']); ?></h2>
            <?php
            
            $titleWithoutSpaces = strtolower(str_replace(' ', '_', $titres_periodiques['titre']));
            $titleWithoutAccents = preg_replace(
                '/[àáâãäå]/u', 'a',
                preg_replace('/[èéêë]/u', 'e',
                    preg_replace('/[ìíîï]/u', 'i',
                        preg_replace('/[òóôõö]/u', 'o',
                            preg_replace('/[ùúûü]/u', 'u',
                                preg_replace('/[ç]/u', 'c',
                                    preg_replace('/[ñ]/u', 'n',
                                        preg_replace('/[ýÿ]/u', 'y', $titleWithoutSpaces))))))));
            $imageName = $titleWithoutAccents . '.jpeg';

            if (file_exists('../assets/' . $imageName)): ?>
                <img src="../assets/<?php echo $imageName; ?>" alt="<?php echo htmlspecialchars($titres_periodiques['titre']); ?>" width="150" height="225">
            <?php else: ?>
                <span>Aucune image disponible</span>
            <?php endif; ?>

            <p><strong>Auteur:</strong> <?php echo htmlspecialchars($titres_periodiques['auteur']); ?></p>
            <p><strong>Éditeur:</strong> <?php echo htmlspecialchars($titres_periodiques['editeur']); ?></p>
            <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($titres_periodiques['description'])); ?></p>

            
                <p><strong>Numéro:</strong> <?php echo htmlspecialchars($titres_periodiques['numero']); ?></p>
            
            <?php if (isset($message_erreur)): ?>
                <p style="color: red;"><?php echo $message_erreur; ?></p>
            <?php endif; ?>

            <?php if (isset($message_confirmation)): ?>
    <p style="color: green;"><?php echo $message_confirmation; ?></p>
 
    <!-- Redirige vers la bonne page en fonction du type et modifie le texte du bouton -->
    <a href="titres_periodiques.php"><button>Retourner voir les titres_periodiques</button></a>
    <a href="livre.php"><button>Voir les livres</button></a>
        <a href="film.php"><button>Voir les films</button></a>
        <a href="cd.php"><button>Voir les CDs</button></a>

        <?php else: ?>
    <!-- Si aucun message de confirmation, afficher le formulaire d'emprunt -->
    <form action="confirmation_emprunt_titres_periodiques.php?id_livre=<?php echo $titres_periodiques['id']; ?>" method="post">
    <button type="submit" <?php echo $disableButton ? 'disabled style="background-color: grey; cursor: not-allowed;"' : ''; ?>>
        <?php echo $buttonMessage; ?>
    </button>
</form>

<?php endif; ?>


        </div>
    </main>

    <footer>
        <p>&copy; 2025 Médiathèque de Montpellier. Tous droits réservés.</p>
    </footer>
</body>
</html>
