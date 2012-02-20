<?php

add_action('wp_ajax_add_ip_exclusion',    'ipAddressAjaxAction::addIpExclusion');
add_action('wp_ajax_save_ip_exclusion',   'ipAddressAjaxAction::saveIpExclusion');
add_action('wp_ajax_remove_ip_exclusion', 'ipAddressAjaxAction::removeIpExclusion');
add_action('wp_ajax_load_ip_exclusion_container', 'ipAddressAjaxAction::loadIpExclusionContainer');

class ipAddressAjaxAction extends appAjaxAction {
	
	public static function addIpExclusion() { ?>
        <hr />
    <div class="add-ip-based-exclusion">
    <h4>Add New Exclusion</h4>
Allow the IP Address
    <input size="14" class="excluded-ip" name="excluded_ip" />
        to bypass all private 
    <select style="width:150px;" multiple class="excluded-post-type" name="excluded_post_types[]">
        <?php $postTypes = appHelper::getApplicablePostTypes(); ?>
        <?php foreach ($postTypes as $slug => $label): ?>
            <option value="<?php echo $slug; ?>">
                <?php echo $label; ?>
            </option>
        <?php endforeach; ?>
    </select><br />
    <input id="submit-ip-based-exclusion" type="submit" class="button-primary" value="<?php _e('Save'); ?>" />
    <img src="/wordpress/wp-admin/images/wpspin_light.gif" class="ajax-loading" id="ajax-loading" alt="">
</div>        <?php
        die();
    }

    public static function saveIpExclusion() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            exit('0');
        }   

        if (!isset($_POST['excluded_ip']) || 
                !isset($_POST['excluded_post_types'])) {
                exit('0');
        }

        $_POST['excluded_ip'] = trim($_POST['excluded_ip']);

        if (!filter_var($_POST['excluded_ip'], FILTER_VALIDATE_IP)) {
            exit('IP Address Format Invalid.');
        }

        if (ipAddressExclusions::setExclusion($_POST)) {
            exit('1');
        } else {
            exit('0');
        }
    }

    public static function removeIpExclusion() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            exit('0');
        }

        if (!isset($_POST['post_type_array_key']) ||
                !isset($_POST['ip'])) {
            exit('0');            
        }

        if (ipAddressExclusions::removeExclusion($_POST['ip'], $_POST['post_type_array_key'])) {
            exit('1');
        } else {
            exit('0');
        }
    }

    public static function loadIpExclusionContainer() { ?>

<a id="add-ip-exclusion" href="#"><img class="plus-add-new" src="<?php echo APP_DESIGN_URL . '/img/add.png'; ?>" title="Add New" /></a>       

<div class="ip-exclusion-container">
    <?php foreach (ipAddressExclusions::getExclusion() as $ipAddress => $postTypeExclusions): ?>
        <strong><?php echo $ipAddress; ?></strong>
        <ul>
            <?php foreach ($postTypeExclusions as $key => $value): ?>
                <?php $postTypeLabels = appHelper::getPostTypeLabels($value); ?>
                <li class="exclusion-ip-<?php echo $ipAddress; ?>">
                    <?php echo $ipAddress; ?> users are bypassing private <?php echo $postTypeLabels->name; ?>.
                    <a id="remove-ip-exclusion-<?php echo $key; ?>"
                       class="remove-ip-exclusion"
                       href="#">
                            <img class="x-remove-symbol" src="<?php echo APP_DESIGN_URL . '/img/remove.png'; ?>" title="Remove Exclusion" />
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>   
    <?php endforeach; ?>
</div>


    <?php die();
    }
}