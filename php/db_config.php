<?php
// db_config.php

$host = 'localhost';
$username = 'root'; // Assurez-vous que votre utilisateur est correct
$password = 'root'; // Le mot de passe de votre base de données
$dbname = 'mediatheque';

// Créer la connexion
$conn = new mysqli($host, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connexion échouée: " . $conn->connect_error);
}

// Vérifier et mettre à jour le statut des abonnements expirés
$today = date('Y-m-d'); // Date actuelle
$sql_update_abonnements = "
    UPDATE abonne
    SET statut_abonnement = 'inactif'
    WHERE date_expiration < '$today' AND statut_abonnement != 'inactif'
";

if ($conn->query($sql_update_abonnements) === TRUE) {
    // Si la mise à jour a réussi
    // Vous pouvez éventuellement ajouter un log ou une notification pour savoir que la mise à jour a été effectuée.
} else {
    // Si une erreur se produit
    echo "Erreur lors de la mise à jour des abonnements : " . $conn->error;
}
?>
