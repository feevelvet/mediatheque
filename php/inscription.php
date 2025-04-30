<?php



require_once 'db_config.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $motdepasse = password_hash($_POST['motdepasse'], PASSWORD_DEFAULT); 
    $role = 'client'; 

    
    $sql_check_email = "SELECT id FROM utilisateurs WHERE email = ?";
    if ($stmt_check = $conn->prepare($sql_check_email)) {
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $stmt_check->store_result();
        
        
        if ($stmt_check->num_rows > 0) {
            
            header("Location: ../inscription.php?erreur=email_existe");
            exit(); 
        }
        $stmt_check->close();
    }

    
    $sql = "INSERT INTO utilisateurs (nom, prenom, email, motdepasse, role) VALUES (?, ?, ?, ?, ?)";
    if ($stmt = $conn->prepare($sql)) {
        
        $stmt->bind_param("sssss", $nom, $prenom, $email, $motdepasse, $role);
        
        
        if ($stmt->execute()) {
            echo "Inscription réussie!";
            header("Location: ../index.php"); 
            exit(); 
        } else {
            
            header("Location: ../connexion.php?erreur=inscription");
            exit();
        }
        
        $stmt->close();
    } else {
        
        header("Location: ../connexion.php?erreur=preparation_sql");
        exit();
    }
}


$conn->close();
?>
