<?php
/**
 * Plugin Name:       Simple Menu Items Control
 * Plugin URI:        https://github.com/astanabe/wp-simple-menuitems-control
 * Description:       A simple menu items visibility control plugins for WordPress
 * Author:            Akifumi S. Tanabe
 * Author URI:        https://github.com/astanabe
 * License:           GNU General Public License v2
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wp-simple-menuitems-control
 * Domain Path:       /languages
 * Version:           0.1.0
 * Requires at least: 6.4
 *
 * @package           WP_Simple_MenuItems_Control
 */

// Security check
if (!defined('ABSPATH')) {
	exit;
}

// Add visibility field to menuitems
function wp_simple_menuitems_control_add_visibility_field($item_id, $item, $depth, $args) {
	$visibility = get_post_meta($item_id, '_wp_simple_menuitems_control_visibility', true);
	if (empty($visibility)) $visibility = 'always';
	$roles = get_post_meta($item_id, '_wp_simple_menuitems_control_roles', true);
	$groups = get_post_meta($item_id, '_wp_simple_menuitems_control_groups', true);
	if (!is_array($roles)) $roles = [];
	if (!is_array($groups)) $groups = [];
	$all_roles = wp_roles()->roles;
	$all_groups = function_exists('bp_is_active') && bp_is_active('groups') ? wp_simple_menuitems_control_get_groups() : [];
	?>
	<p class="field-visibility description description-wide">
		<label>
			<?php esc_html_e('Visibility', 'wp-simple-menuitems-control'); ?><br />
			<input type="radio" class="wp-simple-menuitems-control-visibility-<?php echo $item_id; ?>" name="wp-simple-menuitems-control-visibility[<?php echo $item_id; ?>]" value="always" <?php checked($visibility, 'always'); ?>> Always display<br />
			<input type="radio" class="wp-simple-menuitems-control-visibility-<?php echo $item_id; ?>" name="wp-simple-menuitems-control-visibility[<?php echo $item_id; ?>]" value="logged-out" <?php checked($visibility, 'logged-out'); ?>> Displays for Logged-out users only<br />
			<input type="radio" class="wp-simple-menuitems-control-visibility-<?php echo $item_id; ?>" name="wp-simple-menuitems-control-visibility[<?php echo $item_id; ?>]" value="logged-in" <?php checked($visibility, 'logged-in'); ?>> Displays for Logged-in users only
		</label>
	</p>
	<div class="wp-simple-menuitems-control-roles-groups-<?php echo $item_id; ?>" <?php echo ($visibility === 'logged-in') ? '' : 'style="display:none;"'; ?>>
		<p class="field-roles description">
			<?php esc_html_e('Select Roles:', 'wp-simple-menuitems-control'); ?><br />
			<?php foreach ($all_roles as $role_key => $role) : ?>
				<input type="checkbox" class="wp-simple-menuitems-control-role-<?php echo $item_id; ?>" name="wp-simple-menuitems-control-roles[<?php echo $item_id; ?>][]" value="<?php echo esc_attr($role_key); ?>" <?php checked(in_array($role_key, $roles)); ?>> <?php echo esc_html($role['name']); ?><br />
			<?php endforeach; ?>
		</p>
		<?php if (!empty($all_groups)) : ?>
			<p class="field-groups description">
				<?php esc_html_e('Select Groups:', 'wp-simple-menuitems-control'); ?><br />
				<?php foreach ($all_groups as $group) : ?>
					<input type="checkbox" class="wp-simple-menuitems-control-group-<?php echo $item_id; ?>" name="wp-simple-menuitems-control-groups[<?php echo $item_id; ?>][]" value="<?php echo esc_attr($group['id']); ?>" <?php checked(in_array($group['id'], $groups)); ?>> <?php echo esc_html($group['name']); ?><br />
				<?php endforeach; ?>
			</p>
		<?php endif; ?>
	</div>
	<script>
		jQuery(document).ready(function($) {
			let item_id = "<?php echo $item_id; ?>";
			function updateRadiobuttonState() {
				let logeedinChecked = $('.wp-simple-menuitems-control-visibility-' + item_id + ':checked').val() == 'logged-in';
				if (logeedinChecked) {
					$('.wp-simple-menuitems-control-roles-groups-' + item_id).show();
				} else {
					$('.wp-simple-menuitems-control-roles-groups-' + item_id).hide();
				}
			}
			$('.wp-simple-menuitems-control-visibility-' + item_id).on('change', updateRadiobuttonState);
			updateRadiobuttonState();
			function updateCheckboxState() {
				let roleChecked = $('.wp-simple-menuitems-control-role-' + item_id + ':checked').length > 0;
				let groupChecked = $('.wp-simple-menuitems-control-group-' + item_id + ':checked').length > 0;
				if (roleChecked) {
					$('.wp-simple-menuitems-control-group-' + item_id).prop('disabled', true);
				} else if (groupChecked) {
					$('.wp-simple-menuitems-control-role-' + item_id).prop('disabled', true);
				} else {
					$('.wp-simple-menuitems-control-role-' + item_id + ', .wp-simple-menuitems-control-group-' + item_id).prop('disabled', false);
				}
			}
			$('.wp-simple-menuitems-control-role-' + item_id + ', .wp-simple-menuitems-control-group-' + item_id).on('change', updateCheckboxState);
			updateCheckboxState();
		});
	</script>
	<?php
}
add_action('wp_nav_menu_item_custom_fields', 'wp_simple_menuitems_control_add_visibility_field', 10, 4);

// Save visibility settings
function wp_simple_menuitems_control_save_visibility_settings($menu_id, $item_id) {
	if (isset($_POST['wp-simple-menuitems-control-visibility'][$item_id])) {
		update_post_meta($item_id, '_wp_simple_menuitems_control_visibility', sanitize_text_field($_POST['wp-simple-menuitems-control-visibility'][$item_id]));
	}
	if (isset($_POST['wp-simple-menuitems-control-roles'][$item_id])) {
		update_post_meta($item_id, '_wp_simple_menuitems_control_roles', array_map('sanitize_text_field', $_POST['wp-simple-menuitems-control-roles'][$item_id]));
	} else {
		delete_post_meta($item_id, '_wp_simple_menuitems_control_roles');
	}
	if (isset($_POST['wp-simple-menuitems-control-groups'][$item_id])) {
		update_post_meta($item_id, '_wp_simple_menuitems_control_groups', array_map('sanitize_text_field', $_POST['wp-simple-menuitems-control-groups'][$item_id]));
	} else {
		delete_post_meta($item_id, '_wp_simple_menuitems_control_groups');
	}
}
add_action('wp_update_nav_menu_item', 'wp_simple_menuitems_control_save_visibility_settings', 10, 2);

// Filter menu items based on visibility settings
function wp_simple_menuitems_control_filter_menuitems($items, $args) {
	if (is_admin()) {
		return $items;
	}
	foreach ($items as $key => $item) {
		$visibility = get_post_meta($item->ID, '_wp_simple_menuitems_control_visibility', true);
		$roles = get_post_meta($item->ID, '_wp_simple_menuitems_control_roles', true);
		$groups = get_post_meta($item->ID, '_wp_simple_menuitems_control_groups', true);
		if ($visibility === 'logged-out' && is_user_logged_in()) {
			unset($items[$key]);
		}
		if ($visibility === 'logged-in' && !is_user_logged_in()) {
			unset($items[$key]);
		}
		if ($visibility === 'logged-in' && !empty($roles) && !array_intersect(wp_get_current_user()->roles, $roles)) {
			unset($items[$key]);
		}
		if ($visibility === 'logged-in' && function_exists('bp_is_active') && bp_is_active('groups') && !empty($groups) && !array_intersect(groups_get_user_groups(get_current_user_id())['groups'], $groups)) {
			unset($items[$key]);
		}
	}
	return $items;
}
add_filter('wp_get_nav_menu_items', 'wp_simple_menuitems_control_filter_menuitems', 10, 2);

// Get top 50 groups
function wp_simple_menuitems_control_get_groups() {
	if (!function_exists('bp_has_groups')) {
		return [];
	}
	$groups = [];
	if (bp_has_groups(['per_page' => 50, 'orderby' => 'total_member_count', 'order' => 'DESC'])) {
		while (bp_groups()) {
			bp_the_group();
			$groups[] = ['id' => bp_get_group_id(), 'name' => bp_get_group_name()];
		}
	}
	return $groups;
}

// Page for deactivation
function wp_simple_menuitems_control_deactivate_page() {
	if (!current_user_can('manage_options')) {
		return;
	}
	if (isset($_POST['wp_simple_menuitems_control_deactivate_confirm']) && check_admin_referer('wp_simple_menuitems_control_deactivate_confirm', 'wp_simple_menuitems_control_deactivate_confirm_nonce')) {
		if ($_POST['wp_simple_menuitems_control_deactivate_confirm'] === 'remove') {
			update_option('wp_simple_menuitems_control_uninstall_settings', 'remove');
		}
		else {
			update_option('wp_simple_menuitems_control_uninstall_settings', 'keep');
		}
		deactivate_plugins(plugin_basename(__FILE__));
		wp_safe_redirect(admin_url('plugins.php?deactivated=true'));
		exit;
	}
	?>
	<div class="wrap">
		<h2>Deactivate Simple Menu Items Control Plugin</h2>
		<form method="post">
			<?php wp_nonce_field('wp_simple_menuitems_control_deactivate_confirm', 'wp_simple_menuitems_control_deactivate_confirm_nonce'); ?>
			<p>Do you want to remove all settings of this plugin when uninstalling?</p>
			<p>
				<label>
					<input type="radio" name="wp_simple_menuitems_control_deactivate_confirm" value="keep" checked />
					Leave settings (default)
				</label>
			</p>
			<p>
				<label>
					<input type="radio" name="wp_simple_menuitems_control_deactivate_confirm" value="remove" />
					Remove all settings
				</label>
			</p>
			<p>
				<input type="submit" class="button button-primary" value="Deactivate" />
			</p>
		</form>
	</div>
	<?php
	exit;
}

// Intercept deactivation request and redirect to confirmation screen
function wp_simple_menuitems_control_deactivate_hook() {
	if (isset($_GET['action']) && $_GET['action'] === 'deactivate' && isset($_GET['plugin']) && $_GET['plugin'] === plugin_basename(__FILE__)) {
		wp_safe_redirect(admin_url('admin.php?page=wp-simple-menuitems-control-deactivate'));
		exit;
	}
}
add_action('admin_init', 'wp_simple_menuitems_control_deactivate_hook');

// Add deactivation confirmation page to the admin menu
function wp_simple_menuitems_control_add_deactivate_page() {
	add_submenu_page(
		null, // No parent menu, hidden page
		'Deactivate Simple Menu Items Control Plugin',
		'Deactivate Simple Menu Items Control Plugin',
		'manage_options',
		'wp-simple-menuitems-control-deactivate',
		'wp_simple_menuitems_control_deactivate_page'
	);
}
add_action('admin_menu', 'wp_simple_menuitems_control_add_deactivate_page');

// Remove all settings when uninstalling if specified
function wp_simple_menuitems_control_uninstall() {
	if (get_option('wp_simple_menuitems_control_uninstall_settings') === 'remove') {
		global $wpdb;
		$wpdb->query("DELETE FROM $wpdb->postmeta WHERE meta_key IN ('_wp_simple_menuitems_control_visibility', '_wp_simple_menuitems_control_roles', '_wp_simple_menuitems_control_groups')");
		delete_option('wp_simple_menuitems_control_uninstall_settings');
	}
}
register_uninstall_hook(__FILE__, 'wp_simple_menuitems_control_uninstall');
