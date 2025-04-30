<?php

$host = 'localhost';
$username = 'root';
$password = 'root';
$database = 'mediatheque';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


function afficherResultat($message, $success = true) {
    if ($success) {
        echo "<p style='color: green;'>$message</p>";
    } else {
        echo "<p style='color: red;'>$message</p>";
    }
}


function creerUtilisateur($email, $nom, $prenom) {
    global $conn;

    
    $sqlVerif = "SELECT id FROM abonne WHERE email = ?";
    $stmtVerif = $conn->prepare($sqlVerif);
    $stmtVerif->bind_param("s", $email);
    $stmtVerif->execute();
    $resultVerif = $stmtVerif->get_result();

    if ($resultVerif->num_rows == 0) {
        
        $sql = "INSERT INTO abonne (email, nom, prenom) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $email, $nom, $prenom);

        if ($stmt->execute()) {
            afficherResultat("Utilisateur $email créé et abonné avec succès.");
        } else {
            afficherResultat("Erreur lors de la création de l'utilisateur $email.", false);
        }
    } else {
        afficherResultat("L'utilisateur $email existe déjà.", false);
    }
}


function creerLivre($titre, $auteur) {
    global $conn;

    
    $sqlVerifLivre = "SELECT id FROM livre WHERE titre = ?";
    $stmtVerifLivre = $conn->prepare($sqlVerifLivre);
    $stmtVerifLivre->bind_param("s", $titre);
    $stmtVerifLivre->execute();
    $resultVerifLivre = $stmtVerifLivre->get_result();

    if ($resultVerifLivre->num_rows == 0) {
        
        $sql = "INSERT INTO livre (titre, auteur) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $titre, $auteur);

        if ($stmt->execute()) {
            afficherResultat("Livre '$titre' créé avec succès.");
        } else {
            afficherResultat("Erreur lors de la création du livre '$titre'.", false);
        }
    } else {
        afficherResultat("Le livre '$titre' existe déjà.", false);
    }
}


function emprunterLivre($email, $titre) {
    global $conn;

    
    $sqlUser = "SELECT id FROM abonne WHERE email = ?";
    $stmtUser = $conn->prepare($sqlUser);
    $stmtUser->bind_param("s", $email);
    $stmtUser->execute();
    $resultUser = $stmtUser->get_result();
    $user = $resultUser->fetch_assoc();

    if ($user) {
        $userId = $user['id'];

        
        $sqlLivre = "SELECT id, statut FROM livre WHERE titre = ?";
        $stmtLivre = $conn->prepare($sqlLivre);
        $stmtLivre->bind_param("s", $titre);
        $stmtLivre->execute();
        $resultLivre = $stmtLivre->get_result();
        $livre = $resultLivre->fetch_assoc();

        if ($livre) {
            if ($livre['statut'] != 'emprunte') { 
                $livreId = $livre['id'];

                
                $sqlEmprunt = "SELECT * FROM emprunt WHERE id_livre = ? AND emprunte_par_email = ?";
                $stmtEmprunt = $conn->prepare($sqlEmprunt);
                $stmtEmprunt->bind_param("is", $livreId, $email);
                $stmtEmprunt->execute();
                $resultEmprunt = $stmtEmprunt->get_result();

                if ($resultEmprunt->num_rows == 0) {
                    
                    $dateEmprunt = date('Y-m-d');
                    $sqlEmpruntInsert = "INSERT INTO emprunt (id_abonne, id_livre, emprunte_par_email, date_emprunt) 
                                         VALUES (?, ?, ?, ?)";
                    $stmtEmpruntInsert = $conn->prepare($sqlEmpruntInsert);
                    $stmtEmpruntInsert->bind_param("iiss", $userId, $livreId, $email, $dateEmprunt);
                    if ($stmtEmpruntInsert->execute()) {
                        
                        $sqlUpdateLivre = "UPDATE livre SET statut = 'emprunte', emprunte_par_email = ? WHERE id = ?";
                        $stmtUpdateLivre = $conn->prepare($sqlUpdateLivre);
                        $stmtUpdateLivre->bind_param("si", $email, $livreId);
                        $stmtUpdateLivre->execute();

                        afficherResultat("Le livre '$titre' a été emprunté avec succès par $email.");
                    } else {
                        afficherResultat("Erreur lors de l'emprunt du livre '$titre'.", false);
                    }
                } else {
                    afficherResultat("Le livre '$titre' a déjà été emprunté par $email.", false);
                }
            } else {
                afficherResultat("Le livre '$titre' est déjà emprunté et n'est pas disponible.", false);
            }
        } else {
            afficherResultat("Le livre '$titre' n'existe pas.", false);
        }
    } else {
        afficherResultat("L'utilisateur $email n'existe pas.", false);
    }
}



creerUtilisateur('jean.dupont@example.com', 'Dupont', 'Jean');


creerLivre('The Matrix', 'Lana Wachowski');


emprunterLivre('jean.dupont@example.com', 'The Matrix');



?>
