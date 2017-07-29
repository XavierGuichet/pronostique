<span class="editorsprofit">
    Profit de nos experts: <span><?=Formatter::formatCurrency($stats->field('gain'), 'unités', true);?></span><br>
    Paris gagnés: <span class="editorsgreen"><?=$stats->field('V')?></span> | Paris perdus: <span class="editorsred"><?=$stats->field('P')?></span><br>
</span>
