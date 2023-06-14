<?php
// Add a menu page to the WordPress dashboard
function wmp_settings_menu() {
    $license_key = get_option('wmp_license_key', '');

    // Check if the license key is empty or invalid
    $is_license_valid = wmp_validate_license_key($license_key);

    $role_input_capability = $is_license_valid ? 'manage_options' : 'do_not_allow';
    $role_edit_capability = $is_license_valid ? 'manage_options' : 'do_not_allow';

    add_menu_page(
        'WooCommerce Member Plugin Settings',
        'Member Plugin',
        $role_input_capability,
        'wmp-settings',
        'wmp_settings_page',
        'dashicons-groups',
        30
    );

    // Check if the license key is empty or invalid before adding the submenu page
    if (!empty($license_key) && $is_license_valid) {
        add_submenu_page(
            'wmp-settings',
            'Role Capabilities',
            'Role Capabilities',
            $role_edit_capability,
            'wmp-role-capabilities',
            'wmp_role_capabilities_page'
        );
    }

    // Add submenu page for license key
    add_submenu_page(
        'wmp-settings',
        'License Key',
        'License Key',
        'manage_options',
        'wmp-license-key',
        'wmp_license_key_page'
    );
}
add_action('admin_menu', 'wmp_settings_menu');


function wmp_role_capabilities_page() {
    $license_key = get_option('wmp_license_key', '');

    // Check if the license key is empty or invalid
    if (empty($license_key) || !wmp_validate_license_key($license_key)) {
        echo '<div class="notice notice-warning"><p>Please enter a valid license key to access this page.</p></div>';
        return;
    }

    // Render the role capabilities page
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Role Capabilities', 'woocommerce-member-plugin'); ?></h1>
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e('Select Role', 'woocommerce-member-plugin'); ?></th>
                    <td>
                        <select name="wmp_role" id="wmp-role">
                            <?php
                            $member_roles = get_option('wmp_member_roles', '');
                            $roles = explode(',', $member_roles);

                            foreach ($roles as $role) {
                                $role = trim($role);
                                $selected = (isset($_POST['wmp_role']) && $_POST['wmp_role'] === $role) ? 'selected' : '';
                                echo '<option value="' . esc_attr($role) . '" ' . $selected . '>' . esc_html($role) . '</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Capabilities', 'woocommerce-member-plugin'); ?></th>
                    <td>
                        <?php
                        $all_capabilities = array(
                            'read',
                            'edit_posts',
                            'delete_posts',
                            'publish_posts',
                            'upload_files',
                            'edit_published_posts',
                            'delete_published_posts',
                            'edit_private_posts',
                            'delete_private_posts',
                            'edit_others_posts',
                            'delete_others_posts',
                            'edit_pages',
                            'delete_pages',
                            'publish_pages',
                            'edit_published_pages',
                            'delete_published_pages',
                            'edit_private_pages',
                            'delete_private_pages',
                            'edit_others_pages',
                            'delete_others_pages',
                            'edit_products',
                            'read_private_products',
                            'edit_private_products',
                            'delete_private_products',
                            'delete_products',
                            'delete_published_products',
                            'delete_others_products',
                            'delete_private_products',
                            'edit_published_products',
                            'edit_others_products',
                            'edit_private_products',
                            'publish_products',
                            'read_private_forums',
                            'edit_private_forums',
                            'delete_private_forums',
                            'delete_forums',
                            'delete_published_forums',
                            'delete_others_forums',
                            'delete_private_forums',
                            'edit_published_forums',
                            'edit_others_forums',
                            'edit_private_forums',
                            'publish_forums',
                            // Add more capabilities as needed
                        );

                        $selected_capabilities = isset($_POST['wmp_capabilities']) ? $_POST['wmp_capabilities'] : array();

                        foreach ($all_capabilities as $capability) {
                            $checked = in_array($capability, $selected_capabilities) ? 'checked' : '';
                            echo '<label><input type="checkbox" name="wmp_capabilities[]" value="' . esc_attr($capability) . '" ' . $checked . '> ' . esc_html($capability) . '</label><br>';
                        }
                        ?>
                    </td>
                </tr>
            </table>
            <?php wp_nonce_field('wmp_role_capabilities_nonce', 'wmp_role_capabilities_nonce'); ?>
            <p class="submit">
                <input type="submit" name="wmp_capabilities_submit" class="button-primary" value="<?php esc_attr_e('Save Changes', 'woocommerce-member-plugin'); ?>">
            </p>
        </form>
    </div>
    <?php
}

