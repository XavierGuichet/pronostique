<?=$before_widget?>
<?=$widget_title?>
<table class="tableau_pronostics">
    <tr>
        <th colspan="4" style="font-size:14px">
            <?=$titre?>
        </th>
    </tr>
    <?=$entetes?>
<?php
$i = 0;
foreach($row as $user_stat) {
    ++$i;
    $color = $i <= 3 ? 'rgb(46,176,58)' : 'rgb(247,180,0)'; ?>
    <tr>
        <td class="center" style="width:10%;">
            <div class="mise" style="background-color:<?=$color?>;"><?=$i?></div>
        </td>
        <td style="width:70%;">
            <a class="none" href="/tipser-stats/?id=<?=$user_stat->user_id?>"><?=$user_stat->name?></a>
        </td>
        <td style="width:70%;">
            <?=$user_stat->yield?>%
        </td>
        <td style="width:20%;"><?=TipsFormatter::prefixSign($user_stat->gain)?></td>
    </tr>
<?php
}
?>
</table>
<?=$after_widget?>
