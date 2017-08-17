<?php
if ($all_tips->total() > 0 || ($more_tips && $more_tips->total() > 0)) {
?>
<table class="tableau_pronostics">
    <tr>
        <th class="date2">Date</th>
        <th class="resultat2"></th>
        <th class="match2">Match</th>
        <?=($show_sport ? '<th class="sport2">Sport</th>' : '')?>
        <?=($show_pari ? '<th class="pari2">Pari</th>' : '')?>
        <?=($show_match_result ? '<th class="match_result">Résultat</th>' : '')?>
        <th class="mise2">Mise</th>
        <?=($show_user ? '<th class="tipster2">Tipster</th>' : '')?>
        <th class="cote2">Cote</th>
        <?=($show_profit ? '<th class="gain2">Profit</th>' : '')?>
    </tr>
<?php
while ( $all_tips->fetch() ) {
    $nb_comments = 0;
    $link = "/pronostique/".$all_tips->field('permalink');
    if($all_tips->field('post.ID') != 0) {
        $comments_count = get_comment_count($all_tips->field('post.ID'));
        $nb_comments = $comments_count ? $comments_count['approved'] : '';
        $link = get_permalink($all_tips->field('post.ID'));
    }
    $pari = mb_substr($all_tips->field('pari'),0,12);
    if($use_poolbox && !empty($all_tips->field('code_poolbox'))) {
        $pari = $all_tips->field('code_poolbox');
    }

    $isTipsVisible = ($isUserAdherent || $all_tips->field('tips_result') || !$all_tips->field('is_vip'));

    if($isTipsVisible) {
?>
    <tr class="<?php echo ($all_tips->field('is_vip') ? 'tips_vip' : '')?>">
        <td class="date2">
            <?=date_i18n("j/m", strtotime($all_tips->field('date')))?>
        </td>
        <td class="resultat2">
            <i class="fa <?=Formatter::resultat2str($all_tips->field('tips_result'))?>" aria-hidden="true"></i>
        </td>
        <td class="match2">
            <strong>
                <a class="simplelink" href="<?=$link?>"><?=stripslashes($all_tips->display('name'))?></a>
                <?php if($nb_comments) {?>
                <span class="tips-comm-number"><?=$nb_comments?></span>
                <?php } ?>
            </strong>
        </td>
        <?=($show_sport ? '<td class="sport2">'.$all_tips->display('sport').'</td>' : '')?>
        <?=($show_pari ? '<td>'.$pari.'</td>' : '')?>
        <?=($show_match_result ? '<td>'.$all_tips->field('match_result').'</td>' : '')?>
        <td class="mise2">
            <div class="mise <?=Formatter::getMiseColorClass($all_tips->field('mise'))?>"><?=$all_tips->field('mise')?></div>
        </td>
        <?=($show_user ? "<td><a href=\"/tipser-stats/?id=".$all_tips->field('author.ID')."\">".$all_tips->field('author.user_nicename')."</a></td>" : '')?>
        <td class="couleurcote"><?=$all_tips->display('cote')?></td>
        <?=($show_profit ? '<th class="gain2">'.Calculator::Gain($all_tips->field('tips_result'),$all_tips->field('mise'),$all_tips->display('cote')).'</th>' : '')?>
    </tr>
<?php } else { ?>
    <tr class="hidden_tips">
        <td class="date2">
            <?=date_i18n("j/m", strtotime($all_tips->field('date')))?>
        </td>
        <td class="resultat2">
            <i class="fa <?=Formatter::resultat2str($all_tips->field('tips_result'))?>" aria-hidden="true"></i>
        </td>
        <td class="match2">
            <strong>
                <a class="simplelink" href="/vip/">Rejoignez les VIPS pour accéder à ce pronostique</a>
            </strong>
        </td>
        <?=($show_sport ? '<td class="sport2">'.$all_tips->display('sport').'</td>' : '')?>
        <?=($show_pari ? '<td class=""><i class="fa fa-lock" aria-hidden="true"></i></td>' : '')?>
        <?=($show_match_result ? '<td class=""></td>' : '')?>
        <td class="mise2">
            <div class="mise <?=Formatter::getMiseColorClass($all_tips->field('mise'))?>"><?=$all_tips->field('mise')?></div>
        </td>
        <?=($show_user ? "<td><a href=\"/tipser-stats/?id=".$all_tips->field('author.ID')."\">".$all_tips->field('author.user_nicename')."</a></td>" : '')?>
        <td class="couleurcote"><i class="fa fa-lock" aria-hidden="true"></i></td>
        <?=($show_profit ? '<th class="gain2">'.Calculator::Gain($all_tips->field('tips_result'),$all_tips->field('mise'),$all_tips->field('cote')).'</th>' : '')?>
    </tr>
<?php } } ?>
<?php
if ($more_tips) {
while ( $more_tips->fetch() ) {
    $nb_comments = 0;
    $link = "/pronostique/".$more_tips->field('permalink');
    if($more_tips->field('post.ID') != 0) {
        $comments_count = get_comment_count($more_tips->field('post.ID'));
        $nb_comments = $comments_count ? $comments_count['approved'] : '';
        $link = get_permalink($more_tips->field('post.ID'));
    }
    $pari = mb_substr($more_tips->field('pari'),0,6);
    if($use_poolbox && !empty($more_tips->field('code_poolbox'))) {
        $pari = $more_tips->field('code_poolbox');
    }

    $isTipsVisible = ($isUserAdherent || $more_tips->field('tips_result') || !$more_tips->field('is_vip'));

    if($isTipsVisible) {
?>
    <tr>
        <td class="date2">
            <?=date_i18n("j/m", strtotime($more_tips->field('date')))?>
        </td>
        <td class="resultat2">
            <i class="fa <?=Formatter::resultat2str($more_tips->field('tips_result'))?>" aria-hidden="true"></i>
        </td>
        <td class="match2">
            <strong>
                <a class="simplelink" href="<?=$link?>"><?=stripslashes($more_tips->display('name'))?></a>
                <?php if($nb_comments) {?>
                <span class="tips-comm-number"><?=$nb_comments?></span>
                <?php } ?>
            </strong>
        </td>
        <?=($show_sport ? '<td class="sport2">'.$more_tips->display('sport').'</td>' : '')?>
        <?=($show_pari ? '<td>'.$pari.'</td>' : '')?>
        <?=($show_match_result ? '<td>'.$more_tips->field('match_result').'</td>' : '')?>
        <td class="mise2">
            <div class="mise <?=Formatter::getMiseColorClass($more_tips->field('mise'))?>"><?=$more_tips->field('mise')?></div>
        </td>
        <?=($show_user ? "<td><a href=\"/tipser-stats/?id=".$more_tips->field('author.ID')."\">".$more_tips->field('author.user_nicename')."</a></td>" : '')?>
        <td class="couleurcote"><?=$more_tips->display('cote')?></td>
        <?=($show_profit ? '<th class="gain2">'.Calculator::Gain($more_tips->field('tips_result'),$more_tips->field('mise'),$more_tips->display('cote')).'</th>' : '')?>
    </tr>
<?php } else { ?>
    <tr class="hidden_tips">
        <td class="date2">
            <?=date_i18n("j/m", strtotime($more_tips->field('date')))?>
        </td>
        <td class="resultat2">
            <i class="fa <?=Formatter::resultat2str($more_tips->field('tips_result'))?>" aria-hidden="true"></i>
        </td>
        <td class="match2">
            <strong>
                <a class="simplelink" href="/vip/">Rejoignez les VIPS pour accéder à ce pronostique</a>
            </strong>
        </td>
        <?=($show_sport ? '<td class="sport2">'.$more_tips->display('sport').'</td>' : '')?>
        <?=($show_pari ? '<td class=""><i class="fa fa-lock" aria-hidden="true"></i></td>' : '')?>
        <?=($show_match_result ? '<td class=""></td>' : '')?>
        <td class="mise2">
            <div class="mise <?=Formatter::getMiseColorClass($more_tips->field('mise'))?>"><?=$more_tips->field('mise')?></div>
        </td>
        <?=($show_user ? "<td><a href=\"/tipser-stats/?id=".$more_tips->field('author.ID')."\">".$more_tips->field('author.user_nicename')."</a></td>" : '')?>
        <td class="couleurcote"><i class="fa fa-lock" aria-hidden="true"></i></td>
        <?=($show_profit ? '<th class="gain2">'.Calculator::Gain($more_tips->field('tips_result'),$more_tips->field('mise'),$more_tips->field('cote')).'</th>' : '')?>
    </tr>
<?php } } } ?>
</table>
<?php
} else {
    echo '<p class="no_result">Aucun pronostique dans cette catégorie actuellement</p>';
}
?>
