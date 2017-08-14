<?php
$MAX_ANALYSE = 195; // en caractères
while ($all_tips->fetch()) {
    $match = mb_strimwidth(stripslashes($all_tips->display('name')), 0, 45, '...');
    // $href_details = get_permalink($tips->tips_post_id);
    $href_details = $all_tips->display('permalink');
    $link = '/pronostique/'.$all_tips->field('permalink');
    if ($all_tips->field('post.ID') != 0) {
        $link = get_permalink($all_tips->field('post.ID'));
    }
    $analyse = strip_tags(stripslashes($all_tips->display('analyse')));
    $resume = substr($analyse, 0, $MAX_ANALYSE).(strlen($analyse) > $MAX_ANALYSE ? '&hellip;' : '');
    $url_miniature = (null !== $all_tips->display('miniature') && !empty($all_tips->display('miniature'))) ? $all_tips->display('miniature') : '/wp-content/uploads/2012/02/va-300x164.jpg';
    $miniature_html = sprintf('<img src="%s" title="%s" width="105" height="59" />', $url_miniature, $all_tips->display('name'));
    $isTipsVisible = ($isUserAdherent || !$all_tips->field('is_vip'));
    if ($isTipsVisible) {
        ?>
    <div class="home_expert home_expert_<?=$direction?>">
        <a href="<?=$link?>">
            <div class="home_expert_content">
                <?=$miniature_html?>
                <span class="home_expert_title"><?=$match?></span>
                <span class="home_expert_excerpt"><?=$resume?></span>
            </div>
            <span class="home_expert_author">Par <?=$all_tips->field('author.user_nicename')?> | <?=date_i18n('d F Y', strtotime($all_tips->field('created')))?></span>
        </a>
    </div>
<?php
    }
}
if ($more_tips) {
while ($more_tips->fetch()) {
    $match = mb_strimwidth(stripslashes($more_tips->display('name')), 0, 45, '...');
    // $href_details = get_permalink($tips->tips_post_id);
    $href_details = $more_tips->display('permalink');
    $link = '/pronostique/'.$more_tips->field('permalink');
    if ($more_tips->field('post.ID') != 0) {
        $link = get_permalink($more_tips->field('post.ID'));
    }
    $analyse = strip_tags(stripslashes($more_tips->display('analyse')));
    $resume = substr($analyse, 0, $MAX_ANALYSE).(strlen($analyse) > $MAX_ANALYSE ? '&hellip;' : '');
    $url_miniature = (null !== $more_tips->display('miniature') && !empty($more_tips->display('miniature'))) ? $more_tips->display('miniature') : '/wp-content/uploads/2012/02/va-300x164.jpg';
    $miniature_html = sprintf('<img src="%s" title="%s" width="105" height="59" />', $url_miniature, $more_tips->display('name'));
    $isTipsVisible = ($isUserAdherent || !$more_tips->field('is_vip'));
    if ($isTipsVisible) {
        ?>
    <div class="home_expert home_expert_<?=$direction?>">
        <a href="<?=$link?>">
            <div class="home_expert_content">
                <?=$miniature_html?>
                <span class="home_expert_title"><?=$match?></span>
                <span class="home_expert_excerpt"><?=$resume?></span>
            </div>
            <span class="home_expert_author">Par <?=$more_tips->field('author.user_nicename')?> | <?=date_i18n('d F Y', strtotime($more_tips->field('created')))?></span>
        </a>
    </div>
<?php
    }
}
} ?>
