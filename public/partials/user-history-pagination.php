<?php
setlocale(LC_TIME, "fr_FR");
while ($months_with_nb_pari->fetch()) {
    $text_month = strftime('%b-%y',strtotime($months_with_nb_pari->field('month')));
    // if(date_format($today,'Y-m') < $date_debut) break;
    //   $style = date_format($today,'Y-m') == $date ? 'text-decoration:none;color:black;font-weight:bold;' : '';
    if($currentmonth === $months_with_nb_pari->field('month')) {
        ?>
        <span><?=$text_month?></span>
        <?php
    } else {
      ?>
      <!-- sprintf('', $style,$uid,urlencode(date_format($today,'Y-m')),$i); -->
      <a style="%s" href="/tipser-stats/?id=<?=$user_id?>&mois=<?=$months_with_nb_pari->field('month')?>"><?=$text_month?></a>
      <!-- date_sub($today, DateInterval::createFromDateString('1 month')); -->
      <?php
  }
}

?>
