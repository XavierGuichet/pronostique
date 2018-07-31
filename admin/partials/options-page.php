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
    <hr/>
    <h2>Archivage des pronostic</h2>
    <p>Actuellement <em><?=$archive_tips->total_found()?></em> pronostics sont archivés</p>
    <form method="post" action="<?=$formaction?>">
      <?=$formnonce_archive?>
      <label>Avant le :</label>
      <input type="datetime" class="datepicker" name="archive_before" value="<?=$archive_before?>">
      <input class="button button-primary button-large" type="submit" name="calc_nb_archive" value="Calculer le nb de pronos" />
      <?php
      if($archive_before) {
        ?>
        <div style="border: 2px solid red; max-width: 50%; margin: 1rem 0; padding: 1rem;">
          <p>Il y a <?=$to_archive_tips->total_found()?> pronostics non-archivé avant le <?=$archive_before?></p>
          <input class="button button-alert button-large" type="submit" name="do_archive" value="Archiver ces pronos" />
        </div>
      <?php
      }
      ?>
    </form>

</div>
