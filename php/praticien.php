<?php
/**
 * PAGE DE CONSULTATION DES PRATICIENS - GSB
 * Objectif : Permettre de consulter les coordonnées et le lieu d'exercice d'un praticien.
 */

// 1. Connexion à la base de données
try {
    // Connexion via PDO (hôte, nom de base, encodage, utilisateur, mot de passe)
    $bdd = new PDO('mysql:host=localhost;dbname=gsbsamuel;charset=utf8', 'root', '');
} catch (Exception $e) {
    // Si la connexion échoue, on affiche l'erreur et on arrête tout
    die('Erreur : ' . $e->getMessage());
}

// 2. Récupération de la liste des praticiens pour le menu déroulant
// On sélectionne le numéro (ID), le nom et le prénom pour remplir le select
$reqListe = $bdd->query("SELECT PRA_NUM, PRA_NOM, PRA_PRENOM FROM praticien ORDER BY PRA_NOM");
$praticiens = $reqListe->fetchAll();

// 3. Traitement de la sélection d'un praticien
$infosPra = null; // Variable pour stocker les informations détaillées
if (isset($_POST['id_pra']) && !empty($_POST['id_pra'])) {
    
    // Requête préparée pour éviter les injections SQL
    // INNER JOIN permet de récupérer le libellé du type (ex: Médecin de ville) 
    // présent dans la table 'type_praticien' grâce à la clé étrangère TYP_CODE
    $stmt = $bdd->prepare("
        SELECT P.*, T.TYP_LIBELLE 
        FROM praticien P
        INNER JOIN type_praticien T ON P.TYP_CODE = T.TYP_CODE 
        WHERE P.PRA_NUM = ?
    ");
    $stmt->execute([$_POST['id_pra']]);
    
    // On récupère le résultat sous forme de tableau associatif
    $infosPra = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultation Praticiens - GSB</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="shortcut icon" href="../images/Gsblogo.png" type="image/x-icon">
</head>
<body>

<header>
    <h1>Praticiens - Application GSB</h1>
    <a href="../index.php"><img src="../images/Gsblogo.png" alt="Logo GSB"></a>
</header>

<main>
    <nav class="menu">
        <ul>
            <li><a href="../index.php">Retour à l'accueil</a></li>
        </ul>
    </nav>

    <section>
        <h2>Sélectionner un praticien</h2>
        <form method="POST" action="">
            <label for="id_pra">Choisir un praticien :</label>
            
            <select name="id_pra" id="id_pra" onchange="this.form.submit()">
                <option value="">-- Choisissez dans la liste --</option>
                
                <?php foreach ($praticiens as $p): ?>
                    <option value="<?= $p['PRA_NUM'] ?>" <?= (isset($_POST['id_pra']) && $_POST['id_pra'] == $p['PRA_NUM']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['PRA_NOM'] . " " . $p['PRA_PRENOM']) ?>
                    </option>
                <?php endforeach; ?>
                
            </select>
            <noscript><button type="submit">Valider</button></noscript>
        </form>
    </section>

    <hr>

    <?php 
    // 4. Affichage des résultats si un praticien a été trouvé en base
    if ($infosPra): 
    ?>
    <section class="details-praticien">
        <h3>Détails de : <?= htmlspecialchars($infosPra['PRA_NOM'] . " " . $infosPra['PRA_PRENOM']) ?></h3>
        
        <table border="1">
            <tr><td><strong>Numéro</strong></td><td><?= htmlspecialchars($infosPra['PRA_NUM']) ?></td></tr>
            <tr><td><strong>Adresse</strong></td><td><?= htmlspecialchars($infosPra['PRA_ADRESSE']) ?></td></tr>
            <tr><td><strong>Ville</strong></td><td><?= htmlspecialchars($infosPra['PRA_CP'] . " " . $infosPra['PRA_VILLE']) ?></td></tr>
            <tr><td><strong>Coeff. Notoriété</strong></td><td><?= htmlspecialchars($infosPra['PRA_COEFNOTORIETE']) ?></td></tr>
            <tr><td><strong>Lieu d'exercice</strong></td><td><?= htmlspecialchars($infosPra['TYP_LIBELLE']) ?></td></tr>
        </table>
    </section>
    
    <?php 
    // Si un ID a été envoyé mais qu'aucune donnée n'est revenue (cas d'erreur)
    elseif (isset($_POST['id_pra']) && !empty($_POST['id_pra'])): 
    ?>
        <p>Aucune information trouvée pour ce praticien.</p>
    <?php endif; ?>

</main>

<footer>
    <p>&copy; 2026 Samuel Maitrot. GSB.</p>
</footer>

</body>
</html>