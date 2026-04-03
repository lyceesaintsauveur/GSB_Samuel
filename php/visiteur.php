<?php
/**
 * PAGE DE CONSULTATION DES VISITEURS - GSB
 * Objectif : Sélectionner un visiteur dans une liste et afficher ses informations détaillées.
 */

// 1. Connexion à la base de données via l'objet PDO
try {
    // Les paramètres : driver mysql, hôte, nom de la base, encodage, utilisateur et mot de passe
    $bdd = new PDO('mysql:host=localhost;dbname=gsbsamuel;charset=utf8', 'root', '');
    // Activation des erreurs PDO pour faciliter le débuggage (optionnel mais conseillé)
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    // En cas d'échec, on arrête le script et on affiche l'erreur
    die('Erreur de connexion : ' . $e->getMessage());
}

// 2. Récupération de la liste complète pour remplir le menu déroulant
// On trie par nom pour que la recherche soit plus intuitive pour l'utilisateur
$reqListe = $bdd->query("SELECT VIS_MATRICULE, VIS_NOM, Vis_PRENOM FROM visiteur ORDER BY VIS_NOM");
$visiteurs = $reqListe->fetchAll();

// 3. Traitement du formulaire : Si un visiteur a été sélectionné
$infosVis = null; // Variable qui stockera les données du visiteur choisi
if (isset($_POST['id_vis']) && !empty($_POST['id_vis'])) {
    
    // Utilisation d'une requête préparée pour contrer les injections SQL
    // Le '?' sera remplacé par la valeur de $_POST['id_vis'] lors de l'execute
    $stmt = $bdd->prepare("
        SELECT V.*, S.SEC_LIBELLE 
        FROM visiteur V
        LEFT JOIN secteur S ON V.SEC_CODE = S.SEC_CODE 
        WHERE V.VIS_MATRICULE = ?
    ");
    $stmt->execute([$_POST['id_vis']]);
    
    // fetch() récupère la ligne correspondante sous forme de tableau associatif
    $infosVis = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultation Visiteurs - GSB</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="shortcut icon" href="../images/Gsblogo.png" type="image/x-icon">
</head>
<body>

<header>
    <h1>Visiteurs - Application GSB</h1>
    <a href="../index.php"><img src="../images/Gsblogo.png" alt="Logo GSB"></a>
</header>

<main>
    <nav class="menu">
        <ul>
            <li><a href="../index.php">Retour à l'accueil</a></li>
        </ul>
    </nav>

    <section>
        <h2>Sélectionner un visiteur</h2>
        <form method="POST" action="">
            <label for="id_vis">Choisir un collaborateur :</label>
            
            <select name="id_vis" id="id_vis" onchange="this.form.submit()">
                <option value="">-- Choisissez dans la liste --</option>
                
                <?php foreach ($visiteurs as $v): ?>
                    <option value="<?= $v['VIS_MATRICULE'] ?>" <?= (isset($_POST['id_vis']) && $_POST['id_vis'] == $v['VIS_MATRICULE']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars(strtoupper($v['VIS_NOM']) . " " . $v['Vis_PRENOM']) ?>
                    </option>
                <?php endforeach; ?>
                
            </select>
            <noscript><button type="submit">Valider</button></noscript>
        </form>
    </section>

    <hr>

    <?php 
    // 4. Affichage conditionnel : Si on a récupéré des informations sur un visiteur
    if ($infosVis): 
    ?>
    <section class="details-visiteur">
        <h3>Fiche de : <?= htmlspecialchars($infosVis['VIS_NOM'] . " " . $infosVis['Vis_PRENOM']) ?></h3>
        
        <table border="1">
            <tr><td><strong>Matricule</strong></td><td><?= htmlspecialchars($infosVis['VIS_MATRICULE']) ?></td></tr>
            <tr><td><strong>Adresse</strong></td><td><?= htmlspecialchars($infosVis['VIS_ADRESSE']) ?></td></tr>
            <tr><td><strong>Ville</strong></td><td><?= htmlspecialchars($infosVis['VIS_CP'] . " " . $infosVis['VIS_VILLE']) ?></td></tr>
            <tr><td><strong>Secteur</strong></td><td><?= htmlspecialchars($infosVis['SEC_LIBELLE'] ?? 'Non affecté') ?></td></tr>
            <tr><td><strong>Date d'embauche</strong></td><td><?= date('d/m/Y', strtotime($infosVis['VIS_DATEEMBAUCHE'])) ?></td></tr>
        </table>
    </section>

    <?php 
    // Si l'utilisateur a validé mais que fetch() n'a rien renvoyé (cas rare)
    elseif (isset($_POST['id_vis']) && !empty($_POST['id_vis'])): 
    ?>
        <p>Aucune information trouvée pour ce visiteur.</p>
    <?php endif; ?>

</main>

<footer>
    <p>&copy; 2026 Samuel Maitrot. GSB.</p>
</footer>

</body>
</html>