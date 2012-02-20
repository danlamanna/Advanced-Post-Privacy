<?php

add_action('admin_head', 'ipAddressAjaxJs::addIpExclusion');
add_action('admin_head', 'ipAddressAjaxJs::saveIpExclusion');
add_action('admin_head', 'ipAddressAjaxJs::removeIpExclusion');
add_action('admin_head', 'ipAddressAjaxJs::loadIpExclusionContainer');

class ipAddressAjaxJs extends appAjaxJs {

	public static function addIpExclusion() {
        ?>
        <script type="text/javascript">
			jQuery(document).ready(function() {
    			jQuery('#add-ip-exclusion').live('click', function() {
                    var data = { action: 'add_ip_exclusion' }

        	       	// If the haven't saved a new exclusion yet, bail.
        			if (jQuery('#ip-based-exclusion .add-ip-based-exclusion').size() == 1) {
            			return false;
        			}

        			jQuery.post(ajaxurl, data, function(resp) {
            			jQuery('#ip-based-exclusion').append(resp);

                        jQuery('select').chosen();
        			});
    			});
			});
		</script>
        <?php
    }

    public static function saveIpExclusion() { ?>
        <script type="text/javascript">
        jQuery(document).ready(function() {
            jQuery('#submit-ip-based-exclusion').live('click', function() {
                jQuery('#ajax-loading').show();

                var excludedIp        = jQuery(this).siblings('.excluded-ip').val();
                var excludedPostTypes = [];

                jQuery(this).siblings('select.excluded-post-type').find(':selected').each(function(i, selected) {
                    excludedPostTypes[i] = jQuery(selected).val(); 
                });

                var data = { 
                    action: 'save_ip_exclusion',
                    excluded_ip: excludedIp, 
                    excluded_post_types: excludedPostTypes
                }

                jQuery.post(ajaxurl, data, function(resp) {
                    jQuery('#ajax-loading').hide();
                   
                    if (resp == 1) {
                        reloadIpExclusionContainer();
                    } else if (resp == 0) {
                        alert('There was an issue excluding the IP Address.');
                    } else {
                        alert(resp);
                    }                    
                });
            });
        });
        </script>
    <?php
    }

    public static function removeIpExclusion() { ?>
        <script type="text/javascript">
        jQuery(document).ready(function() {
            jQuery('a.remove-ip-exclusion').live('click', function() {
                var globalThis      = jQuery(this);
                var ipExclusionLi = jQuery(this).closest('li');

                var postTypeArrayKey = jQuery(this).attr('id');
                postTypeArrayKey     = postTypeArrayKey.replace('remove-ip-exclusion-', '');

                var ip_address = jQuery(this).closest('li').attr('class');
                ip_address     = ip_address.replace('exclusion-ip-', '');

                var data = { 
                    action: 'remove_ip_exclusion',
                    post_type_array_key: postTypeArrayKey,
                    ip: ip_address
                }

                jQuery.post(ajaxurl, data, function(resp) {
                    if (resp == 1) {
                        // If it's the last LI for that role, remove the entire UL
                        if (globalThis.parent('ul').find('li').size() == 1) {
                            globalThis.parent('ul').fadeOut();
                        } else {
                            ipExclusionLi.fadeOut();
                        }
                    } else {
                        alert('Issue removing IP exclusion.');
                    }
                });
            });
        });
        </script> <?php
    }

    public static function loadIpExclusionContainer() { ?>
        <script type="text/javascript">
            function reloadIpExclusionContainer() {
                var data = { action: 'load_ip_exclusion_container' }

                jQuery.post(ajaxurl, data, function(resp) {
                    jQuery('#ip-based-exclusion').html(resp); 
                });
            }
        </script> <?php
    }
}