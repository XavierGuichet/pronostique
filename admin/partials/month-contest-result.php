<h1>Resultat du concours du mois de : <?=$month?></h1>
<form method="post" action="<?=$formaction?>">
    <?=$formnonce?>
    <label>Changer de mois :</label>
    <select name="month">
        <option value="1">Janvier</option>
        <option value="2">Février</option>
        <option value="3">Mars</option>
        <option value="4">Avril</option>
        <option value="5">Mai</option>
        <option value="6">Juin</option>
        <option value="7">Juillet</option>
        <option value="8">Août</option>
        <option value="9">Septembre</option>
        <option value="10">Octobre</option>
        <option value="11">Novembre</option>
        <option value="12">Décemebre</option>
    </select>
    <select name="year">
        <?php
        for($i = Date('Y'); $i >= 2012; $i--) {
            echo '<option value="'.$i.'">'.$i.'</option>';
        }
         ?>
    </select>
    <input type="submit" name="change_month" value="Valider" />
</form>
<table class="tableau_pronostics" border="1">
    <tr>
        <th colspan="5" style="font-size:14px">
            <?=$titre?>
        </th>
    </tr>
    <?=$entetes?>

<?php
$i = 0;
while ($row->fetch()) {
    if($row->field('nb_tips') < 10) {
        continue;
    }
    $color = $i < 10 ? 'rgb(46,176,58)' : 'rgb(247,180,0)';
    ++$i;
    ?>
    <tr>
        <td class="center" style="width:10%;">
            <div class="mise" style="background-color:<?=$color?>;"><?=$i?></div>
        </td>
        <td style="width:50%;">
            <a class="none" href="/tipser-stats/?&id=<?=$row->display('author.ID')?>"><?=$row->display('author.user_nicename')?></a>
        </td>
        <td style="width:25%;"><?=$row->display('V')?> - <?=$row->display('P')?> - <?=$row->display('N')?></td>
        <td style="width:10%;"><?=$row->display('nb_tips')?></td>
        <td style="width:15%;"><?=Formatter::prefixSign($row->display('Gain'))?></td>
    </tr>
<?php
}
?>
<?php
$i = 0;
$row->reset();
while ($row->fetch()) {
    if($row->field('nb_tips') >= 9) {
        continue;
    }
    ?>
    <tr>
        <td class="center" style="width:10%;">
            <div class="mise" style="background-color: grey;">-</div>
        </td>
        <td style="width:50%;">
            <a class="none" href="/tipser-stats/?&id=<?=$row->display('author.ID')?>"><?=$row->display('author.user_nicename')?></a>
        </td>
        <td style="width:25%;"><?=$row->display('V')?> - <?=$row->display('P')?> - <?=$row->display('N')?></td>
        <td style="width:10%;"><?=$row->display('nb_tips')?></td>
        <td style="width:15%;"><?=Formatter::prefixSign($row->display('Gain'))?></td>
    </tr>
<?php
}
if ($row->total() == 0) {
    ?>
    <tr>
        <td class="center" colspan="5">Pas de resultat pour ce mois</td>
    </tr>
<?php
} ?>
</table>
