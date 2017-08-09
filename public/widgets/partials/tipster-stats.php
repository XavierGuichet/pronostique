<?=$before_widget?>
<?=$widget_title?>
<div class="stats_tipster_side">
    <div class="stat_tipster_side">
        <span>Tips publiés</span> <span class="stat_value"><?=$stats->field('nb_total_tips')?></span>
    </div>
    <div class="stat_tipster_side">
        <span>Tips gagnés</span> <span class="stat_value <?=Formatter::valeur2CSS(1)?>"><?=$stats->field('V')?></span>
    </div>
    <div class="stat_tipster_side">
        <span>Tips perdus</span> <span class="stat_value <?=Formatter::valeur2CSS(-1)?>"><?=$stats->field('P')?></span>
    </div>
    <div class="stat_tipster_side">
        <span>Tips remb.</span> <span class="stat_value nul"><?=$stats->field('N')?></span>
    </div>
    <div class="stat_tipster_side">
        <span>Profit total</span> <span class="stat_value <?=Formatter::valeur2CSS($stats->field('gain'))?>"><?=$stats->field('gain')?></span>
    </div>
    <div class="stat_tipster_side">
        <span>Ce mois</span> <span class="stat_value <?=Formatter::valeur2CSS($month_profit)?>"><?=$month_profit?></span>
    </div>
    <div class="stat_tipster_side">
        <span>Yield total</span> <span class="stat_value <?=Formatter::valeur2CSS($yield)?>"><?=$yield?>%</span>
    </div>
    <div class="clearfix"><br/></div>
</div>
<?=$after_widget?>
