<h1>Liste des tipsters en attente de validation de groupe</h1>
<div class="tipster-container">
<?php
foreach($users as $user) {
    ?>
    <div class="tipster-col">
        <div class="tipster-item">
            <div class="tipster-header">
                <h2><?=$user['name']?></h2>
            </div>
            <div class="tipster-content">
                <?=$user['analyse']?>
            </div>
            <button class="js-pqe-toggle-content button-secondary">Etendre / Fermer</button>
            <div class="tipster-action">
                <a href="/wp-admin/user-edit.php?user_id=<?=$user['id']?>" target="_blank">Aller sur la fiche du tipster pour le valider</a>
            </div>
        </div>
    </div>
    <?php
}
if (count($users) == 0) {
    echo "<p>Aucun utilisateur en attente de validation</p>";
}
?>
</div>
