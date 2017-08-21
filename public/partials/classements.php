<table class="tableau_pronostics">
    <tr>
        <th colspan="3" style="font-size:14px">
            <?=$titre?>
        </th>
    </tr>
    <?=$entetes?>

<?php
$i = 0;
while ($row->fetch()) {
    ++$i;
    $color = $i <= 3 ? 'rgb(46,176,58)' : 'rgb(247,180,0)'; ?>
    <tr>
        <td class="center" style="width:10%;">
            <div class="mise" style="background-color:<?=$color?>;"><?=$i?></div>
        </td>
        <td style="width:70%;">
            <a class="none" href="/tipser-stats/?&id=<?=$row->display('author.ID')?>"><?=$row->display('author.user_nicename')?></a>
        </td>
        <td style="width:20%;"><?=TipsFormatter::prefixSign($row->display('Gain'))?></td>
    </tr>
<?php
}
if ($row->total() == 0) {
    ?>
    <tr>
        <td class="center" colspan="3">Pas encore de classement pour ce mois ci</td>
    </tr>
<?php
} ?>
</table>
