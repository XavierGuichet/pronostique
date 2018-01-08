<div class="prono-tax-nav" id="prono-tax-nav">
<?php
foreach($taxonomies_by_sport as $key => $sport_group) {
?>
    <ul class="prono-tax-nav_sport-list">
        <div class="prono-tax-nav_sport-list_header">
            <a href="<?=$sport_group['sport']['permalink']?>"><?=$sport_group['sport']['name']?></a>
        </div>
        <?php if(count($sport_group['competitions']) > 0) { ?>
            <ul class="prono-tax-nav_sport-list_list">
                <?php
                foreach($sport_group['competitions'] as $competition) {
                ?>
                    <li class="prono-tax-nav_sport-list_list_item">
                        <a href="<?=$competition['permalink']?>"><span class="flag-icon flag-icon-<?=$competition['country-iso']?>"></span> <?=$competition['name']?></a>
                    </li>
                <?php } ?>
            </ul>
        <?php } ?>
    </ul>
<?php
} ?>
</div>
