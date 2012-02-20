<?php
/**
 * Plugin Name: Advanced Post Privacy
 * Description: Allows flexibility among private posts and pages in Wordpress, allowing exclusions based on IP address or User Role.
 * Author:      Dan LaManna
 * Author URI:  http://danlamanna.com
 * Version:     1.0.0
 */

define('APP_PREFIX', 'adv_pp_');

define('APP_PATH', WP_PLUGIN_DIR . '/' . basename(dirname(__FILE__))); 

define('APP_CODE_PATH',   APP_PATH . '/code');
define('APP_DESIGN_PATH',     APP_PATH . '/design');

define('APP_PLUGIN_URL',      get_bloginfo('wpurl') . '/wp-content/plugins/' . basename(dirname(__FILE__)));
define('APP_DESIGN_URL',  get_bloginfo('wpurl') . '/wp-content/plugins/' . basename(dirname(__FILE__)) . '/design');

define('APP_CONFIG_FILE',     APP_PATH . '/config.json');

define('VIEWER_IP_ADDRESS',   $_SERVER['REMOTE_ADDR']);

// Ensure config file is readable before doing anything
if (!is_readable(APP_CONFIG_FILE)) {
  add_action('admin_notices', 'appUnreadableConfigFileNotice');

  return;
}

function appUnreadableConfigFileNotice() { ?>
  <div class="error fade">
            Advanced Post Privacy isn't running because the configuration file isn't readable, or doesn't exist.
  </div>
<?php
}


// Setup, register styles/scripts, load files, etc.
advancedPostPrivacy::init();

if (is_admin()) {
    wp_enqueue_script('jquery-chosen');
    wp_enqueue_style('chosen');
    wp_enqueue_style('adv-pp-settings');
}

// Remove any posts from $posts if we're on a singular post/page
// IF it's not the actual single post/page we're on
add_action('loop_start', 'advancedPostPrivacy::removeExtraPosts');

// Primary filter
add_filter('the_posts', 'advancedPostPrivacy::addBypassedPosts');

// Add menu page
add_action('admin_menu', 'advancedPostPrivacy::menuPage');
// Uninstall hook to remove anything we added
register_uninstall_hook(__FILE__, 'advancedPostPrivacy::appUninstall');

/**
 * Handles the primary functions of APP. 
 */
class advancedPostPrivacy {

    const GUEST_ROLE_NAME = 'Guest';

    protected static $_json_config = null;

    public static function init() {
        self::$_json_config = json_decode(file_get_contents(APP_CONFIG_FILE), true);

        self::loadModules();

        wp_register_script('jquery-chosen',  APP_DESIGN_URL . '/js/chosen.jquery.min.js');

        wp_register_style('chosen',          APP_DESIGN_URL . '/css/chosen/chosen.css');
        wp_register_style('adv-pp-settings', APP_DESIGN_URL . '/css/admin-styles.css');

        return true;
    }

    public static function loadModules() {
        self::loadCoreModules();
        self::loadExclusionModules();

        return self;
    }

    public static function loadCoreModules() {
        $jsonConfig = self::$_json_config['advancedPostPrivacy'];

        include_once(APP_CODE_PATH . '/core/class.ajax.php');

        foreach ($jsonConfig['coreModules'] as $moduleName => $coreModule) {
            foreach (glob(APP_PATH . '/' . $coreModule['directory'] . '/*') as $coreModuleFile) {
                require_once($coreModuleFile);
            }
        }

        return self;
    }

    public static function loadExclusionModules() {
        $jsonConfig = self::$_json_config['advancedPostPrivacy'];

        foreach ($jsonConfig['exclusionModules'] as $moduleName => $exclusionModule) {

            $modulePathExpr = APP_PATH . '/' . $exclusionModule['directory'];

            foreach (glob($modulePathExpr . '/*.php') as $exclusionModuleFile) {
                if (strpos($exclusionModuleFile, 'settings.php') === false) {
                    include_once($exclusionModuleFile);
                }
            }

            foreach (glob($modulePathExpr . '/ajax/*.php') as $ajaxFile) {
                include_once($ajaxFile);
            }
        }

        return self;
    }

    public static function getExclusionModuleData($dataKey, $exclusionModuleClass=null) {
        if ($exclusionModuleClass !== null) {
            $exclusionModuleData = self::$_json_config['advancedPostPrivacy']['exclusionModules'][$exclusionModuleClass];
	    
	    //	    var_dump($exclusionModuleData);
	    //	    var_dump($dataKey);

	    if (array_key_exists($dataKey, (array) $exclusionModuleData)) {
	      return $exclusionModuleData[$dataKey];
	    } else {
	      return false;
	    }
        } else {
            $exclusionModuleData = array();

            foreach (self::$_json_config['advancedPostPrivacy']['exclusionModules'] as $exclusionModule => $data) {
                $exclusionModuleData[] = $data[$dataKey];
            }

            return $exclusionModuleData;
        }
    }

    /**
     * Gets all options from extending classes optionName method, and makes 
     * sure to delete them on uninstall of APP. 
     * @return void
     */
    public static function appUninstall() {
        do_action('adv_pp_uninstall');

        foreach (self::$_json_config['advancedPostPrivacy']['exclusionModules'] as $moduleClass => $value) {
	  $moduleOption = call_user_func($moduleClass . '::optionName');

            delete_option($moduleOption);
        }


    }
    
    /**
     * Registers the APP options page with WP.
     * @return void
     */
    public static function menuPage() {
        add_options_page('Advanced Post Privacy - Settings', 
                         'Advanced Post Privacy', 
                         'manage_options',
                         'advanced-post-privacy-settings',
                         'advancedPostPrivacy::settingsMenuPage');
    }
   
    /**
     * Includes the settings menu page.
     * @return void
     */
    public static function settingsMenuPage() { 
        include_once(APP_DESIGN_PATH . '/settings.php');
    }

    /**
     * Adds all bypassed posts to "the_posts" hook.
     * @param [type] $thePosts [description]
     */
    public static function addBypassedPosts($thePosts) {
        $bypassedPostIds = self::getBypassedPostIds();

        foreach ($bypassedPostIds as $postId) {
            $thePosts[] = get_post($postId);
        }

        $jankyUniquePosts = array();

        foreach ($thePosts as $k => $thePost) {
            if (!in_array($thePost->ID, $jankyUniquePosts)) {
                $jankyUniquePosts[] = $thePost->ID;
            } else {
                unset($thePosts[$k]);
            }
        }

        // @todo- Make so much less janky.
        
        return $thePosts;
    }

    /**
     * Because of where is_singular becomes available, we remove
     * bypassed posts at the start of the loop here, as long as
     * it isn't the actual post being looped over. 
     * @return [type]
     */
    public static function removeExtraPosts() {
        if (!is_singular()) {
            return false;
        }

        $bypassedPostIds = advancedPostPrivacy::getBypassedPostIds();

        global $posts, $post;

        foreach ($posts as $key => $val) {
            if (in_array($val->ID, $bypassedPostIds) && $val->ID != $post->ID) {
                unset($posts[$key]);
            }
        }

        return;
    }

    /**
     * Takes an array of post type slugs and gets all private post IDs
     * in the post types.
     * @param  string|array $postTypeSlugs One or more post type slugs.
     * @return array Array of post IDs.
     */
    public static function getPrivatePostIdsFromPostTypes($postTypeSlugs) {
        global $wpdb;

        $postTypeSlugs = (array) $postTypeSlugs;

        $privatePostIds = array();

        foreach ($postTypeSlugs as $postTypeSlug) {
            $postTypePrivateIds = $wpdb->get_col(
                                    $wpdb->prepare("select ID from $wpdb->posts 
                                                    where post_status = 'private'
                                                    and
                                                    post_type = '$postTypeSlug'"));

            $privatePostIds = array_merge($privatePostIds, $postTypePrivateIds);
        }

        return $privatePostIds;
    }

    /**
     * Gets all bypassed post IDs by going through all the classes in
     * the config file, and calls getExcludedPostIds on them to merge.
     * @return array Post Ids meant to be bypassed
     */
    public static function getBypassedPostIds() {
        $bypassedPostIds = array();

        $jsonConfig      = self::$_json_config['advancedPostPrivacy'];

        foreach ($jsonConfig['exclusionModules'] as $exclusionClass => $exclusionModuleData) {
            $excludedPostIds = call_user_func($exclusionClass . '::' . 'getExcludedPostIds');
            $bypassedPostIds = array_merge($bypassedPostIds, $excludedPostIds);
        }

        $bypassedPostIds = array_unique($bypassedPostIds);

        $bypassedPostIds = apply_filters('adv_pp_bypassed_post_ids', $bypassedPostIds);

        return array_unique($bypassedPostIds);
    }    
}