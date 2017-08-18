<?php
if ( $results !== null ) {
    if ($results) { ?>
        <div class="updated"><p><strong>Mise à jour des paramètres réussie</strong></p></div>
    <?php } else { ?>
        <div class="error"><p><strong>Echec de la mise à jour des paramètres</strong></p></div>
    <?php }
}
?>
<div class="wrap">
    <h1>Gestion des pronostics</h1><br/>

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
    // $(document).ready( function() {
    //     $("#expert_migrate").trigger('click');
    // });
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
