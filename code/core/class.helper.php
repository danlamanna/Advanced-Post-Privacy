<?php

class appHelper extends advancedPostPrivacy {

    /**
     * Post Types that are restricted to Advanced Post Privacys
     * access.
     * @type Array
     */
    public static $excludedPostTypes = array('revision',
                                             'nav_menu_item',
					     'attachment');

    /**
     * Gets all Roles for APP to use.
     * @param  bool   $includeGuest=true [description]
     * @return [type]
     */
    public static function getAllRoles($includeGuest=true) {
        global $wp_roles;

        $allRoles = $wp_roles->roles;

        if ($includeGuest === true) {
            $allRoles[advancedPostPrivacy::GUEST_ROLE_NAME] = array('name' => 'Guest');
        }

        return $allRoles;
    }

	/**
	 * Gets the current users role as a string.
     *     - How does WP not have a function that does this?
	 * @return string|bool User role on success, false on failure.
	 */
	public static function getUsersRole() {
        if ($currentUserRole = wp_get_current_user()) {
            return current($currentUserRole->roles);
        } else {
            return false;
        }
    }
 
 	/**
 	 * Gets the labels of a post type.
 	 * @param  string $postTypeSlug Slug of a registered post type.
 	 * @return array Array of labels for the post type.
 	 */
 	public static function getPostTypeLabels($postTypeSlug) {
         $postTypeObj = get_post_type_object($postTypeSlug);

         return apply_filters('adv_pp_post_type_labels', $postTypeObj->labels);
    }

    /**
     * Gets the post types that we'll allow Advanced Post
     * Privacy to access, by default it ignores revision and nav_menu_item.
     * @return array slug => label array of allowed post types.
     */
    public static function getApplicablePostTypes() {
        $excludedPostTypes = self::$excludedPostTypes;

        $registeredPostTypes = get_post_types(null, 'objects');

        foreach ($registeredPostTypes as $slug => $postTypeObj) {
            if (in_array($slug, $excludedPostTypes)) {
                unset($registeredPostTypes[$slug]);
            }
        }

        $postTypeSlugLabels = array();

        // Setup final array of slug => label
        foreach ($registeredPostTypes as $slug => $postTypeObj) {
            $postTypeSlugLabels[$slug] = $postTypeObj->label;
        }

        return apply_filters('adv_pp_applicable_post_types', $postTypeSlugLabels);
    }
}