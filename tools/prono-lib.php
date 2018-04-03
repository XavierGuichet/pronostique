<?php

class PronoLib {

    private static $_instance;
    private static $xdkdcache;

    /**
     * Empêche la création externe d'instances.
     */
    private function __construct () {}

    /**
     * Empêche la copie externe de l'instance.
     */
    private function __clone () {}

    /**
     * Renvoi de l'instance et initialisation si nécessaire.
     */
    public static function getInstance () {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
            self::$xdkdcache = new XdkdCache();
        }

        return self::$_instance;
    }

    public function getListTopData($of_the_month = true, $max = 10) {
      $data_cache_file_timespan_name = $of_the_month == 'true' ? 'month' : 'global';
      $data_cache_name = 'top-tipsters-'.$data_cache_file_timespan_name."-".$max;

      $cache_token = get_option("cache_".$data_cache_name);
      $data = false;
      if($cache_token) {
        $data = self::$xdkdcache->getCache($cache_token,$data_cache_name);
      }
      if($data === false) {
        $cond_month = $of_the_month == 'true' ? ' AND MONTH(date) = MONTH(NOW()) AND YEAR(date) = YEAR(NOW())' : '';
        $pronos = pods('pronostique')->find(
            array(
                'select' => 'ROUND(SUM( IF(tips_result = 1, (cote-1)*mise, IF(tips_result = 2, - mise, IF(tips_result = 3, 0, 0))) ), 2) AS Gain, author.id as author_id, author.user_nicename as author_usernicename',
                'where' => 'tips_result > 0 AND is_expert = 0 '.$cond_month,
                'limit' => $max,
                'orderby' => 'Gain Desc',
                'groupby' => 'author.id',
            )
            );
        $data = $pronos->data();
        if(!$data) {
          $data = array();
        }
        $cache_token = self::$xdkdcache->writeCache($data_cache_name, $data);
        add_option("cache_".$data_cache_name, $cache_token);
      }
      return $data;
    }

      public function getHotStreakRankingData() {
        $data_cache_name = 'hot-streak-ranking';
        $cache_token = get_option("cache_".$data_cache_name);
        $data = false;
        if($cache_token) {
          $data = self::$xdkdcache->getCache($cache_token,$data_cache_name);
        }
        if($data === false) {
          $tips = pods('pronostique')->find(
                                  array(
                                      'select' => 't.ID, tips_result, author.ID as user_id, author.user_nicename as username',
                                      'limit' => 0,
                                      'where' => 'tips_result > 0 AND is_expert != 1 AND is_vip != 1 AND date BETWEEN (CURDATE() - INTERVAL 365 DAY) AND NOW()',
                                      'orderby' => 'user_id DESC, date DESC',
                                  )
          );

          $res2letter = array(1 => 'V', 2 => 'P', 3 => 'N');
          $hotStreaks_by_uid = array();

          // create array with hotstreak of all tipster
          while ($tips->fetch()) {
              $user_id = $tips->field('user_id');
              if (!isset($hotStreaks_by_uid[$user_id])) {
                  $hotStreaks_by_uid[$user_id] = array(
                                      'V' => 0,
                                      'P' => 0,
                                      'N' => 0,
                                      'tips_count' => 0,
                                      'display_name' => $tips->field('username'),
                                      'user_id' => $user_id,
                                      );
              }
              if ($hotStreaks_by_uid[$user_id]['tips_count'] >= 20) {
                  continue;
              }
              $hotStreaks_by_uid[$user_id]['tips_count'] += 1;
              $letter = $res2letter[$tips->field('tips_result')];
              $hotStreaks_by_uid[$user_id][$letter] += 1;
          }

          // create an array in which value are hot-streak string
          $best_id = array();
          foreach ($hotStreaks_by_uid as $uid => $user_hot_streak) {
              $best_id[$uid] = sprintf('%02d-%02d-%02d', $user_hot_streak['V'], $user_hot_streak['N'], $user_hot_streak['P']);
          }
          // order that array and keep first 25
          arsort($best_id);
          $best_id = array_slice($best_id, 0, 25, true);

          // get hotstreak complete information and add them to the outputed array;
          $data = array();
          foreach ($best_id as $uid => $v2) {
              $data[] = $hotStreaks_by_uid[$uid];
          }
          $cache_token = self::$xdkdcache->writeCache($data_cache_name, $data);
          add_option("cache_".$data_cache_name, $cache_token);
        }

        return $data;
      }

      public function refreshAllData() {
        $data_cache_name = 'hot-streak-ranking';
        $result = self::$xdkdcache->clearFileCache($data_cache_name);
        self::getHotStreakRankingData();
        $data_cache_name = 'top-tipsters-global-25';
        $result &= self::$xdkdcache->clearFileCache($data_cache_name);
        self::getListTopData(false, 25);
        $data_cache_name = 'top-tipsters-month-25';
        $result &= self::$xdkdcache->clearFileCache($data_cache_name);
        self::getListTopData(true, 25);
      }

      public function refreshTaxonomiesFilterData() {
        $data_cache_name = 'pronos-filters';
        $result = self::$xdkdcache->clearFileCache($data_cache_name);
        $data = self::getTaxonomiesFilterData();
        return $result;
      }

    public function getTaxonomiesFilterData() {
      $data_cache_name = 'pronos-filters';
      $cache_token = get_option("cache_".$data_cache_name);
      $taxonomies_by_sport = false;

      if($cache_token) {
        $taxonomies_by_sport = self::$xdkdcache->getCache($cache_token,$data_cache_name);
      }
      if($taxonomies_by_sport === false) {
        $sports = pods( 'sport' )->find(array(
            'select' => ' `t`.name, `tt`.count, `t`.term_id',
            'where' => 'count != 0' ,
            'orderby' => 'tt.count DESC, name ASC',
            'limit' => -1
        ));
        $taxonomies_by_sport = array();

        while($sports->fetch() ) {
            $sport = $sports->display('name');
            if(!isset($taxonomies_by_sport[$sport]) && !$sports->field('hide')) {
                $sport_term = get_term($sports->display('term_id'));
                $taxonomies_by_sport[$sport] = array(
                    'sport' => array(
                        'name' => $sport_term->name,
                        'permalink' => get_term_link((int) $sport_term->term_id)
                    ),
                    'competitions' => array()
                );
            }
        }

        $competitions = pods( 'competition' )->find(array(
            'select' => ' `t`.term_id, `t`.name, `tt`.count, `t`.term_id, `sport`.name as sport_name',
            'where' => '(count != 0 OR description != "")',
            'orderby' => 'tt.count DESC, name ASC',
            'limit' => -1));

        while($competitions->fetch() ) {
            $sport = $competitions->display('sport_name');
            if(!isset($taxonomies_by_sport[$sport])) {
                continue;
            }
            $compet_term = get_term($competitions->display('term_id'));
            if(!$competitions->field('hide')) {
                $taxonomy = array(
                    'name' => $compet_term->name,
                    'permalink' => get_term_link((int) $compet_term->term_id),
                    'countryIso' => $competitions->display('country.code_iso'),
                );
                $taxonomies_by_sport[$sport]['competitions'][] = $taxonomy;
            }
        }
        $cache_token = self::$xdkdcache->writeCache($data_cache_name, $taxonomies_by_sport);
        add_option("cache_".$data_cache_name, $cache_token);
        $taxonomies_by_sport = self::$xdkdcache->getCache($cache_token,$data_cache_name);
      }

      return $taxonomies_by_sport;
    }
}
