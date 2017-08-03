<?php
if ($all_tips->total() > 0) {
?>
<table class="tableau_pronostics">
    <tr>
        <th class="date2">Date</th>
        <th class="resultat2"></th>
        <th class="match2">Match</th>
        <th class="">Pari</th>
        <?=($show_sport ? '<th class="sport2">Sport</th>' : '')?>
        <th class="mise2">Mise</th>
        <?=($show_user ? '<th class="tipster2">Tipster</th>' : '')?>
        <th class="cote2">Cote</th>
    </tr>
<?php
while ( $all_tips->fetch() ) {
    $all_tipser_str = $show_user ? "<td><a href=\"/tipser-stats/?id=".$all_tips->field('author.ID')."\">".$all_tips->field('author.user_nicename')."</a></td>" : '';
    $val_sport = $show_sport ? '<td class="sport2">'.$all_tips->display('sport').'</td>' : '';
    $nb_comments = 0;
    if($all_tips->field('post.ID') != 0) {
        $comments_count = get_comment_count($all_tips->field('post.ID'));
        $nb_comments = $comments_count ? $comments_count['approved'] : '';
    }
    $code_poolbox = !empty($all_tips->field('code_poolbox')) ? $all_tips->field('code_poolbox') : $all_tips->field('pari');

    $isTipsVisible = ($isUserAdherent || $all_tips->field('resultat') || !$all_tips->field('is_vip'));

    if($isTipsVisible) {
?>
    <tr>
        <td class="date2"><?=date_i18n("j/m", strtotime($all_tips->field('date')))?></td>
        <td class="resultat2"><i class="fa <?=Formatter::resultat2str($all_tips->field('resultat'))?>" aria-hidden="true"></i>
</td>
        <td class="match2">
            <strong>
                <a class="simplelink" href="/pronostique/<?=$all_tips->field('permalink')?>"><?=stripslashes($all_tips->display('name'))?></a>
                <span class="tips-comm-number"><?=$nb_comments?></span>
            </strong>
        </td>
        <td><?=substr($code_poolbox,0,6)?></td>
        <?=$val_sport?>
        <td class="mise2">
            <div class="mise <?=Formatter::getMiseColorClass($all_tips->field('mise'))?>"><?=$all_tips->field('mise')?></div>
        </td>
        <?=$all_tipser_str?>
        <td class="couleurcote"><?=$all_tips->display('cote')?></td>
    </tr>
<?php } else { ?>
    <tr>
        <td class="date2"><?=date_i18n("j/m", strtotime($all_tips->field('date')))?></td>
        <td class="resultat2"><?=Formatter::resultat2str($all_tips->field('resultat'))?></td>
        <td class="match2">
            <strong>
                <a class="simplelink" href="">Rejoignez les VIPS pour accéder à ce pronostique</a>
            </strong>
        </td>
        <td class=""><i class="fa fa-lock" aria-hidden="true"></i></td>
        <?=$val_sport?>
        <td class="mise2">
            <div class="mise <?=Formatter::getMiseColorClass($all_tips->field('mise'))?>"><?=$all_tips->field('mise')?></div>
        </td>
        <?=$all_tipser_str?>
        <td class="couleurcote"><i class="fa fa-lock" aria-hidden="true"></i></td>
    </tr>

<?php } } ?>
</table>
<?php
} else {
    echo '<p class="no_result">Aucun pronostique dans cette catégorie actuellement</p>';
}
?>
