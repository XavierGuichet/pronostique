<?php

class UsersDAO
{
    const GROUP_RETIRED_EXPERTS = 'ExpertsSupprimes';
    const GROUP_EXPERTS = 'Experts';
    const GROUP_TIPSERS = 'Tipsters';
    const GROUP_ADHERENTS = 'Adhérent';

    public static function getUserGroups($uid)
    {
        global $wpdb;

        if (!isset($uid) || !$uid) {
            return array();
        }

        $groups = $wpdb->get_results("SELECT * FROM $wpdb->user2group_rs JOIN $wpdb->groups_rs ON $wpdb->groups_id_col = $wpdb->user2group_gid_col WHERE $wpdb->user2group_uid_col = '$uid'");
        $tab = array();
        foreach ($groups as $g) {
            array_push($tab, $g->group_name);
        }

        return $tab;
    }

    public static function isUserInGroup($uid, $group_name)
    {
        $groups = self::getUserGroups($uid);

        return in_array($group_name, $groups);
    }

    // check if a user has already a group which permits him to post tips
    public static function isUserConfirmed($uid)
    {
        $groups = self::getUserGroups($uid);

        return (in_array(UsersDAO::GROUP_TIPSERS, $groups) ||
                in_array(UsersDAO::GROUP_EXPERTS, $groups) ||
                in_array(UsersDAO::GROUP_RETIRED_EXPERTS, $groups));
    }
}
