<table class="tableau_pronostics">
    <tr>
        <th colspan="3" style="font-size:14px">
            <?=$titre?>
        </th>
    </tr>
    <?=$entetes?>

<?php
$pos = 1;
$last_hot_streak = '';
foreach($row as $i => $user_hot_streak) {
    $current_hot_streak_str = $user_hot_streak['V']."-".$user_hot_streak['N']."-".$user_hot_streak['P'];
    if($last_hot_streak != $current_hot_streak_str) {
        $last_hot_streak = $current_hot_streak_str;
        $pos = $i + 1;
    }
    $color = $pos <= 3 ? 'rgb(46,176,58)' : 'rgb(247,180,0)';
    ?>

    <tr>
        <td class="center" style="width:10%;">
            <div class="mise" style="background-color:<?=$color?>;"><?=($pos)?></div>
        </td>
        <td style="width:70%;">
            <a class="none" href="/tipser-stats/?&id=<?=$user_hot_streak['user_id']?>"><?=$user_hot_streak['display_name']?></a>
        </td>
        <td style="width:20%;">
            <span style="color:green;"><?=$user_hot_streak['V']?></span>&nbsp;-&nbsp;<span style="color:orange;"><?=$user_hot_streak['N']?></span>&nbsp;-&nbsp;<span style="color:red;"><?=$user_hot_streak['P']?></span>
        </td>
    </tr>
<?php
}
?>
</table>
