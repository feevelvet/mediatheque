<?php
session_start();
require_once 'db_config.php';


if (!isset($_SESSION['email'])) {
    header('Location: ../connexion.php');
    exit;
}


$id_livre = isset($_POST['id_livre']) ? intval($_POST['id_livre']) : 0;
$email_utilisateur = isset($_POST['email']) ? mysqli_real_escape_string($conn, $_POST['email']) : '';


if ($id_livre === 0 || empty($email_utilisateur)) {
    echo "Données manquantes.";
    exit;
}


$sql_verif = "SELECT statut FROM livre WHERE id = $id_livre";
$result_verif = mysqli_query($conn, $sql_verif);
if (!$result_verif || mysqli_num_rows($result_verif) === 0) {
    echo "Livre introuvable.";
    exit;
}

$row = mysqli_fetch_assoc($result_verif);
if ($row['statut'] === 'emprunte') {
    echo "Ce livre est déjà emprunté.";
    exit;
}


$date_retour = date('Y-m-d', strtotime('+15 days'));


$sql_update = "
    UPDATE livre 
    SET statut = 'emprunte', emprunte_par_email = '$email_utilisateur', date_retour = '$date_retour' 
    WHERE id = $id_livre
";


$sql_insert = "
    INSERT INTO emprunt (id_livre, date_emprunt, date_retour, emprunte_par_email)
    VALUES ($id_livre, NOW(), '$date_retour', '$email_utilisateur')
";

if (mysqli_query($conn, $sql_update) && mysqli_query($conn, $sql_insert)) {
    echo "Livre emprunté avec succès ! Retour prévu le $date_retour.";
    
    echo '<br><br><a href="dashboard.php"><button>Retour au Dashboard</button></a>';
} else {
    echo "Erreur lors de l'emprunt : " . mysqli_error($conn);
}

mysqli_close($conn);
?>
