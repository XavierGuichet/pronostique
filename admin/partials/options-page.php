<?php
if ( $formresult !== null ) {
    if ($formresult) { ?>
        <div class="updated"><p><strong>Réussite</strong></p></div>
    <?php } else { ?>
        <div class="error"><p><strong>Echec</strong></p></div>
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
        <div>
            <label>Catégorie Standard</label>
            <select autocomplete="off" name="prono_std_default_cat">
                <?php
                foreach($categories as $category) {
                    $selected = '';
                    if($category->term_id == $prono_std_cat) {
                        $selected = 'selected';
                    }
                    echo '<option value="'.$category->term_id.'" '.$selected.'>'.$category->name.'</option>';
                }
                ?>
            </select>
        </div>
        <input class="button button-primary button-large" type="submit" name="set_default_cat" value="Valider" />
    </form>
    <h2>Gestion du cache du module pronostic</h2>
    <form method="post" action="<?=$formaction?>">
      <?=$formnonce_cache_handling?>
      <input class="button button-primary button-large" type="submit" name="clear_cache" value="Vider le cache" />
      <input class="button button-primary button-large" type="submit" name="refresh_filter_cache" value="Rafraichir le cache des filtres" />
      <input class="button button-primary button-large" type="submit" name="refresh_tops_cache" value="Rafraichir tout les classements du cache" />

    </form>
</div>
