<?php
if (count($tips) > 0) {
?>
<table class="tableau_pronostics">
    <tr>
        <?=($display_columns['date'] ? '<th class="date2">Date</th>' : '')?>
        <?=($display_columns['icon'] ? '<th class="resultat2"></th>' : '')?>
        <?=($display_columns['match'] ? '<th class="match2">Match</th>' : '')?>
        <?=($display_columns['sport'] ? '<th class="sport2">Sport</th>' : '')?>
        <?=($display_columns['competition'] ? '<th class="competition">Compétition</th>' : '')?>
        <?=($display_columns['pari'] ? '<th class="pari2">Pari</th>' : '')?>
        <?=($display_columns['resultat'] ? '<th class="match_result">Résultat</th>' : '')?>
        <?=($display_columns['mise'] ? '<th class="mise2">Mise</th>' : '')?>
        <?=($display_columns['tipster'] ? '<th class="tipster2">Tipster</th>' : '')?>
        <?=($display_columns['cote'] ? '<th class="cote2">Cote</th>' : '')?>
        <?=($display_columns['profit'] ? '<th class="gain2">Profit</th>' : '')?>
    </tr>
<?php
foreach($tips as $tips) {
    $comments = '';
    $link = "/pronostics/".$tips->permalink;

    if($tips->post_id != 0) {
        $comments_count = get_comment_count($tips->post_id);
        $nb_comments = $comments_count ? $comments_count['approved'] : '';
        $link = get_permalink($tips->post_id);
        if($nb_comments) {
            $comments = '<span class="tips-comm-number">'.$nb_comments.'</span>';
        }
    }
    $pari = mb_substr($tips->pari,0,12);
    if($use_poolbox && !empty($tips->code_poolbox)) {
        $pari = $tips->code_poolbox;
    }

    // Les adhérents voient tout les tips
    // Les tips avec un résultat sont toujours visible
    // Les tips non-vip sont toujours visible
    $isTipsVisible = ($isUserAdherent || $tips->tips_result || !$tips->is_vip);

    if($isTipsVisible) {
?>
<tr class="<?php echo ($tips->is_vip ? 'tips_vip' : '')?>">
    <?=($display_columns['date'] ? '<td class="date2" title="'.date_i18n("j/m/Y H:i", strtotime($tips->date)).'">'.date_i18n("j/m", strtotime($tips->date)).'</td>' : '')?>
    <?=($display_columns['icon'] ? '<td class="resultat2"> <i class="fa '.TipsFormatter::resultat2str($tips->tips_result).'" aria-hidden="true"></i> </td>' : '')?>
    <?=($display_columns['match'] ? ' <td class="match2"> <strong> <a class="simplelink" href="'.$link.'">'.stripslashes($tips->name).'</a> '.$comments.'</strong> </td>' : '')?>
    <?=($display_columns['sport'] ? '<td class="sport2">'.$tips->sport.'</td>' : '')?>
    <?=($display_columns['competition'] ? '<td class="competition">'.$tips->competition.'</td>' : '')?>
    <?=($display_columns['pari'] ? '<td class="pari2" title="'.$tips->pari.'">'.$pari.'</td>' : '')?>
    <?=($display_columns['resultat'] ? '<td>'.$tips->match_result.'</td>' : '')?>
    <?=($display_columns['mise'] ? '<td class="mise2"><div class="mise '.TipsFormatter::getMiseColorClass($tips->mise).'">'.$tips->mise.'</div></td>' : '')?>
    <?=($display_columns['tipster'] ? '<td><a href="/tipser-stats/?id='.$tips->tipster_id.'">'.$tips->tipster_nicename.'</a></td>' : '')?>
    <?=($display_columns['cote'] ? '<td class="couleurcote">'.$tips->cote.'</td>' : '')?>
    <?=($display_columns['profit'] ? '<th class="gain2">'.Calculator::Gain($tips->tips_result,$tips->mise,$tips->cote).'</th>' : '')?>
</tr>
<?php } else { ?>
    <tr class="hidden_tips">
        <?=($display_columns['date'] ? '<td class="date2">'.date_i18n("j/m", strtotime($tips->date)).'</td>' : '')?>
        <?=($display_columns['icon'] ? '<td class="resultat2"> <i class="fa '.TipsFormatter::resultat2str($tips->tips_result).'" aria-hidden="true"></i> </td>' : '')?>
        <?=($display_columns['match'] ? '<td class="match2"> <strong> <a class="simplelink" href="/vip/">Rejoignez le VIP pour accéder à ce pari</a> </strong> </td>' : '')?>
        <?=($display_columns['sport'] ? '<td class="sport2">'.$tips->sport.'</td>' : '')?>
        <?=($display_columns['competition'] ? '<td class="competition">'.$tips->competition.'</td>' : '')?>
        <?=($display_columns['pari'] ? '<td class=""><i class="fa fa-lock" aria-hidden="true"></i></td>' : '')?>
        <?=($display_columns['resultat'] ? '<td class=""></td>' : '')?>
        <?=($display_columns['mise'] ? '<td class="mise2"><div class="mise '.TipsFormatter::getMiseColorClass($tips->mise).'">'.$tips->mise.'</div></td>' : '')?>
        <?=($display_columns['tipster'] ? '<td><a href="/tipser-stats/?id='.$tips->tipster_id.'">'.$tips->tipster_nicename.'</a></td>' : '')?>
        <?=($display_columns['cote'] ? '<td class="couleurcote"><i class="fa fa-lock" aria-hidden="true"></i></td>' : '')?>
        <?=($display_columns['profit'] ? '<th class="gain2">'.Calculator::Gain($tips->tips_result,$tips->mise,$tips->cote).'</th>' : '')?>
    </tr>
<?php } } ?>
</table>
<?php
} else {
    echo '<p class="no_result">Aucun pronostique dans cette catégorie actuellement</p>';
}
?>
