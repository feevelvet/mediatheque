<?php
session_start();

if (!isset($_SESSION['email']) || $_SESSION['user_role'] != 'admin') {
    echo "Accès refusé. Vous devez être un administrateur pour accéder à cette page.";
    exit();
}

include('db_config.php');


if (isset($_POST['action']) && isset($_POST['email'])) {
    $email = $_POST['email'];
    $action = $_POST['action'];

    $nouveau_statut = ($action == 'valider') ? 'actif' : 'refusé';
    $sql_update = "UPDATE abonne SET statut_abonnement = ? WHERE email = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("ss", $nouveau_statut, $email);

    if ($stmt_update->execute()) {
        echo "Abonnement " . ($action == 'valider' ? "validé" : "refusé") . " avec succès.<br>";
    } else {
        echo "Erreur lors de la mise à jour : " . $stmt_update->error . "<br>";
    }
}


$sql = "SELECT * FROM abonne WHERE statut_abonnement = 'en attente'";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

echo "<h1>Demandes en attente de vérification</h1>";

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $nom = htmlspecialchars($row['nom']);
        $prenom = htmlspecialchars($row['prenom']);
        $email = htmlspecialchars($row['email']);
        $date_naissance = htmlspecialchars($row['date_naissance']);
        $type_abonnement = htmlspecialchars($row['type_abonnement']);
        $date_naissance_obj = new DateTime($date_naissance);
        $aujourdhui = new DateTime();
        $age = $aujourdhui->diff($date_naissance_obj)->y;

        echo "<div style='border: 1px solid #ccc; padding: 10px; margin-bottom: 15px;'>";
        echo "<h2>$nom $prenom</h2>";
        echo "<p><strong>Email:</strong> $email</p>";
        echo "<p><strong>Date de naissance:</strong> $date_naissance</p>";
        echo "<p><strong>Âge attendu :</strong> $age ans</p>"; 
        echo "<p><strong>Type d'abonnement:</strong> $type_abonnement</p>";

        if (!empty($row['justificatif_blob'])) {
            $justificatif = $row['justificatif_blob'];

            
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->buffer($justificatif);
            $base64 = base64_encode($justificatif);

            echo "<p><strong>Justificatif :</strong></p>";

            if (strpos($mime, 'image/') === 0) {
                
                echo "<img src='data:$mime;base64,$base64' alt='Justificatif' style='max-width:300px; max-height:400px;'><br>";
            } elseif ($mime === 'application/pdf') {
                
                echo "<iframe src='data:$mime;base64,$base64' width='100%' height='500px' style='border:1px solid #aaa;'></iframe><br>";
            } else {
                
                echo "<a href='data:$mime;base64,$base64' download='justificatif'>📄 Télécharger le justificatif ($mime)</a><br>";
            }
        } else {
            echo "<p><em>Aucun justificatif fourni.</em></p>";
        }

        echo "<form method='POST' action=''>";
        echo "<input type='hidden' name='email' value='$email'>";
        echo "<button type='submit' name='action' value='valider'>✅ Valider</button> ";
        echo "<button type='submit' name='action' value='refuser'>❌ Refuser</button>";
        echo "</form>";
        echo "</div>";
    }
} else {
    echo "<p>Aucune demande en attente.</p>";
}

$conn->close();
?>
