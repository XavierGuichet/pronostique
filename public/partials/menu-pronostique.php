<?php
if (is_user_logged_in()) {
    foreach($links as $link) {
        ?>
            <a class="lien_menu_tips" href="<?=$link['href']?>"><?=$link['title']?></a>
        <?php
    }
?>

<?php
} else {
?>
<a class="lien_menu_tips" href="#">S'enregistrer comme tipster</a><p style="margin-top:5px;font-size:1.1em;text-align:center">Pour postuler au concours, veuillez d'abord vous <a style="text-decoration:underline" href="/wp-signup.php">inscrire sur Conseils Paris Sportifs.</a></p>
<?php
}
 ?>
