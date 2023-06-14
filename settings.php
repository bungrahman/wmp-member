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

function wmp_settings_page() {
    // Handle form submissions and update settings
    if (isset($_POST['wmp_roles_submit'])) {
        update_option('wmp_member_roles', sanitize_text_field($_POST['wmp_roles']));

        // Register new roles based on the input
        $new_roles = explode(',', $_POST['wmp_roles']);
        foreach ($new_roles as $new_role) {
            $new_role = trim($new_role);
            $role_exists = get_role($new_role);

            if (!$role_exists) {
                add_role($new_role, ucfirst($new_role), array());
            }
        }
    } elseif (isset($_POST['wmp_roles_delete_submit'])) {
        $selected_role = $_POST['wmp_roles_delete']; // Get the selected role

        if ($selected_role) {
            $role_object = get_role($selected_role); // Get the role object

            if ($role_object) {
                remove_role($selected_role); // Remove the selected role
            }
        }
    }

    // Get all roles for dropdown
    $all_roles = wp_roles()->roles;

    ?>
    <div class="wrap">
        <h1><?php esc_html_e('WooCommerce Member Plugin Settings', 'woocommerce-member-plugin'); ?></h1>
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e('Member Roles', 'woocommerce-member-plugin'); ?></th>
                    <td>
                        <input type="text" name="wmp_roles" value="<?php echo esc_attr(get_option('wmp_member_roles')); ?>" class="regular-text">
                        <p class="description"><?php esc_html_e('Enter the member roles separated by commas.', 'woocommerce-member-plugin'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Delete Roles', 'woocommerce-member-plugin'); ?></th>
                    <td>
                        <select name="wmp_roles_delete">
                            <?php
                            foreach ($all_roles as $role => $role_details) {
                                echo '<option value="' . esc_attr($role) . '">' . esc_html($role_details['name']) . '</option>';
                            }
                            ?>
                        </select>
                        <p class="description"><?php esc_html_e('Select a role to delete.', 'woocommerce-member-plugin'); ?></p>
                    </td>
                </tr>
            </table>
            <?php wp_nonce_field('wmp_settings_nonce', 'wmp_settings_nonce'); ?>
            <p class="submit">
                <input type="submit" name="wmp_roles_submit" class="button-primary" value="<?php esc_attr_e('Save Changes', 'woocommerce-member-plugin'); ?>">
                <input type="submit" name="wmp_roles_delete_submit" class="button" value="<?php esc_attr_e('Delete Role', 'woocommerce-member-plugin'); ?>">
            </p>
        </form>
    </div>

    <?php
}



// Render the license key page
function wmp_license_key_page() {
    // Handle form submissions and validate license key
    if (isset($_POST['wmp_license_key_submit'])) {
        $license_key = isset($_POST['wmp_license_key']) ? sanitize_text_field($_POST['wmp_license_key']) : '';

        // Validate the license key (Example: Check against a server API)
        $is_valid = wmp_validate_license_key($license_key);

        // Save the license key if valid
        if ($is_valid) {
            update_option('wmp_license_key', $license_key);
            $message = 'License key validated and saved successfully.';
            $notice_class = 'updated';

            // Hide the last 5 characters of the license key
            $license_key_hidden = substr($license_key, 0, -5) . '******';
        } else {
            $message = 'Invalid license key. Please try again.';
            $notice_class = 'error';
            $license_key_hidden = $license_key; // Show the original license key if invalid
        }

        // Display the notice message
        echo '<div class="' . $notice_class . ' notice is-dismissible"><p>' . esc_html($message) . '</p></div>';
    } else {
        // Retrieve the saved license key (if any)
        $saved_license_key = get_option('wmp_license_key', '');

        // Hide the last 5 characters of the saved license key
        $license_key_hidden = substr($saved_license_key, 0, -5) . '******';
        
        // Set is_valid to false as we are not validating here
        $is_valid = false;
    }

    // Display the license key form
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('License Key', 'woocommerce-member-plugin'); ?></h1>
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e('Enter License Key', 'woocommerce-member-plugin'); ?></th>
                    <td>
                        <?php if ($is_valid) : ?>
                            <input type="text" value="<?php echo esc_attr($license_key_hidden); ?>" class="regular-text" disabled>
                        <?php else : ?>
                            <input type="text" name="wmp_license_key" value="<?php echo esc_attr($license_key_hidden); ?>" class="regular-text">
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
            <?php wp_nonce_field('wmp_license_key_nonce', 'wmp_license_key_nonce'); ?>
            <p class="submit">
                <?php if (!$is_valid) : ?>
                    <input type="submit" name="wmp_license_key_submit" class="button-primary" value="<?php esc_attr_e('Save Key', 'woocommerce-member-plugin'); ?>">
                <?php endif; ?>
            </p>
        </form>
    </div>
    <?php
}

// Validate the license key (Example function, replace with your own validation logic)
// Validate the license key against the domain using SHA-256 encryption
// Validate the license key against the site URL using MD5 encryption
function wmp_validate_license_key($license_key) {
    // Retrieve the site URL
    $site_url = get_site_url();

    // Perform MD5 encryption on the site URL
    $hashed_site_url = md5($site_url);

    // Compare the hashed site URL with the license key
    return $hashed_site_url === $license_key;
}

