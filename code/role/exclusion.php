<?php

class roleExclusions extends advancedPostPrivacy
                     implements appExclusions {

    public static function optionName() {
        return APP_PREFIX . 'role_based_exclusions'; 
    }

    public static function getExclusion($specificRole=null) {
        $roleBasedExclusions = get_option(self::optionName(), array());

        // If a role is specified, only return those exclusions
        // Empty array if none exist
        if ($specificRole !== null) {
            if (array_key_exists($specificRole, $roleBasedExclusions)) {
                return $roleBasedExclusions[$specificRole];
            } else {
                return array();
            }
        }

        return $roleBasedExclusions;
    }

    public static function setExclusion($exclusionData) {
        list($excludedRole, $excludedPostTypes) = array($exclusionData['excluded_role'],
                                                        $exclusionData['excluded_post_types']);

        $roleBasedExclusions = self::getExclusion();

        // @todo - refactor for excluded_post_types as array
        foreach ($excludedPostTypes as $excludedPostType) {
            // If this role doesn't have any exclusions, add it and return true
            if (!array_key_exists($excludedRole, $roleBasedExclusions)) {
                $roleBasedExclusions[$excludedRole] = array($excludedPostType);

                update_option(self::optionName(), $roleBasedExclusions);

                continue;
            } else { // Options already exist for this role, so it's a little more complicated
                if (in_array($excludedPostType, $roleBasedExclusions[$excludedRole])) {
                    continue; // Already exists for that role/post type combo, bail.
                } else {
                    $roleBasedExclusions[$excludedRole][] = $excludedPostType;

                    update_option(self::optionName(), $roleBasedExclusions);

                    continue;
                }
            }
        }

        return true;
    }

    public static function removeExclusion($roleName, $postTypeArrayKey) {
        $roleBasedExclusions = get_option(self::optionName(), array());
         
        unset($roleBasedExclusions[$roleName][$postTypeArrayKey]);

        update_option(self::optionName(), $roleBasedExclusions);

        return true;
    }

    public static function getExcludedPostIds() {
        $postIdsToAdd = array();
            
        // Sets the users role, sets it to GUEST_ROLE_NAME if the current user doesn't have a role
        $userRole = (($uRole = appHelper::getUsersRole()) !== false) ? $uRole : self::GUEST_ROLE_NAME;

        // Adds all private posts with that post type to be added
        if ($excludedPostTypes = self::getExclusion($userRole)) {
            $postIdsToAdd = self::getPrivatePostIdsFromPostTypes($excludedPostTypes);
        }

        return $postIdsToAdd; 
    }
}