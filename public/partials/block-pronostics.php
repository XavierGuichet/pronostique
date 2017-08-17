<?php
$MAX_ANALYSE = 195; // en caractères
foreach($tips as $tip) {
    $match = mb_strimwidth(stripslashes($tip->name), 0, 45, '...');
    $link = '/pronostique/'.$tip->permalink;
    if ($tip->post_id != 0) {
        $link = get_permalink($tip->post_id);
    } else {
        trigger_error('Prono without post', E_USER_ERROR);
    }
    $analyse = strip_tags(stripslashes($tip->analyse));
    $resume = substr($analyse, 0, $MAX_ANALYSE).(strlen($analyse) > $MAX_ANALYSE ? '&hellip;' : '');
    $miniature_html = sprintf('<img src="/wp-content/uploads/2012/02/va-300x164.jpg" title="%s" width="105" height="59" />',  $tip->name);
    if($tip->image_id) {
        $miniature_html = pods_image($tip->image_id, 'thumbnail',0, array('title' => $tip->name));
    }
    $isTipsVisible = ($isUserAdherent || !$tip->is_vip);

    if ($isTipsVisible) {
?>
    <div class="home_expert home_expert_<?=$direction?>">
        <a href="<?=$link?>">
            <div class="home_expert_content">
                <?=$miniature_html?>
                <span class="home_expert_title"><?=$match?></span>
                <span class="home_expert_excerpt"><?=$resume?></span>
            </div>
            <span class="home_expert_author">Par <?=$tip->tipster_nicename?> | <?=date_i18n('d F Y', strtotime($tip->created))?></span>
        </a>
    </div>
<?php
    }
}
 ?>
