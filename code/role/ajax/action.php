<?php

add_action('wp_ajax_add_role_exclusion',            'roleAjaxAction::addRoleExclusion');
add_action('wp_ajax_save_role_exclusion',           'roleAjaxAction::saveRoleExclusion');
add_action('wp_ajax_load_role_exclusion_container', 'roleAjaxAction::loadRoleExclusionContainer');
add_action('wp_ajax_remove_role_exclusion',         'roleAjaxAction::removeRoleExclusion');

class roleAjaxAction extends appAjaxAction {
	
	public static function addRoleExclusion() {
        global $wp_roles;

        $appRoles = $wp_roles->roles;
        $appRoles['Guest'] = array('name' => 'Guest');
?>
<hr />
    <div class="add-role-based-exclusion">
    <h4>Add New Exclusion</h4>
Allow the role 
    <select class="excluded-role" name="excluded_role">
        <?php foreach ($appRoles as $roleName => $roleLabel): ?>
            <option value="<?php echo $roleName; ?>"><?php echo $roleLabel['name']; ?></option>
        <?php endforeach; ?>
    </select>
    to bypass all private 
    <select style="width:150px;" multiple class="excluded-post-type" name="excluded_post_types[]">
        <?php $postTypes = appHelper::getApplicablePostTypes(); ?>
        <?php foreach ($postTypes as $slug => $label): ?>
            <option value="<?php echo $slug; ?>">
                <?php echo $label; ?>
            </option>
        <?php endforeach; ?>
    </select><br />
    <input id="submit-role-based-exclusion" type="submit" class="button-primary" value="<?php _e('Save'); ?>" />
    <img src="/wordpress/wp-admin/images/wpspin_light.gif" class="ajax-loading" id="ajax-loading" alt="">
</div>
<?php
die();
    }

    public static function saveRoleExclusion() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            exit('0');
        }   

        if (!isset($_POST['excluded_role']) || 
                !isset($_POST['excluded_post_types'])) {
                exit('0');
        }

        if (roleExclusions::setExclusion($_POST)) {
            exit('1');
        } else {
            exit('0');
        }
    }

    public static function loadRoleExclusionContainer() { 
        $appRoles = appHelper::getAllRoles(); ?>
        <a id="add-role-exclusion" href="#"><img class="plus-add-new" src="<?php echo APP_DESIGN_URL . '/img/add.png'; ?>" title="Add New" /></a>

<div class="role-exclusion-container">
    <?php foreach ($appRoles as $roleName => $roleLabel): ?>
        <?php if ($roleExclusions = roleExclusions::getExclusion($roleName)): ?>
            <strong><?php echo $roleLabel['name']; ?></strong>
            <ul>
                <?php foreach ($roleExclusions as $key => $value): ?>
                    <?php $postTypeLabels = get_post_type_object($value); ?>
                    <?php $postTypeLabels = $postTypeLabels->labels; ?>
                    <li class="exclusion-role-<?php echo $roleName; ?>"><?php echo $roleLabel['name']; ?>s are bypassing <?php echo $postTypeLabels->all_items; ?>
                        <a id="remove-role-exclusion-<?php echo $key ?>" class="remove-role-exclusion" href="#">
                            <img class="x-remove-symbol" src="<?php echo APP_DESIGN_URL . '/img/remove.png'; ?>" title="Remove Exclusion" />
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
<?php
         die();
    }

    public static function removeRoleExclusion() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            exit('0');
        }

        if (!isset($_POST['post_type_array_key']) ||
                !isset($_POST['role_name'])) {
            exit('0');            
        }

        if (roleExclusions::removeExclusion($_POST['role_name'], $_POST['post_type_array_key'])) {
            exit('1');
        } else {
            exit('0');
        }
    }
}