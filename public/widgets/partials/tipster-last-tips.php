<?=$before_widget?>
<?=$widget_title?>
<?php
if ($tips->total() > 0) {
?>
<table class="tableau_pronostics">
    <tr>
        <th class="date2">Date</th>
        <th class="resultat2"></th>
        <th class="match2">Match</th>
        <th class="cote2">Cote</th>
    </tr>
<?php
while ( $tips->fetch() ) {
    $link = "/pronostics/".$tips->field('permalink');
    if($tips->field('post.ID') != 0) {
        $link = get_permalink($tips->field('post.ID'));
    }

    $isTipsVisible = ($isUserAdherent || $tips->field('tips_result') || !$tips->field('is_vip'));

    if($isTipsVisible) {
?>
    <tr>
        <td class="date2"><?=date_i18n("j/m", strtotime($tips->field('date')))?></td>
        <td class="resultat2"><i class="fa <?=TipsFormatter::resultat2str($tips->field('tips_result'))?>" aria-hidden="true"></i>
</td>
        <td class="match2">
            <strong>
                <a class="simplelink" href="<?=$link?>"><?=stripslashes($tips->display('name'))?></a>
            </strong>
        </td>
        <td class="couleurcote"><?=$tips->display('cote')?></td>
    </tr>
<?php } else { ?>
    <tr>
        <td class="date2"><?=date_i18n("j/m", strtotime($tips->field('date')))?></td>
        <td class="resultat2"><i class="fa <?=TipsFormatter::resultat2str($tips->field('tips_result'))?>" aria-hidden="true"></i></td>
        <td class="match2">
            <strong>
                <a class="simplelink" href="/vip/">Rejoignez les VIPS pour accéder à ce pronostic</a>
            </strong>
        </td>
        <td class="couleurcote"><i class="fa fa-lock" aria-hidden="true"></i></td>
    </tr>

<?php } } ?>
</table>
<?php
} else {
    echo '<p class="no_result">Aucun pronostic pour ce tipster.</p>';
}
?>
<?=$after_widget?>
