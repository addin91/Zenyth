<?php
// sécuriser affichage

// calculs
$debut = new DateTime($reservation['date_debut']);
$fin = new DateTime($reservation['date_fin']);
$interval = $debut->diff($fin);
$nbNuits = $interval->days;

$totalChambre = $chambre['prix_nuit'] * $nbNuits ?? 0;


$totalPrestations = 0;
if (!empty($reservationPrestations)) {
    foreach ($reservationPrestations as $p) {
        $totalPrestations += $p['total'];
    }
}

$totalActivites = 0;
if (!empty($reservationActivites)) {
    foreach ($reservationActivites as $a) {
        if ($a['statut'] === 'validée') {
            // ⚠️ adapte prix si tu l’as ailleurs
            $totalActivites += $a['nombre_personnes_concernées'] * 0; 
        }
    }
}

$total = $totalChambre + $totalPrestations + $totalActivites;

// Avoirs
$avoirs = max(0, $facture['avoirs'] ?? 0);

// Réduction (%)
$reduction = $facture['reduction'] ?? 0;
$reduction = max(0, min(100, $reduction));

// Calcul après avoirs
$sousTotal = $total - $avoirs;

// Calcul réduction
$montantReduction = ($reduction > 0) ? $sousTotal * ($reduction / 100) : 0;

// Total final
$prixTotal = max(0, $sousTotal - $montantReduction);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Facture Zenyth</title>

<style>
body {
    font-family: Arial, sans-serif;
    font-size: 12px;
}

h1 {
    text-align: center;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

table, th, td {
    border: 1px solid #000;
}

th, td {
    padding: 8px;
    text-align: left;
}

.total {
    text-align: right;
    font-weight: bold;
}
</style>
</head>

<body>

<h1>Facture - Zenyth</h1>

<p><strong>Date :</strong>
<?php 
if (!empty($facture['date_emission'])) {
    echo date('d/m/Y', strtotime($facture['date_emission']));
} else {
    echo "En cours";
}
?>
</p>

<h3>Client</h3>
<p>
<?= e($client['nom']) ?> <?= e($client['prenom']) ?><br>
<?= e($client['email']) ?>
</p>

---

<h3>Détails</h3>

<table>
    <thead>
        <tr>
            <th>Type</th>
            <th>Description</th>
            <th>Prix</th>
        </tr>
    </thead>
    <tbody>

        <!-- Chambre -->
        <tr>
            <td>Chambre</td>
            <td>
                <?= e($chambre['nom_chambre']) ?> <br>
                <?= e($nbNuits ?? 1) ?> nuit(s)
            </td>
            <td><?= number_format($totalChambre, 2) ?> €</td>
        </tr>

        <!-- Prestations -->
        <?php if (!empty($reservationPrestations)): ?>
            <?php foreach ($reservationPrestations as $p): ?>
                <tr>
                    <td>Prestation</td>
                    <td>ID prestation : <?= e($p['id_prestation']) ?></td>
                    <td><?= number_format($p['total'], 2) ?> €</td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Activités -->
        <?php if (!empty($reservationActivites)): ?>
            <?php foreach ($reservationActivites as $a): ?>
                <?php if ($a['statut'] === 'validée'): ?>
                    <tr>
                        <td>Activité</td>
                        <td>
                            Activité #<?= e($a['id_activite']) ?><br>
                            <?= e($a['date']) ?> - <?= e($a['créneau']) ?><br>
                            <?= e($a['nombre_personnes_concernées']) ?> personne(s)
                        </td>
                        <td>0.00 €</td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>

    </tbody>
    <tfoot>
        <!-- Avoirs -->
        <?php if ($avoirs > 0): ?>
        <tr>
            <td colspan="2"><strong>Avoirs</strong></td>
            <td>-<?= number_format($avoirs, 2) ?> €</td>
        </tr>
        <?php endif; ?>

        <!-- Réduction -->
        <?php if ($reduction > 0): ?>
        <tr>
            <td colspan="2">
                <strong>Réduction (<?= number_format($reduction, 0) ?>%)</strong>
            </td>
            <td>-<?= number_format($montantReduction, 2) ?> €</td>
        </tr>
        <?php endif; ?>
    </tfoot>
</table>

---

<h3 class="total">
    Total HT : <?= number_format($total, 2) ?> €<br>

    <?php if ($avoirs > 0): ?>
        Avoirs : -<?= number_format($avoirs, 2) ?> €<br>
    <?php endif; ?>

    <?php if ($reduction > 0): ?>
        Réduction : -<?= number_format($montantReduction, 2) ?> €<br>
    <?php endif; ?>

    <span style="font-size:16px;">
        Total à payer : <?= number_format($prixTotal, 2) ?> €
    </span>
</h3>
<p><strong>Statut :</strong> <?= e($facture['statut']) ?></p>

</body>
</html>