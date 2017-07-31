<?php
// ---------- DAO ----------
class PronosticsDAO {
  // Get one experts pronostic
  // static function getOnePronosticExperts($tips_id) {
  //   global $wpdb;
  //   $table_tips_experts = $wpdb->prefix.'bmk_tips_experts';
  //   $tips_id = (int)$tips_id;
  //   return $wpdb->get_row("SELECT t.* FROM $table_tips_experts t WHERE id = $tips_id");
  // }

  // Get one simple pronostic
  // static function getOnePronostic($tips_id) {
  //     trigger_error("Deprecated function called: PronosticsDAO::getOnePronostic.", E_USER_NOTICE);
  //   global $wpdb;
  //   return pods( 'tips', $tips_id );
  //   // $table_tips = $wpdb->prefix.'bmk_tips';
  //   // return $wpdb->get_row(sprintf("SELECT t.* FROM %s t WHERE tips_id = %d", $table_tips, (int)$tips_id));
  // }



  // Get experts pronostics
  // static function getPronosticsExpertsWith($debut, $taille) {
  //   global $wpdb, $table_tips_experts;
  //   $nb_non_corrige = $wpdb->get_var("SELECT COUNT(*) FROM $table_tips_experts t WHERE t.tips_resultat = 0");
  //   $debut  = $debut;
  //   $taille = $taille == 0 ? max(2,$nb_non_corrige-2) : $taille;
  //   $taille = $taille + ($taille%2);
  //   $sqlreq = "(SELECT t.*, IF(t.tips_resultat=0,0,1) AS 'corrige' FROM ".$table_tips_experts." t WHERE t.tips_resultat = 0 ORDER BY tips_created_at DESC)
  //               UNION
  //               (SELECT t.*, IF(t.tips_resultat=0,0,1) AS 'corrige' FROM ".$table_tips_experts." t WHERE t.tips_resultat != 0 ORDER BY tips_created_at DESC)
  //               ORDER BY corrige ASC, tips_date DESC, tips_heure DESC LIMIT ".$taille." OFFSET ".$debut;
  //
  //   return $wpdb->get_results($sqlreq);
  //
  // }

  // Get author
  // static function getAuteur($user_id) {
  //   global $wpdb, $table_user;
  //   $user_id = (int)$user_id;
  //   return $wpdb->get_row("SELECT t.* FROM $table_user t WHERE t.ID = $user_id");
  // }

  // Get all experts pronostics
  static function getAllPronosticsExpertsWithUser($cond_str, $limit_str, $table) {
      trigger_error('getAllPronosticsExpertsWithUser deprecated',E_USER_NOTICE);
    global $wpdb, $table_user;
    $sort_order = 'DESC';
    if(!empty($limit_str) && false === stripos($limit_str, 'LIMIT')) $limit_str = "LIMIT $limit_str";
    if($limit_str == 0) $limit_str = '';
    if(isset($cond_str) && !empty($cond_str)) $cond_str = "WHERE $cond_str";
    $sql = "SELECT t.*, u.display_name AS 'nom_tipser' FROM $table t ";
    $sql.= "JOIN $table_user u ON u.ID = t.user_id $cond_str ORDER BY t.tips_date $sort_order, t.tips_heure $sort_order $limit_str";
    return $wpdb->get_results($sql);
  }


}
