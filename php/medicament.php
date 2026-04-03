<?php
/**
 * PAGE DE CONSULTATION DES MÉDICAMENTS - GSB
 * Objectif : Afficher les caractéristiques d'un médicament sélectionné.
 */

// 1. Connexion à la base de données
try {
    // Connexion via PDO (PHP Data Objects) pour la sécurité et la flexibilité
    $bdd = new PDO('mysql:host=localhost;dbname=gsbsamuel;charset=utf8', 'root', '');
} catch (Exception $e) {
    // Arrêt du script en cas d'erreur de connexion
    die('Erreur : ' . $e->getMessage());
}

// 2. Récupération de la liste pour le menu déroulant déroulant
// On ne récupère que l'ID (DEPOTLEGAL) et le Nom pour optimiser la mémoire
$reqListe = $bdd->query("SELECT MED_DEPOTLEGAL, MED_NOMCOMMERCIAL FROM medicament ORDER BY MED_NOMCOMMERCIAL");
$medicaments = $reqListe->fetchAll();

// 3. Traitement du choix utilisateur
$infosMed = null; // Initialisation de la variable de stockage des détails
if (isset($_POST['id_med']) && !empty($_POST['id_med'])) {
    
    // Requête préparée avec une JOINTURE (INNER JOIN)
    // On lie la table 'medicament' à la table 'famille' pour avoir le nom de la famille (FAM_LIBELLE)
    // au lieu d'avoir juste un code technique.
    $stmt = $bdd->prepare("
        SELECT M.*, F.FAM_LIBELLE 
        FROM medicament M 
        INNER JOIN famille F ON M.FAM_CODE = F.FAM_CODE 
        WHERE M.MED_DEPOTLEGAL = ?
    ");
    $stmt->execute([$_POST['id_med']]);
    $infosMed = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultation Médicaments - GSB</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<header>
    <h1>Médicaments - Application GSB</h1>
    <a href="../index.php"><img src="../images/Gsblogo.png" alt="Logo GSB"></a>
</header>

<main>
    <nav class="menu">
        <ul>
            <li><a href="../index.php">Retour à l'accueil</a></li>
        </ul>
    </nav>

    <section>
        <h2>Sélectionner un médicament</h2>
        <form method="POST" action="">
            <label for="id_med">Choisir un nom :</label>
            
            <select name="id_med" id="id_med" onchange="this.form.submit()">
                <option value="">-- Choisissez dans la liste --</option>
                
                <?php foreach ($medicaments as $m): ?>
                    <option value="<?= $m['MED_DEPOTLEGAL'] ?>" <?= (isset($_POST['id_med']) && $_POST['id_med'] == $m['MED_DEPOTLEGAL']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($m['MED_NOMCOMMERCIAL']) ?>
                    </option>
                <?php endforeach; ?>
                
            </select>
            <noscript><button type="submit">Valider</button></noscript>
        </form>
    </section>

    <hr>

    <?php if ($infosMed): ?>
    <section class="details-medicament">
        <h3>Détails de : <?= htmlspecialchars($infosMed['MED_NOMCOMMERCIAL']) ?></h3>
        
        <table border="1">
            <tr><td><strong>Dépôt Légal</strong></td><td><?= htmlspecialchars($infosMed['MED_DEPOTLEGAL']) ?></td></tr>
            <tr><td><strong>Famille</strong></td><td><?= htmlspecialchars($infosMed['FAM_LIBELLE']) ?></td></tr>
            
            <tr><td><strong>Composition</strong></td><td><?= nl2br(htmlspecialchars($infosMed['MED_COMPOSITION'])) ?></td></tr>
            <tr><td><strong>Effets</strong></td><td><?= nl2br(htmlspecialchars($infosMed['MED_EFFETS'])) ?></td></tr>
            <tr><td><strong>Contre-indications</strong></td><td><?= nl2br(htmlspecialchars($infosMed['MED_CONTREINDIC'])) ?></td></tr>
            
            <tr>
                <td><strong>Prix Échantillon</strong></td>
                <td><?= number_format((float)$infosMed['MED_PRIXECHANTILLON'], 2, ',', ' ') ?> €</td>
            </tr>
        </table>
    </section>
    
    <?php elseif (isset($_POST['id_med'])): ?>
        <p>Aucune information trouvée pour ce médicament.</p>
    <?php endif; ?>

</main>

<footer>
    <p>&copy; 2026 Samuel Maitrot. GSB.</p>
</footer>

</body>
</html>