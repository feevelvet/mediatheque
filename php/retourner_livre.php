<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: connexion.php");
    exit();
}

if (!isset($_POST['titre'])) {
    die("Titre du livre manquant.");
}


$servername = "localhost";
$username = "root";
$password = "root"; 
$dbname = "mediatheque";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

$titre = $_POST['titre'];
$email = $_SESSION['email'];


$sql_livre = "SELECT id FROM livre WHERE titre = ? AND emprunte_par_email = ? AND statut = 'emprunte' LIMIT 1";
$stmt_livre = $conn->prepare($sql_livre);
$stmt_livre->bind_param("ss", $titre, $email);
$stmt_livre->execute();
$result = $stmt_livre->get_result();

if ($result->num_rows === 0) {
    die("Aucun emprunt trouvé pour ce livre par cet utilisateur.");
}

$livre = $result->fetch_assoc();
$id_livre = $livre['id'];


$sql_update_emprunt = "UPDATE emprunt SET retour_effectif = NOW() WHERE id_livre = ? AND emprunte_par_email = ? AND retour_effectif IS NULL";
$stmt_update_emprunt = $conn->prepare($sql_update_emprunt);
$stmt_update_emprunt->bind_param("is", $id_livre, $email);
$stmt_update_emprunt->execute();


$sql_update_livre = "UPDATE livre SET statut = 'disponible', date_retour = NULL, emprunte_par_email = NULL WHERE id = ?";
$stmt_update_livre = $conn->prepare($sql_update_livre);
$stmt_update_livre->bind_param("i", $id_livre);
$stmt_update_livre->execute();

$stmt_livre->close();
$stmt_update_emprunt->close();
$stmt_update_livre->close();
$conn->close();

header("Location: dashboard.php");
exit();
