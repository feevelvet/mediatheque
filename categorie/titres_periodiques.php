<?php

session_start();
require_once '../php/db_config.php';


$isConnected = isset($_SESSION['user_id']);
$email = $isConnected ? $_SESSION['email'] : '';  


$tri = isset($_GET['tri']) ? mysqli_real_escape_string($conn, $_GET['tri']) : 'titre';
$statut = isset($_GET['statut']) ? mysqli_real_escape_string($conn, $_GET['statut']) : '';


$colonnes_valides = ['titre', 'auteur', 'editeur',];
if (!in_array($tri, $colonnes_valides)) {
    $tri = 'titre';
}


switch ($statut) {
    case 'disponible':
        $titreTri = 'Disponibles';
        break;
    case 'emprunte':
        $titreTri = 'Empruntés';
        break;
    default:
        $titreTri = 'Tous';
}
$titres_periodiques_EmpruntesParMoi = [];

if ($isConnected) {
    $sql_emprunt_perso = "
        SELECT id_livre FROM emprunt 
        WHERE emprunte_par_email = '$email'
        AND retour_effectif IS NULL
    ";
    $res_emprunt_perso = mysqli_query($conn, $sql_emprunt_perso);
    while ($row = mysqli_fetch_assoc($res_emprunt_perso)) {
        $titres_periodiques_EmpruntesParMoi[] = $row['id_livre'];
    }
}

$isAbonne = false;
if ($isConnected) {
    $sql_abonne = "
        SELECT * FROM abonne
        WHERE email = '$email' AND statut_abonnement = 'actif' AND date_expiration >= CURDATE()
    ";
    $res_abonne = mysqli_query($conn, $sql_abonne);
    if (mysqli_num_rows($res_abonne) > 0) {
        $isAbonne = true;
    }
}


$hasLateEmprunt = false;
$nbEmpruntsActifs = 0;
$today = date('Y-m-d');

if ($isConnected) {
    $sql_verif = "
        SELECT * FROM emprunt
        WHERE emprunte_par_email = '$email'
        AND retour_effectif IS NULL
    ";
    $res_verif = mysqli_query($conn, $sql_verif);

    if ($res_verif) {
        while ($row = mysqli_fetch_assoc($res_verif)) {
            $nbEmpruntsActifs++;
            if ($row['date_retour'] < $today) {
                $hasLateEmprunt = true;
            }
        }
    }
}


$sql = "SELECT * FROM titres_periodiques  ";
if (!empty($statut)) {
    $sql .= " AND statut = '$statut'";
}
$sql .= " ORDER BY $tri";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catalogue - Médiathèque</title>
    <link rel="stylesheet" href="../css/categorie.css?v=<?php echo time(); ?>">

</head>
<body>
<header>
        <nav class="navbar">
            <h1 class="logo">📚 Médiathèque</h1>
            <div class="burger">
                <div></div>
                <div></div>
                <div></div>
            </div>
            <ul class="menu"style="display: none;">
                <li><a href="../index.php">Acceuils</a></li>
                <?php if ($isConnected): ?>
        <li><a href="../php/dashboard.php">Dashboard</a></li>
    <?php endif; ?>
                <li><a href="../php/contact.php">Contact</a></li>
                <li><a href="livre.php">Livre</a></li>
                <li><a href="cd.php">CD</a></li>
                <li><a href="dvd.php">DVD</a></li>
            </ul>
            <div class="auth-buttons">
                <?php if ($isConnected): ?>
                    <a href="../php/deconnexion.php" class="btn">Se déconnecter</a>
                <?php else: ?>
                    <a href="../connexion.php" class="btn">Connexion</a>
                    <a href="../inscription.php" class="btn btn-outline">Inscription</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>


    <main>
        <h1>Catalogue de la Médiathèque</h1>

        <!-- Menu de tri -->
        <div class="filter-menu">
            <?php
            foreach ($colonnes_valides as $colonne) {
                echo '<a href="catalogue.php?tri=' . $colonne . '&statut=' . $statut . '">Trier par ' . ucfirst($colonne) . '</a> | ';
            }
            ?>
            <?php
            $nextStatut = $statut === 'disponible' ? 'emprunte' : ($statut === 'emprunte' ? '' : 'disponible');
            $statutLabel = $statut === 'disponible' ? 'Disponibles' : ($statut === 'emprunte' ? 'Empruntés' : 'Tous');
            ?>
            <a href="catalogue.php?tri=<?php echo $tri; ?>&statut=<?php echo $nextStatut; ?>" class="statut-btn">Statut : <?php echo $statutLabel; ?></a>
        </div>

        <div class="encadre">
        <h3>Vos emprunts</h3>
        <?php
        if ($isConnected) {
            $sql_mes_emprunts = "
            SELECT
                CASE
                    WHEN e.type = 'titres_periodiques ' THEN l.titre
                    WHEN e.type = 'dvd' THEN d.titre
                    WHEN e.type = 'cd' THEN c.titre
                END AS titre,
                e.date_emprunt, e.date_retour, e.type
            FROM emprunt e
            LEFT JOIN titres_periodiques  l ON e.id_livre = l.id AND e.type = 'titres_periodiques '
            LEFT JOIN dvd d ON e.id_livre = d.id AND e.type = 'dvd'
            LEFT JOIN cd c ON e.id_livre = c.id AND e.type = 'cd'
            WHERE e.emprunte_par_email = '$email' AND e.retour_effectif IS NULL
            ";
            $res_mes_emprunts = mysqli_query($conn, $sql_mes_emprunts);
            if (mysqli_num_rows($res_mes_emprunts) > 0): ?>
                <ul>
                    <?php while ($row = mysqli_fetch_assoc($res_mes_emprunts)): ?>
                        <?php
                    
                    $date_retour = strtotime($row['date_retour']);
                    $current_date = strtotime(date('Y-m-d'));
                    $isLate = $date_retour < $current_date;
                ?>
                        <li>
                            <strong><?php echo htmlspecialchars($row['titre']); ?></strong><br>
                            Emprunté le : <?php echo htmlspecialchars($row['date_emprunt']); ?><br>
                            Retour prévu : <?php echo htmlspecialchars($row['date_retour']); ?>
                            <?php if ($isLate): ?>
                        <span style="color: red;">Emprunt en retard!</span>
                    <?php endif; ?>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>Vous n'avez ucun emprunt actuellement.</p>
            <?php endif; ?>
            <p><strong>Emprunts restants :</strong> <?php echo max(0, 4 - $nbEmpruntsActifs); ?> / 4</p>
            <?php if ($nbEmpruntsActifs >= 4): ?>
            <p class="warning">Vous avez atteint la limite de 4 emprunts simultanés. Merci de rendre un de vos emprunts avant d'en emprunter un nouveau.</p>
        <?php endif; ?>
        <?php } else { ?>
            <p>Connectez-vous pour voir vos emprunts.</p>
        <?php } ?>
    </div>

        
        

        <!-- Tableau des livres -->
        <?php if (mysqli_num_rows($result) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Titre</th>
                        <th>Auteur</th>
                        <th>Éditeur</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td>
                                <?php 
                                
                                $titleWithoutSpaces = strtolower(str_replace(' ', '_', $row['titre']));
                                
                                
                                $titleWithoutAccents = preg_replace(
                                    '/[àáâãäå]/u', 'a',
                                    preg_replace('/[èéêë]/u', 'e',
                                        preg_replace('/[ìíîï]/u', 'i',
                                            preg_replace('/[òóôõö]/u', 'o',
                                                preg_replace('/[ùúûü]/u', 'u',
                                                    preg_replace('/[ç]/u', 'c',
                                                        preg_replace('/[ñ]/u', 'n',
                                                            preg_replace('/[ýÿ]/u', 'y', $titleWithoutSpaces))))))));
                                
                                
                                $imageName = $titleWithoutAccents . '.jpeg';
                                
                                
                                if (file_exists('../assets/' . $imageName)): ?>
                                    <img src="../assets/<?php echo $imageName; ?>" alt="<?php echo htmlspecialchars($row['titre']); ?>" width="50" height="75">
                                <?php else: ?>
                                    <span>Aucune image</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['titre']); ?></td>
                            <td><?php echo htmlspecialchars($row['auteur']); ?></td>
                            <td><?php echo htmlspecialchars($row['editeur']); ?></td>
                            <td><?php echo $row['statut'] === 'emprunte' ? 'Emprunté' : 'Disponible'; ?></td>
                            <td>
                            <?php if ($isConnected): ?>
        <?php if ($isAbonne): ?>
            <?php if ($row['statut'] === 'disponible' && !$hasLateEmprunt && $nbEmpruntsActifs < 4): ?>
                <a class="voir-details" href="confirmation_emprunt_titres_periodiques.php?id_livre=<?php echo $row['id']; ?>">Voir les détails</a>

                <?php elseif ($row['statut'] === 'emprunte'): ?>
    <?php if (in_array($row['id'], $titres_periodiques_EmpruntesParMoi)): ?>
        <p class="deja-emprunte" style="color: #888; font-style: italic;">Tu m'as déjà chez toi !</p>
    <?php else: ?>
        <form method="post" action="../php/ajouter_precommande.php" style="display:inline;">
    <input type="hidden" name="id_livre" value="<?php echo $row['id']; ?>">
    <input type="hidden" name="type" value="titres_periodiques ">
    <button type="submit" class="btn-precommande">M'avertir quand disponible</button>
</form>

    <?php endif; ?>
<?php endif; ?>

        <?php else: ?>
            <a class="voir-details" href="../php/abonnement.php">S'abonner</a>
        <?php endif; ?>
    <?php else: ?>
        <a class="voir-details" href="../connexion.php">Se connecter</a>
    <?php endif; ?>
</td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Aucun titres_periodiques  trouvé.</p>
        <?php endif; ?>
    </main>
    </body>
    <footer>
        <p>&copy; 2025 Médiathèque de Montpellier. Tous droits réservés.</p>
    </footer>

</html>
<script src="../js/burger.js"></script>