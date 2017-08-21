<?php
if ( $formresult !== null ) {
    if ($formresult) { ?>
        <div class="updated"><p><strong>Mise à jour des paramètres réussie</strong></p></div>
    <?php } else { ?>
        <div class="error"><p><strong>Echec de la mise à jour des paramètres</strong></p></div>
    <?php }
}
?>
<div class="wrap">
    <h1>Gestion des pronostics</h1><br/>
    <h2>Gestion des catégories par défaut des pronostics</h2>
    <form method="post" action="<?=$formaction?>">
        <?=$formnonce_default_cat?>
        <div>
            <label>Catégorie expert</label>
            <select autocomplete="off" name="prono_expert_default_cat">
                <?php
                foreach($categories as $category) {
                    $selected = '';
                    if($category->term_id == $prono_expert_cat) {
                        $selected = 'selected';
                    }
                    echo '<option value="'.$category->term_id.'" '.$selected.'>'.$category->name.'</option>';
                }
                ?>
            </select>
        </div>
        <div>
            <label>Catégorie VIP</label>
            <select autocomplete="off" name="prono_vip_default_cat">
                <?php
                foreach($categories as $category) {
                    $selected = '';
                    if($category->term_id == $prono_vip_cat) {
                        $selected = 'selected';
                    }
                    echo '<option value="'.$category->term_id.'" '.$selected.'>'.$category->name.'</option>';
                }
                ?>
            </select>
        </div>
        <input type="submit" name="set_default_cat" value="Valider" />
    </form>
    <h2>Migration des pronostique (tipseur) DB-Toolkit vers Pods</h2>
    <p>Reste à migrer : <b><?=$count_std_tips_to_migrate?></b> tips</p>
    <form method="post" action="<?=$formaction?>">
        <?=$formnonce_migrate?>
        <div class="submit">
            <input type="submit" name="migrate_std_tips"  id="std_migrate" value="<?=$formsubmit_migrate?>" />
        </div>
    </form>

    <h2>Migration des pronostique (expert) DB-Toolkit vers Pods</h2>
    <p>Reste à migrer : <b><?=$count_expert_tips_to_migrate?></b> tips expert</p>
    <form method="post" action="<?=$formaction?>">
        <?=$formnonce_migrate?>
        <div class="submit">
            <input type="submit" name="migrate_expert_tips" id="expert_migrate" value="<?=$formsubmit_migrate?>" />
        </div>
    </form>
</div>
<?php
if( $count_expert_tips_to_migrate > 0) {
    ?>
<script>
(function( $ ) {
    'use strict';
    $(document).ready( function() {
        $("#expert_migrate").trigger('click');
    });
})( jQuery );
</script>
<?php } elseif( $count_std_tips_to_migrate > 0) {?>
<script>
(function( $ ) {
    'use strict';
    // $(document).ready( function() {
    //     $("#std_migrate").trigger('click');
    // });
})( jQuery );
</script>
<?php } ?>
