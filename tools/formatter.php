<?php

class Formatter {
  static function formatCurrency($val, $unit = 'unités', $is_colored = false) {
    $prefix = $val >= 0 ? ($val > 0 ? '+' : '') : '-';
    $absval = abs($val);
    $out = "$prefix $absval $unit";
    if($is_colored) {
      $class = $val >= 0 ? ($val > 0 ? 'gagne' : 'nul') : 'perdu';
      $out = "<span class=\"$class\"><strong>$out</strong></span>";
    }
    return $out;
  }

  // Prefixe la valeur par son signe
  static function prefixSign($val) {
    if(preg_match('/^\s*-/',$val)) return $val;
    return ($val >= 0 ? '+' : '-') . $val;
  }

  // Retourne la classe CSS selon positif/negatif
  static function valeur2CSS($val) {
    return $val >= 0 ? 'gagne' : 'perdu';
  }

  // Retourne la classe CSS selon positif/negatif
  static function resultat2CSS($result) {
    switch($result) {
    case 1: return 'gagne';
    case 2: return 'perdu';
    case 3: return 'nul';
    default:return 'perdu';
    }
  }

  // Retourne une chaine decrivant le resultat
  static function resultat2str($resultat) {
      $font_awesome_icon_class = array(
          "1" => "fa-check",
          "2" => "fa-times",
          "3" => "fa-exchange"
      );
    return array_key_exists($resultat, $font_awesome_icon_class) ? $font_awesome_icon_class[$resultat] : 'fa-circle';
  }

  // Retourne une chaine decrivant la mise
  static function getMiseColorClass($mise) {
      if($mise <= 3) {
          return "green";
      }
      if($mise <= 6) {
          return "orange";
      }
      return "red";
  }

  // Retourne l'URL d'un fichier uploade
  static function uploadedLink($filename, $date = '2012/02')
  {
      $upload_dir = wp_upload_dir($date);

      return sprintf('%s/%s', $upload_dir['url'], $filename);
  }

  static function getHTMLLinkBookee($bookmaker)
  {
      return sprintf('<a href="%s" title="%s" rel="nofollow" target="_blank">%s</a>', $bookmaker['link'], $bookmaker['name'], $bookmaker['name']);
  }

  static function getLinkTipseurStats($user_id, $user_name)
  {
      return sprintf('<a href="/tipser-stats/?id=%s">%s</a>', $user_id, stripslashes($user_name));
  }
}
