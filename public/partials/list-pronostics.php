<table class="tableau_pronostics">
    <tr>
        <th class="date2">Date</th>
        <th class="resultat2"></th>
        <th class="match2">Match</th>
        <?=($show_sport ? '<th class="sport2">Sport</th>' : '')?>
        <th class="mise2">Mise</th>
        <?=($show_user ? '<th class="tipster2">Tipster</th>' : '')?>
        <th class="cote2">Cote</th>
    </tr>
<?php
while ( $all_tips->fetch() ) {
    $all_tipser_str = $show_user ? "<td><a href=\"/tipser-stats/?id=".$all_tips->field('author.ID')."\">".$all_tips->field('author.user_nicename')."</a></td>" : '';
    $val_sport = $show_sport ? '<td class="sport2">'.$all_tips->display('sport').'</td>' : '';
    $comments_count = get_comment_count($all_tips->field('post.ID'));
    $nb_comments = $comments_count ? $comments_count['approved'] : '';
?>
    <tr>
        <td class="date2"><?=date("j/m/y",strtotime($all_tips->field('date')))?> <?=$all_tips->dispay('permalink')?></td>
        <td class="resultat2"><?=Formatter::resultat2str($all_tips->field('resultat'))?></td>
        <td class="match2">
            <strong>
                <a class="simplelink" href="/pronostique/<?=$all_tips->field('permalink')?>"><?=$all_tips->display('name')?></a>
                <span class="tips-comm-number"><?=$nb_comments?></span>
            </strong>
        </td>
        <?=$val_sport?>
        <td class="mise2"><?=Formatter::mise2str($all_tips->field('mise'))?></td>
        <?=$all_tipser_str?>
        <td class="couleurcote"><?=$all_tips->display('cote')?></td>
    </tr>
<?php } ?>
</table>
