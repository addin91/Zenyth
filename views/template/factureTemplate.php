<?php

// 🔹 Calculs
$totalChambre = ($chambre['prix_nuit'] ?? 0) * ($nbNuits ?? 1);

$totalPrestations = 0;
foreach ($reservationPrestations as $p) {
    $totalPrestations += $p['total'] ?? 0;
}

$totalActivites = 0;
foreach ($reservationActivites as $a) {
    if (in_array(strtolower($a['statut']), ['validee','validée'])) {
        $totalActivites += $a['prix'] ?? 0;
    }
}

$total = $totalChambre + $totalPrestations + $totalActivites;

$avoirs = max(0, $facture['avoirs'] ?? 0);
$reduction = max(0, min(100, $facture['reduction'] ?? 0));

$sousTotal = $total - $avoirs;
$montantReduction = $sousTotal * ($reduction / 100);
$prixTotal = max(0, $sousTotal - $montantReduction);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
body { font-family: Arial; font-size: 12px; }
table { width:100%; border-collapse: collapse; margin-top:20px; }
table, th, td { border:1px solid #000; }
th, td { padding:8px; }
.total { text-align:right; font-weight:bold; }
</style>
</head>

<body>

<h1>Facture</h1>

<p>
<strong>Date :</strong>
<?= !empty($facture['date_emission']) ? date('d/m/Y', strtotime($facture['date_emission'])) : 'En cours' ?>
</p>

<h3>Client</h3>
<p>
<?= e($client['nom']) ?> <?= e($client['prenom']) ?><br>
<?= e($client['email']) ?>
</p>

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
<?= e($chambre['nom_chambre']) ?><br>
<?= $nbNuits ?> nuit(s)
</td>
<td><?= number_format($totalChambre,2) ?> €</td>
</tr>

<!-- Prestations -->
<?php foreach ($reservationPrestations as $p): ?>
<tr>
<td>Prestation</td>
<td><?= e($p['nom']) ?></td>
<td><?= number_format($p['total'] ?? 0,2) ?> €</td>
</tr>
<?php endforeach; ?>

<!-- Activités -->
<?php foreach ($reservationActivites as $a): ?>
<?php if (in_array(strtolower($a['statut']), ['validee','validée'])): ?>
<tr>
<td>Activité</td>
<td>
<?= e($a['nom']) ?><br>
<?= e($a['date']) ?> - <?= e($a['creneau'] ?? '') ?><br>
<?= e($a['nombre_personnes_concernées'] ?? 1) ?> pers.
</td>
<td><?= number_format($a['prix'] ?? 0,2) ?> €</td>
</tr>
<?php endif; ?>
<?php endforeach; ?>

</tbody>

<tfoot>

<?php if ($avoirs > 0): ?>
<tr>
<td colspan="2">Avoirs</td>
<td>-<?= number_format($avoirs,2) ?> €</td>
</tr>
<?php endif; ?>

<?php if ($reduction > 0): ?>
<tr>
<td colspan="2">Réduction (<?= $reduction ?>%)</td>
<td>-<?= number_format($montantReduction,2) ?> €</td>
</tr>
<?php endif; ?>

</tfoot>
</table>

<h3 class="total">
Total : <?= number_format($prixTotal,2) ?> €
</h3>

<p>Statut : <?= e($facture['statut']) ?></p>

</body>
</html>