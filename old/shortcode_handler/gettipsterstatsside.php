<?php

// Affiche les stats du user sur la sidebar pour les pronostics tipsters
// La condition d'affichage est gérée par le plugin widget_logic, dans l'admin des widget
// TODO: Transform me in a widget
function getTipsterStatsSide() {
  global $post;
  $post_author = $post->post_author;
  return getStatsTipster($post_author);
}
