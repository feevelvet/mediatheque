<?php
$host = 'localhost';
$username = 'root';
$password = 'root';
$database = 'mediatheque';

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("❌ Connexion échouée : " . $conn->connect_error);
}
echo "✅ Connexion réussie.<br><br>";


$email_test = "jean.dupont@test.com";
$conn->query("DELETE FROM emprunt WHERE emprunte_par_email = '$email_test'");
$conn->query("DELETE FROM abonne WHERE email = '$email_test'");
$conn->query("DELETE FROM utilisateurs WHERE email = '$email_test'");
$conn->query("DELETE FROM livre WHERE titre = 'Livre de Test'");


$sql_user = "INSERT INTO utilisateurs (nom, prenom, email, motdepasse, role)
VALUES ('Dupont', 'Jean', '$email_test', 'password_test', 'client')";
if ($conn->query($sql_user)) {
    $id_utilisateur = $conn->insert_id;
    echo "✅ Utilisateur créé avec ID : $id_utilisateur<br>";
} else {
    die("❌ Erreur utilisateur : " . $conn->error);
}


$sql_abonne = "INSERT INTO abonne (nom, prenom, email, date_naissance, date_inscription,
type_abonnement, statut_abonnement, date_expiration, dernier_renouvellement, id_utilisateur)
VALUES ('Dupont', 'Jean', '$email_test', '2000-01-01', CURDATE(), 'annuel', 'actif',
DATE_ADD(CURDATE(), INTERVAL 1 YEAR), CURDATE(), $id_utilisateur)";
if ($conn->query($sql_abonne)) {
    $id_abonne = $conn->insert_id;
    echo "✅ Abonné créé avec ID : $id_abonne<br>";
} else {
    die("❌ Erreur abonné : " . $conn->error);
}


$sql_livre = "INSERT INTO livre (titre, auteur) VALUES ('Livre de Test', 'Auteur Test')";
if ($conn->query($sql_livre)) {
    $id_livre = $conn->insert_id;
    echo "✅ Livre ajouté avec ID : $id_livre<br>";
} else {
    die("❌ Erreur livre : " . $conn->error);
}
$date_emprunt = date('Y-m-d');
$date_retour = date('Y-m-d', strtotime('+1 month'));

$sql_emprunt = "INSERT INTO emprunt (id_abonne, id_livre, date_emprunt, date_retour, retour_effectif, emprunte_par_email, type)
VALUES ($id_abonne, $id_livre, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 15 DAY), NULL, '$email_test', 'livre')";
if ($conn->query($sql_emprunt)) {
    echo "✅ Livre emprunté avec succès.<br>";
} else {
    die("❌ Erreur emprunt : " . $conn->error);
}
$sql_update_livre = "UPDATE livre
SET statut = 'emprunte', emprunte_par_email = '$email_test', date_retour = '$date_retour'
WHERE id = $id_livre";
if ($conn->query($sql_update_livre)) {
    echo "✅ Livre mis à jour : statut = emprunté, emprunte_par_email = $email_test<br>";
} else {
    echo "❌ Erreur mise à jour livre : " . $conn->error;
}

/* 5. Suppression des données créées
$conn->query("DELETE FROM emprunt WHERE emprunte_par_email = '$email_test'");
$conn->query("DELETE FROM livre WHERE id = $id_livre");
$conn->query("DELETE FROM abonne WHERE id = $id_abonne");
$conn->query("DELETE FROM utilisateurs WHERE id = $id_utilisateur");
*/

echo "<br>🧹 Données de test supprimées avec succès.";

$conn->close();
?>
