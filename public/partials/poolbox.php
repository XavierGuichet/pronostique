<?php
while ($pronos->fetch()) {
?>
    <div>
        <span class="pickside"><i class="fa <?=Formatter::resultat2str($pronos->field('resultat'))?>" aria-hidden="true"></i> <?=mb_strimwidth(stripslashes($pronos->field('name')), 0, 30, '...')?></span>
        <span class="picktitleside2"><?=substr($pronos->field('code_poolbox'), 0, 10)?> <?=number_format($pronos->field('cote'),2,'.','')?></span>
    </div>
<?php } ?>
