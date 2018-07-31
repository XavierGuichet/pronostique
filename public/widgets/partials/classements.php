<?=$before_widget?>
<?=$widget_title?>
<table class="tableau_pronostics">
    <tr>
        <th colspan="3" style="font-size:14px">
            <?=$titre?>
        </th>
    </tr>
    <?=$entetes?>

<?php
$i = 0;
foreach($rows as $key => $row) {
    ++$i;
    $color = $i <= 3 ? 'rgb(46,176,58)' : 'rgb(247,180,0)'; ?>
    <tr>
        <td class="center" style="width:10%;">
            <div class="mise" style="background-color:<?=$color?>;"><?=$i?></div>
        </td>
        <td style="width:70%;">
            <a class="none" href="/tipser-stats/?&id=<?=$row->author_id?>"><?=$row->author_usernicename?></a>
        </td>
        <td style="width:20%;"><?=TipsFormatter::prefixSign($row->Gain)?></td>
    </tr>
<?php
}
if (count($rows) == 0) {
    ?>
    <tr>
        <td class="center" colspan="3">Pas encore de classement pour ce mois ci</td>
    </tr>
<?php
} ?>
</table>
<?=$after_widget?>
