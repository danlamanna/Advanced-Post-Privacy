<?php

class ipAddressExclusions extends advancedPostPrivacy
                          implements appExclusions {

    private static $_singular_reference = 'ip_address';

    public static function optionName() {
        return APP_PREFIX . 'ip_based_exclusions';
    }

    public static function setExclusion($exclusionData) {
        list($excludedIp, $excludedPostTypes) = array($exclusionData['excluded_ip'],
                                                     $exclusionData['excluded_post_types']);

        $ipBasedExclusions = self::getExclusion();

        foreach ($excludedPostTypes as $excludedPostType) {
            if (!array_key_exists($excludedIp, $ipBasedExclusions)) {
                $ipBasedExclusions[$excludedIp] = array($excludedPostType);

                update_option(self::optionName(), $ipBasedExclusions);

                continue;
            } else {
                if (in_array($excludedPostType, $ipBasedExclusions[$excludedIp])) {
                    continue;
                } else {
                    $ipBasedExclusions[$excludedIp][] = $excludedPostType;

                    update_option(self::optionName(), $ipBasedExclusions);

                    // adv_pp_add_ip_address_exclusion
                    do_action(APP_PREFIX .
                             'add_' .
                              self::getExclusionModuleData('singular', __CLASS__) . 
                             '_exclusion', $exclusionData['excluded_ip']);

                    continue;
                } 
            }
        }
        
        return true;
    }

    public static function getExclusion($specificIp=null) {
        $ipBasedExclusions = get_option(self::optionName(), array());

        // If an ip is specified, only return those exclusions
        // Empty array if none exist
        if ($specificIp !== null) {
            if (array_key_exists($specificIp, $ipBasedExclusions)) {
                return $ipBasedExclusions[$specificIp];
            } else {
                return array();
            }
        }

        return $ipBasedExclusions;
    }

    public static function removeExclusion($ipAddress, $postTypeArrayKey) {
         $ipBasedExclusions = get_option(self::optionName(), array());
         
         unset($ipBasedExclusions[$ipAddress][$postTypeArrayKey]);

         // If there are no post types to that IP
         if (empty($ipBasedExclusions[$ipAddress])) {
             unset($ipBasedExclusions[$ipAddress]);
         }

         update_option(self::optionName(), $ipBasedExclusions);

         // adv_pp_remove_ip_address_exclusion
         do_action(APP_PREFIX . 
                   'remove_' . 
                   self::getExclusionModuleData('singular', __CLASS__) . 
                   '_exclusion', $ipAddress, $postTypeArrayKey);

         return true;
    }

    public static function getExcludedPostIds() {
        $postIdsToAdd = array();

        if ($excludedPostTypes = self::getExclusion(VIEWER_IP_ADDRESS)) {
            $postIdsToAdd = self::getPrivatePostIdsFromPostTypes($excludedPostTypes);
        }
        
        return $postIdsToAdd;
    }
}