<ul class="history-pagination__list">
    <?php
    if ($index_current_month > 0) {
        echo '<li><a href="/tipser-stats/?id='.$user_id.'&mois='.$months_list[$index_current_month - 1]['month'].'"><i class="fa fa-chevron-left" aria-hidden="true"></i></a></li>';
    }
    $last_distance_result = true;
    //when current_month is in the middle of the list we display less month on edge
    $wanted_distance = ($index_current_month < 2 || abs(count($months_list) - $index_current_month) < 2) ? 4 : 2;
    foreach ($months_list as $key => $month) {
        //near start || near current || near end
        $distance = $key < $wanted_distance || abs($key - $index_current_month) < $wanted_distance || abs(count($months_list) - $key) <= $wanted_distance;
        $text_month = date_i18n("M y", strtotime($month['month']));
        if ($distance) {
            if($key === $index_current_month) {
                echo '<li><span class="active">'.$text_month.'</span></li>';
            }
            else {
                echo '<li><a href="/tipser-stats/?id='.$user_id.'&mois='.$month['month'].'">'.$text_month.'</a></li>';
            }
        } else if($last_distance_result) {
            echo "<li><span>&hellip;</span></li>";
        }
        $last_distance_result = $distance;
    }
    if ($index_current_month < count($months_list) - 1) {
        echo '<li><a href="/tipser-stats/?id='.$user_id.'&mois='.$months_list[$index_current_month + 1]['month'].'"><i class="fa fa-chevron-right" aria-hidden="true"></i></a></li>';
    }
    ?>
</ul>
