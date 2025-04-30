<?php



session_start();


if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../connexion.php"); 
    exit();
}


require_once 'db_config.php';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $titre = mysqli_real_escape_string($conn, $_POST['titre']);
    $auteur = mysqli_real_escape_string($conn, $_POST['auteur']);
    $editeur = mysqli_real_escape_string($conn, $_POST['editeur']);
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $statut = mysqli_real_escape_string($conn, $_POST['statut']);

    
    $sql = "INSERT INTO livre (titre, auteur, editeur, type, statut) 
            VALUES ('$titre', '$auteur', '$editeur', '$type', '$statut')";

    if (mysqli_query($conn, $sql)) {
        
        echo "Le livre a été ajouté avec succès.";
    } else {
        
        echo "Erreur: " . $sql . "<br>" . mysqli_error($conn);
    }
}


mysqli_close($conn);
?>

<!-- Formulaire HTML pour ajouter un livre -->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un Livre</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <h1>Médiathèque</h1>
            <ul class="menu">
                <li><a href="../index.php">Accueil</a></li>
                <li><a href="../catalogue.html">Catalogue</a></li>
                <li><a href="../php/contact.php">Contact</a></li>
                <li><a href="../php/deconnexion.php">Se déconnecter</a></li>
            </ul>
        </nav>
    </header>

    <main class="main-content">
        <h2>Ajouter un Livre</h2>
        <form action="ajouter_livre.php" method="POST">
            <label for="titre">Titre</label>
            <input type="text" id="titre" name="titre" required>

            <label for="auteur">Auteur</label>
            <input type="text" id="auteur" name="auteur" required>

            <label for="editeur">Éditeur</label>
            <input type="text" id="editeur" name="editeur" required>

            <label for="type">Type</label>
            <select id="type" name="type" required>
                <option value="livre">Livre</option>
                <option value="CD">CD</option>
                <option value="DVD">DVD</option>
            </select>

            <label for="statut">Statut</label>
            <select id="statut" name="statut" required>
                <option value="disponible">Disponible</option>
                <option value="emprunte">Emprunté</option>
            </select>

            <button type="submit">Ajouter le Livre</button>
        </form>
    </main>

    <footer>
        <p>&copy; 2025 Médiathèque de Montpellier</p>
    </footer>
</body>
</html>
