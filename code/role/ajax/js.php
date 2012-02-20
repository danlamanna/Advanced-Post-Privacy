<?php

add_action('admin_head', 'roleAjaxJs::addRoleExclusion');
add_action('admin_head', 'roleAjaxJs::saveRoleExclusion');
add_action('admin_head', 'roleAjaxJs::loadRoleExclusionContainer');
add_action('admin_head', 'roleAjaxJs::removeRoleExclusion');

class roleAjaxJs extends appAjaxJs {
	
	public static function addRoleExclusion() { ?>
        <script type="text/javascript">
        jQuery(document).ready(function() {
            var data = { action: 'add_role_exclusion' }

            jQuery('#add-role-exclusion').live('click', function() {
                // If the haven't saved a new exclusion yet, bail.
                if (jQuery('#role-based-exclusion .add-role-based-exclusion').size() == 1) {
                    return false;
                }

                jQuery.post(ajaxurl, data, function(resp) {
                    jQuery('#role-based-exclusion').append(resp);
                });
            });
        });
        </script> <?php
    }

    public static function saveRoleExclusion() { ?>
        <script type="text/javascript">
        jQuery(document).ready(function() {
            jQuery('#submit-role-based-exclusion').live('click', function() {
                jQuery('#ajax-loading').show();

                var excludedRole      = jQuery(this).siblings('select.excluded-role').find(':selected').val();
                var excludedPostTypes = [];

                jQuery(this).siblings('select.excluded-post-type').find(':selected').each(function(i, selected) {
                    excludedPostTypes[i] = jQuery(selected).val(); 
                });

                var data = { 
                    action: 'save_role_exclusion',
                    excluded_role: excludedRole, 
                    excluded_post_types: excludedPostTypes 
                }

                jQuery.post(ajaxurl, data, function(resp) {
                    jQuery('#ajax-loading').hide();
                    jQuery('.add-role-based-exclusion').fadeOut();

            reloadRoleExclusionContainer();
                });
            });
        });
        </script> <?php
    }

    public static function removeRoleExclusion() { ?>
         <script type="text/javascript">
        jQuery(document).ready(function() {
            jQuery('a.remove-role-exclusion').live('click', function() {
                var globalThis      = jQuery(this);
                var roleExclusionLi = jQuery(this).closest('li');

                var postTypeArrayKey = jQuery(this).attr('id');
                postTypeArrayKey     = postTypeArrayKey.replace('remove-role-exclusion-', '');

                var roleName         = jQuery(this).closest('li').attr('class');
                roleName             = roleName.replace('exclusion-role-', '');

                var data = { 
                    action: 'remove_role_exclusion',
                    post_type_array_key: postTypeArrayKey,
                    role_name: roleName
                }

                jQuery.post(ajaxurl, data, function(resp) {
                    if (resp == 1) {
                        // If it's the last LI for that role, remove the entire UL
                        if (globalThis.parent('ul').find('li').size() == 1) {
                            reloadRoleExclusionContainer();
                        } else {
                            roleExclusionLi.fadeOut();
                        }

                        reloadRoleExclusionContainer();
                    } else {
                        alert('Issue removing role exclusion.');
                    }
                });
            });
        });
        </script> <?php
    }

    public static function loadRoleExclusionContainer() { ?>
        <script type="text/javascript">
            function reloadRoleExclusionContainer() {
                var data = { action: 'load_role_exclusion_container' }

                jQuery.post(ajaxurl, data, function(resp) {
                    jQuery('#role-based-exclusion').html(resp); 
                });
            }
        </script> <?php
    }
}