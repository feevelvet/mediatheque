<?php

require_once "db_config.php";

header('Content-Type: application/json');

if (!isset($_SESSION['email'])) {
    echo json_encode(["status" => "error", "message" => "Utilisateur non connecté"]);
    exit;
}

$email = $_SESSION['email'];


$sql = "SELECT * FROM abonne WHERE email = ? AND statut_abonnement = 'actif' AND date_expiration >= CURDATE()";
$stmt = $conn->prepare($sql);
$stmt->execute([$email]);
$abonne = $stmt->fetch();

if (!$abonne) {
    echo json_encode(["status" => "non_abonne", "message" => "Vous devez être abonné pour emprunter"]);
    exit;
}


$sql = "SELECT COUNT(*) FROM emprunts WHERE emprunte_par_email = ? AND date_retour IS NULL";
$stmt = $conn->prepare($sql);
$stmt->execute([$email]);
$nb_emprunts = $stmt->fetchColumn();

if ($nb_emprunts >= 4) {
    echo json_encode(["status" => "limite", "message" => "Vous avez atteint la limite de 4 emprunts"]);
    exit;
}


$sql = "SELECT COUNT(*) FROM emprunts WHERE emprunte_par_email = ? AND date_retour IS NULL AND date_retour_prevue < CURDATE()";
$stmt = $conn->prepare($sql);
$stmt->execute([$email]);
$retards = $stmt->fetchColumn();

if ($retards > 0) {
    echo json_encode(["status" => "retard", "message" => "Vous avez un ou plusieurs emprunts en retard"]);
    exit;
}


echo json_encode(["status" => "ok", "message" => "Emprunt autorisé"]);
?>
