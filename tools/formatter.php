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

  static function formatDateNice($date, $heure, $format) {
    setlocale(LC_TIME,'fr_FR');
    $dt = date_create($date.'T'.str_replace('h',':',$heure).':00');
    return strftime($format, $dt->getTimestamp());
  }

  static function formatSport($sport) {
    $sport2img = array(
      '' => '',
      '' => '',
    );

    $href = isset($sport2img[$sport]) ? $sport2img[$sport] : $default_img;
    return sprintf('<img src="%s" alt="%s" title="%s" />', $href, $sport, $sport);
  }

  static function sanitizeTitleFrench($str) {
    //$str = html_entity_decode($str, ENT_QUOTES, 'utf-8');
    $str = str_replace( '.', '-', $str );
    $str = preg_replace( '~--+~', '-', $str );
    $str = remove_accents($str);
    $str = normalizer_normalize($str);
    $str = sanitize_title_with_dashes($str);
    return $str;
  }
  static function generateLinkForTips($tips, $is_tips_expert) {
    $tips = (object)$tips;
    $link = '';
    if(isset($tips->tips_match)) {
      if($is_tips_expert) {
        $link = self::sanitizeTitleFrench(sprintf('pronostic-%s', $tips->tips_match));
      } else {
        $id = isset($tips->tips_post_id) ? $tips->tips_post_id : 9999;
        $link = self::sanitizeTitleFrench(sprintf('pronostic-%s-%d', $tips->tips_match, $id));
      }
    }
    return $link;
  }

  static function generateTitleForTips($tips, $is_tips_expert) {
    $t = (object)$tips;
    if($is_tips_expert) {
      $str= '// ' . self::formatDateNice($t->tips_date,$t->tips_heure,'%A %d/%m %H:%M');
    } else {
      $user = get_userdata($t->user_id);
      $str = sprintf('par %s', self::getLinkTipseurStats($t->user_id, $user->display_name));
    }
    return sprintf('Pronostic %s %s', $t->tips_match, $str);
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
    $style = 'color:white;border-radius:2px;padding:2px;';
    $link  = '<img src="%s" />';
    $res2str = array(
      '1' => sprintf($link, self::uploadedLink('won.png')), //'<div style="background-color:green;'.$style.'">G</div>',
      '2' => sprintf($link, self::uploadedLink('icon-lost.png')), //'<div style="background-color:red;'.$style.'">P</div>',
      '3' => sprintf($link, self::uploadedLink('push1.gif')), //'<div style="background-color:orange;'.$style.'">N</div>',
    );
    return array_key_exists($resultat, $res2str) ? $res2str[$resultat] : sprintf($link, self::uploadedLink('icon-reste.png', '2012/11'));
  }

  // Retourne une chaine decrivant la mise
  static function mise2str($mise) {
    $mise = (int)$mise;
    $style = '';
    $misestr = '<div class="mise" style="background-color:%s;%s">%d</div>';
    $ccol = array();
    $ccol[1] = $ccol[2] = $ccol[3] = 'green';
    $ccol[4] = $ccol[5] = $ccol[6] = $ccol[7] = 'orange';
    $ccol[8] = $ccol[9] = $ccol[10]= 'red';
    return sprintf($misestr, $ccol[$mise], $style, $mise);
  }

  // Date US->FR
  static function dateUS2FR($dateUS) {
    $dus = explode('-', $dateUS);
    return sprintf('%s/%s/%s', $dus[2], $dus[1], $dus[0]);
  }

  static function dateUS2FRNice($dateUS) {
    setlocale(LC_TIME,'fr_FR');
    $dt = date_create($dateUS);
    return strftime('%d %B %Hh%M',$dt->getTimestamp());
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

  static function getLinkTipsStats($tips_post_id, $tips_match, $tips_id)
  {
      $href = $tips_post_id ? get_permalink($tips_post_id) : sprintf('/tips-stats/?id=%d', $tips_id);
      $comments_count = get_comment_count($tips_post_id);
      $nb_comments = $comments_count ? $comments_count['approved'] : '';

      return sprintf('<a class="simplelink" href="%s">%s</a> <span class="tips-comm-number">%s</span>', $href, stripslashes($tips_match), $nb_comments);
  }
}
