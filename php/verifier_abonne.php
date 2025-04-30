<?php

session_start();
require_once 'php/db_config.php';


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
} else {
    echo json_encode(["status" => "abonne", "message" => "Vous êtes bien abonné"]);
}
?>
