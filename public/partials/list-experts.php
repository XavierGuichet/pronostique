<?php
if ($experts->total() > 0) {
    while ($experts->fetch()) {
        $groups = UsersGroup::getUserGroups($experts->field('user_id'));
        $is_expert = in_array(UsersGroup::GROUP_EXPERTS, $groups);
        $is_retired = in_array(UsersGroup::GROUP_RETIRED_EXPERTS, $groups);
        if ($is_expert && $limit == 'inactif') {
            continue;
        }
        if ($is_retired && $limit == 'actif') {
            continue;
        }

        $yield = TipsFormatter::prefixSign(Calculator::Yield($experts->field('mises'), $experts->field('gain')));
        $href_expert = '/tipser-stats/?id='.$experts->field('user_id');
        $profit = TipsFormatter::prefixSign($experts->field('gain') ? $experts->field('gain') : 0);
        $profit_class = TipsFormatter::valeur2CSS($experts->field('gain'));
        if ($is_expert) {
            ?>
            <p class="bloc_expert">
                <a href="<?=$href_expert?>"><?=$experts->field('username')?></a>
                <span class="<?=$profit_class?>"><?=$profit?> Unités</span> <br />
                <span class="subtitle">Yield : <?=$yield?>%</span>
                <hr/>
            </p>
        <?php
        }
        if ($is_retired) {
            ?>
            <p>
                <?=$experts->field('username')?>
                <span class="<?=$profit_class?>"><?=$profit?> Unités</span>
            </p>
            <?php
        }
    }
} else {
    $out .= '<em>Aucuns experts...</em>';
}
