<div>
    <div class="stat_expert" style="width:200px;">
        <span class="stat_title">Mises totales</span>
        <br/>
        <span class="stat_value"><?=$sExpM->mises_all?></span>
    </div>
    <div class="stat_expert" style="width:200px;">
        <span class="stat_title">Profit</span>
        <br/>
        <span class="stat_value"><?=TipsFormatter::formatCurrency($sExpM->profit)?>
        </span>
    </div>
    <div class="stat_expert" style="width:195px;border-width:1px;">
        <span class="stat_title">Yield</span>
        <br/>
        <span class="stat_value"><?=Calculator::Yield($sExpM->mises, $sExpM->profit)?>%</span>
    </div>
</div>
