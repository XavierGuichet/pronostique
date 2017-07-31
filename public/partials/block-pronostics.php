<?php
$MAX_ANALYSE = 195; // en caractères
$classDiv = ($direction === "row") ? 'imgtxta' : 'imgtxtad';
while ( $all_tips->fetch() ) {
    $match = mb_strimwidth(stripslashes($all_tips->display('name')), 0, 45, '...');
    // $href_details = get_permalink($tips->tips_post_id);
    $href_details = $all_tips->display('permalink');
    $analyse = strip_tags(stripslashes($all_tips->display('analyse')));
    $resume = substr($analyse, 0, $MAX_ANALYSE).(strlen($analyse) > $MAX_ANALYSE ? '&hellip;' : '');
    $url_miniature = (null !== $all_tips->display('miniature') && !empty($all_tips->display('miniature'))) ? $all_tips->display('miniature') : '/wp-content/uploads/2012/02/va-300x164.jpg';
    $miniature_html = sprintf('<img src="%s" title="%s" width="105" height="59" />', $url_miniature, $all_tips->display('name'));
    $isTipsVisible = ($isUserAdherent || !$all_tips->field('is_vip'));
    if($isTipsVisible) {
?>
    <div class="<?=$classDiv?>">
        <span class="articleimg">
            <?=$miniature_html?>
            <span class="artit"><a href="/pronostique/<?=$href_details?>"><?=$match?></a></span>
            <span class="articletxt2"><?=$resume?></span>
        </span>
        <span class="articleauth">Par <?=$all_tips->field('author.user_nicename')?> | <?=date_i18n("d F Y", strtotime($all_tips->field('created')))?></span>
    </div>
<?php } } ?>
