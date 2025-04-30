<?php
session_start();
require_once 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $motdepasse = trim($_POST['motdepasse']);

    if (empty($email) || empty($motdepasse)) {
        echo "Veuillez remplir tous les champs.";
        exit;
    }

    $sql = "SELECT * FROM utilisateurs WHERE email = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($motdepasse, $user['motdepasse'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['prenom'] = $user['prenom'];
            $_SESSION['nom'] = $user['nom'];
            if ($user['role'] === 'mediatheque') {
                header("Location: gestion_emprunts.php");
                exit;
            } elseif ($user['role'] === 'admin') {
                header("Location: admin.php");
                exit;
            }
             
    
            header("Location: dashboard.php");
            exit;
        } else {
            header("Location: ../connexion.php?erreur=1");
    exit;
        }
        $stmt->close();
    }
}
?>
