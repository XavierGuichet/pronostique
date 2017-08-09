<div class="stats_expert_container">
<div class="stat_expert">
    <span class="stat_title">Profit total</span><br/>
    <span class="stat_value <?=Formatter::valeur2CSS($stats->field('gain'))?>"><?=$stats->field('gain')?></span>
</div>
<?php
if (!$hide_month_profit) {
    ?>
<div class="stat_expert">
    <span class="stat_title">Ce mois</span><br/>
    <span class="stat_value <?=Formatter::valeur2CSS($month_profit)?>"><?=$month_profit?></span>
</div>
<?php } ?>
<div class="stat_expert">
    <span class="stat_title">Tips publiés</span><br/>
    <span class="stat_value"><?=$stats->field('nb_total_tips')?></span>
</div>
<div class="stat_expert">
    <span class="stat_title">Tips gagnés</span><br/>
    <span class="stat_value <?=Formatter::valeur2CSS(1)?>"><?=$stats->field('V')?></span>
</div>
<div class="stat_expert">
    <span class="stat_title">Tips perdus</span><br/>
    <span class="stat_value <?=Formatter::valeur2CSS(-1)?>"><?=$stats->field('P')?></span>
</div>
<div class="stat_expert">
    <span class="stat_title">Tips remb</span><br/>
    <span class="stat_value nul"><?=$stats->field('N')?></span>
</div>
<div class="stat_expert">
    <span class="stat_title">Yield total</span><br/>
    <span class="stat_value <?=Formatter::valeur2CSS($yield)?>"><?=$yield?></span>
</div>
</div>
