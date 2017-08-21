<?php
if ($experts->total() > 0) {
    while ($experts->fetch()) {
        $is_expert = UsersGroup::isUserInGroup($experts->field('user_id'), UsersGroup::GROUP_EXPERTS);
        if ($is_expert && $limit == 'inactif') {
            continue;
        }

        $is_retired = UsersGroup::isUserInGroup($experts->field('user_id'), UsersGroup::GROUP_RETIRED_EXPERTS);
        if ($is_retired && $limit == 'actif') {
            continue;
        }
        $yield = Formatter::prefixSign(Calculator::Yield($experts->field('mises'), $experts->field('gain')));
        $href_expert = '/bilan-expert/?id='.$experts->field('user_id');
        $profit = Formatter::prefixSign($experts->field('gain') ? $experts->field('gain') : 0);
        $profit_class = Formatter::valeur2CSS($experts->field('gain'));
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
