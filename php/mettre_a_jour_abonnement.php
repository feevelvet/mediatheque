<?php
session_start();

if (!isset($_SESSION['email'])) {
    echo "<div class='alert'>Vous devez être connecté.</div>";
    exit();
}?>

<?php
function afficherMessageEtRedirection($message, $succes = true, $destination = "accueil.php", $secondes = 5) {
    $classe = $succes ? 'message-success' : 'message-error';
    $emoji = $succes ? '✅' : '❌';

    echo "
    <!DOCTYPE html>
    <html lang='fr'>
    <head>
    
        <meta charset='UTF-8'>
        <title>Redirection...</title>
        <link rel='stylesheet' href='../css/gestion.css'>
    </head>
    <body>
        <div class='message-box $classe'>
            $emoji $message<br>
            Redirection dans <span class='countdown' id='countdown' data-redirect='$destination'>$secondes</span> secondes...
        </div>

        <script src='../js/redirection.js'></script>
    </body>
    </html>";
    exit();
}
?>
<?php

$email = $_SESSION['email'];
include('db_config.php');
$sqlUser = "SELECT id FROM utilisateurs WHERE email = ?";
$stmtUser = $conn->prepare($sqlUser);
$stmtUser->bind_param("s", $email);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$user = $resultUser->fetch_assoc();

if (!$user) {
    afficherMessageEtRedirection("Utilisateur non trouvé dans la base.", false);
}
$id_utilisateur = $user['id'];
$email = $_SESSION['email'];
if (isset($_POST['final_submit'])) {
    $type_abonnement = $_POST['type_abonnement'];
    $pdfFile = isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK ? $_FILES['pdf_file'] : null;

    
    if ($pdfFile !== null) {
        $fileData = file_get_contents($pdfFile['tmp_name']);
        $fileName = $pdfFile['name'];
        $fileType = $pdfFile['type'];
    } else {
        $fileData = null; 
    }

    
    $sql = "SELECT * FROM abonne WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $abonne = $result->fetch_assoc();

    $today = new DateTime();
    $date_inscription = $today->format('Y-m-d');
    $dernier_renouvellement = $today->format('Y-m-d');
    $date_expiration = (clone $today)->modify('+1 year')->format('Y-m-d');

    if ($abonne) {
        
        $date_naissance = new DateTime($abonne['date_naissance']);
        $age = $date_naissance->diff($today)->y;

        $status_abonnement = ($age < 18 || $statut_abonnement == 'refusé') ? 'en attente' : 'actif';

        $updateSql = "UPDATE abonne SET type_abonnement = ?, statut_abonnement = ?, justificatif_blob = ?, date_expiration = ?, dernier_renouvellement = ? WHERE email = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("ssssss", $type_abonnement, $status_abonnement, $fileData, $date_expiration, $dernier_renouvellement, $email);

        if ($stmt->execute()) {
            afficherMessageEtRedirection("Abonnement mis à jour avec succès.", true, "abonnement.php", 5);

        } else {
            afficherMessageEtRedirection("Erreur lors de la mise à jour.", false, "yfyugiu.php", 5);

        }
    } else {
        
        $nom = $_POST['nom'] ?? '';
        $prenom = $_POST['prenom'] ?? '';
        $date_naissance = $_POST['date_naissance'] ?? '2000-01-01';
        $dateN = new DateTime($date_naissance);
        $age = $dateN->diff($today)->y;

        $statut_abonnement = ($age < 18) ? 'en attente' : 'actif';

        $insertSql = "INSERT INTO abonne (id_utilisateur, nom, prenom, email, date_naissance, date_inscription, type_abonnement, statut_abonnement, date_expiration, dernier_renouvellement, justificatif_blob)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insertSql);
            $stmt->bind_param("issssssssss", $id_utilisateur, $nom, $prenom, $email, $date_naissance, $date_inscription, $type_abonnement, $statut_abonnement, $date_expiration, $dernier_renouvellement, $fileData);


        if ($stmt->execute()) {
            afficherMessageEtRedirection("Abonnement mis à jour avec succès.", true, "abonnement.php", 5);
        } else {
            afficherMessageEtRedirection("Erreur lors de la mise à jour.", false, "abonnement.php", 5);
        }
    }
}
echo "Statut actuel : " . $statut_abonnement;

?>
