<h1>Pronostique : ajout rapide de resultat (<?=$tips->total()?>)</h1>
<div class="prono-quick-edit-container">
<?php
while ($tips->fetch()) {
    ?>
    <div class="prono-quick-edit-col">
    <div class="prono-quick-edit-item">
        <div class="prono-quick-edit-header">
            <h2><?=$tips->field('name')?></h2>
            <p class="date"><?=date_i18n("d F Y", strtotime($tips->field('date')))?></p>
            <p class="sport"><?=$tips->field('sport.name')?></p>
            <p class="tipster"><?=$tips->field('author.user_nicename')?></p>
        </div>
        <div class="prono-quick-edit-content">
            <?=$tips->field('analyse')?>
        </div>
        <button class="js-pqe-toggle-content button-secondary">Etendre / Fermer</button>
        <div class="prono-quick-edit-tips">
            <table>
                <tr>
                    <th>Pari</th>
                    <th>Cote</th>
                    <th>Mise</th>
                </tr>
                <tr>
                    <td><?=$tips->field('pari')?></td>
                    <td><?=$tips->field('cote')?></td>
                    <td><?=$tips->field('mise')?></td>
                </tr>
            </table>
        </div>
        <div class="prono-quick-edit-form">
            <form class="prono-quick-edit-ajaxform">
                <input type="hidden" name="ID" value="<?=$tips->field('id')?>"/>
                <div class="prono-quick-edit-field">
                    <label>Resultat du match</label>
                    <input type="text" name="match_result" value="<?=$tips->field('match_result')?>"/>
                </div>
                <div class="prono-quick-edit-field">
                    <label>Resultat du Pari</label>
                    <select name="tips_result" class="pods-form-ui-field-type-pick pods-form-ui-field-name-pods-field-tips-result" autocomplete="off">
                        <option value="0" <?=($tips->field('tips_result') == "0" ? 'selected' : '')?>>inconnu</option>
                        <option value="1" <?=($tips->field('tips_result') == "1" ? 'selected' : '')?>>gagné</option>
                        <option value="2" <?=($tips->field('tips_result') == "2" ? 'selected' : '')?>>perdu</option>
                        <option value="3" <?=($tips->field('tips_result') == "3" ? 'selected' : '')?>>remboursé</option>
                    </select>
                </div>
                <input type="submit" class="button-primary" value="Valider">
            </form>
            <div class="form-errors notice-warning error" style="display: none;">

            </div>
        </div>
    </div>
    </div>
    <?php

}
?>
</div>
