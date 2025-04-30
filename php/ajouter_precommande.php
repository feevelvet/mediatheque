<?php
require 'db_config.php';
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: ../connexion.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_livre = intval($_POST['id_livre']);
    $email = $_SESSION['email'];
    $date = date('Y-m-d');

    
    $type_livre = isset($_POST['type']) ? strtolower($_POST['type']) : 'livre'; 

    
    $verif = $conn->prepare("SELECT * FROM precommande WHERE id_livre = ? AND email = ? AND est_notifie = 0 AND type = ?");
    $verif->bind_param("iss", $id_livre, $email, $type_livre);
    $verif->execute();
    $result = $verif->get_result();

    if ($result->num_rows === 0) {
        $stmt = $conn->prepare("INSERT INTO precommande (id_livre, email, date_precommande, type) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $id_livre, $email, $date, $type_livre);
        $stmt->execute();
        $stmt->close();
        $_SESSION['message'] = "Vous serez averti lorsque le $type_livre sera disponible.";
    } else {
        $_SESSION['message'] = "Vous avez déjà demandé à être averti.";
    }

    $verif->close();

    
    $redirect_pages = ['livre' => 'livre.php', 'dvd' => 'dvd.php', 'cd' => 'cd.php'];
    $page = isset($redirect_pages[$type_livre]) ? $redirect_pages[$type_livre] : 'index.php';

    header("Location: ../categorie/$page");
    exit;
}
