=== Plugin Name ===
Contributors: asdasDan
Tags: private, posts, pages, exception, bypass, privacy, exclude
Requires at least: 3.0
Tested up to: 3.3
Stable Tag: trunk

Allows an administrator to make special exceptions to their private posts/pages (any content type), based on user role (or Guests), or IP address.

== Description ==

Advanced Post Privacy takes advantage of the current underdeveloped functionality of private posts. It
allows you to make exceptions to any content types that have been marked as private.

**Possible Future Features:**    
* Allowing exceptions on a per page/post basis.    
* Allowing protected posts/pages to be bypassed.    
* Integration with popular Member Plugins.    
* Integration with Pods CMS.   
* Anything else requested.   
   

== Installation ==

1. Download the plugin and extract the files to the /wp-content/plugins/ directory.
2. Activate the plugin through the Plugins menu.

== Screenshots ==

No screenshots yet.

== Usage ==

The usage of this plugin varies, I created it after having to do something very simple for a
project that wasn't built in, allowing a search engine to crawl private posts while remaining
private. I was able to do it using IP address, and this made the process much easier.

Other uses could include:     
   * Allowing certain roles (such as authors) to see private media.    
   * Allowing certain users to see all private posts based on their IP address.    


== Extending ==

**Note: At this point all additional folders that are added to the plugin will be deleted upon upgrade.
	Until I figure out how I want to work around that, if you develop any modules you want to keep, they will
	have to be moved out of the plugin folder before upgrade and then moved back in.**

Advanced Post Privacy (APP) is built in a very modular way, that allows for extending upon
via hooks/filters, or in the way of adding your own exclusion module. Each type of exclusion
is within it's own code, so all code related to the role exclusion is found within the /code/role
directory.

**Available Actions**:   
* adv_pp_add_ip_address_exclusion (1 arg, the IP address being added)   
* adv_pp_remove_ip_address_exclusion (2 args, the IP address, and the post type slug being removed)   
* adv_pp_uninstall (No arguments, runs on uninstall)   
    
**Available Filters**:   
* adv_pp_applicable_post_types (Array of slug => label post types that APP can access.)   
* adv_pp_post_type_labels (Array of labels for Post Types)    
* adv_pp_bypassed_post_ids (Array of Post Ids the current user can bypass)    
     
**Adding a type of Exclusion**    
By default, Advanced Post Privacy (APP) has two types of exclusions, IP Address, and Role. It’s built in a mostly modular fashion, and is a configuration based plugin, meaning after adding your functionality, you need to explicitly tell the system about your new functionality, via the config.json file.    
         
If you wanted to add a new type of exclusion, for example IP Address Range, you would have to do the following -    
  * Create a new directory inside the code folder, naming doesn’t matter much here, it could be called “ip-range”.    
  * Create a file called exclusion.php, this will be a class which could be called “ipRangeExclusions”, this class must implement the “appExclusions” interface, and should (for posterity) extend the primary “advancedPostPrivacy” class. This will force the class to have 5 static methods, broken down as:    
  * optionName() - Takes no arguments, and returns the name of the option in which all the exclusions will be stored in the database. This should be prefixed with the “APP_PREFIX” constant for uniqueness. This option will be completely removed on the uninstall (deletion) of the plugin.    
  * getExclusion() - Takes an optional $identifier argument, without that argument it should contain the code that gets all exclusions of the IP Range type, with it, it will return exclusions for that specific IP Range.    
  * setExclusion() - Takes an array of data that will set a single exclusion of that type. To comply with the other types, your code should contain a similar snippet on a successful added exclusion:   
	   `do_action(APP_PREFIX .    
                      'add_' .    
                      self::getExclusionModuleData(__CLASS__, 'singular') .    
                      '_exclusion', $exclusionData['excluded_ip']);`    
  This will create a hook for when an exclusion of your type is created. This method should also ignore any entries that already exist, just for good practice.    
  * removeExclusion() - This takes two arguments, the exclusions unique identifier (in this case it would be the IP Range), and the post type slug (“post”, “page”, etc). This code should remove the exclusion, and create an identical hook to the one in setExclusion(), only with the term remove instead of add.    
  * getExcludedPostIds() - This is the function that makes your exclusion type work, you do whatever checking you would need to here, in this case it would be checking if the users IP address (constant “VIEWER_IP_ADDRESS”) falls within any of the ranges in the database. It should return an array of private post Ids, that can be obtained by using:   
			     `self::getPrivatePostIdsFromPostTypes($arrayOfPostTypes)`
  Ideally the array of post types would be obtained by calling self::getExclusion() with the IP range passed.    
  * Now, ajax files for the interface must be created, create a folder called ajax within the code folder, and create files called action.php, and js.php.   
  * With action.php, this will store the functionality of your ajax calls, the class should extend appAjaxAction, and actions should be added to the wp_ajax_action_name method, refer to the Wordpress Ajax API for more information.   
  * With js.php, this will store the javascript that triggers your ajax calls, the class should extend appAjaxJs, and all methods should be added to the admin_head hook. Again, following the practices of the Wordpress Ajax API.   
  * Now you can create the user interface “widget” (in the non-WP sense) to your exclusion type, this will be a php file in your directory named settings.php. This will be included on the settings page.   
  * Finally, all of this is done, but the system still doesn’t know your exclusion module exists, but once you tell it it’s there, it will function seamlessly with the rest of the system (configuration based ftw). We do this by modifying the config.json in the root directory, you may want to back it up since it’s easy to mess up the syntax. You’ll want to add 3 lines of json under the “exclusionModules” array item, this would be an example:   
		       `"ipAddressExclusions": {    
				"singular":  "ip_address",    
				"directory": "code/ip-address"   
				 }`   
    
ipAddressExclusions must match the class name used in your exclusions.php file, the singular name is what will be used in hooks (and probably other places later), the directory is simply where your code lives from the plugin path, in this example it would be “code/ip-range”.   
    
Refresh your settings page, and you should see your system in effect!   


**Data available to your Exclusion Module**    
If you followed the conventions, all of your classes will ultimately extend the core advancedPostPrivacy class,
meaning you have access to all the methods within the class, however most of them may be made private in the 
future as they are really for the core. The protected static property of $_json_config is stored here, 
and has the complete array of the loaded json configuration file.

**Assets Available**    
jQuery chosen js, and css,  is enqueued via APP, and can be called on any pages as it is normally called.    
   
**Constants**   
APP_PREFIX (adv_pp_)      
APP_PATH    
APP_CODE_PATH    
APP_DESIGN_PATH    
APP_PLUGIN_URL   
APP_DESIGN_URL	  
APP_CONFIG_FILE (path to the config.json file)     
VIEWER_IP_ADDRESS (users REMOTE_ADDR from $_SERVER)    

== Changelog ==

= 1.0.0 = 
* Switching Versioning Syntax

= 0.0.1 =
* First stable release.
