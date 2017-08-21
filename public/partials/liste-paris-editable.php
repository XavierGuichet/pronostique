<?php
if ($tips->total() > 0) {
?>
<table class="tableau_pronostics">
    <tr>
        <th class="date2">Date</th>
        <th class="match2">Match</th>
        <th class="sport2">Sport</th>
        <th class="pari2">Pari</th>
        <th class="mise2">Mise</th>
        <th class="cote2">Cote</th>
        <th class="action2"></th>
    </tr>
<?php
while ( $tips->fetch() ) {
    $link = "/pronostique/".$tips->field('permalink');
    if($tips->field('post.ID') != 0) {
        $link = get_permalink($tips->field('post.ID'));
    }

?>
    <tr>
        <td class="date2">
            <?=date_i18n("j/m/Y", strtotime($tips->field('date')))?>
        </td>
        <td class="match2">
            <strong>
                <a class="simplelink" href="<?=$link?>"><?=stripslashes($tips->display('name'))?></a>
            </strong>
        </td>
        <td class="sport2"><?=$tips->display('sport')?></td>
        <td><?=$tips->field('pari')?></td>
        <td class="mise2">
            <div class="mise <?=TipsFormatter::getMiseColorClass($tips->field('mise'))?>"><?=$tips->field('mise')?></div>
        </td>
        <td class="couleurcote"><?=$tips->display('cote')?></td>
        <td class="action2">
            <a href="<?=$link?>" target="_blank"><button><i class="fa fa-eye" aria-hidden="true"></i></button></a>
            <a href="/edit-pronostic/?id=<?=$tips->field('id')?>"><button><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
        </td>
    </tr>
<?php } ?>
</table>
<?php } else {
    echo '<p class="no_result">Vous n\'avez aucun paris expert modifiable actuellement.</p>';
}
?>
