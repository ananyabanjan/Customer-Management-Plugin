<?php
/**
 * Plugin Name: Custom Customer Management
 * Description: Manage customer data with CRUD, admin interface, and frontend shortcode.
 * Version: 1.0
 * Author: Ananya PS
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Include necessary files
require_once plugin_dir_path(__FILE__) . 'includes/class-customer-database.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-customer-list-table.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-customer-ajax-handler.php';

// Activation hook to create tables
register_activation_hook(__FILE__, 'ccm_create_tables');
function ccm_create_tables() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_customers';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id int(11) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        email varchar(255) NOT NULL UNIQUE,
        phone varchar(20) NOT NULL,
        dob date NOT NULL,
        gender enum('Male','Female','Other') NOT NULL,
        cr_number varchar(50) NOT NULL,
        address text NOT NULL,
        city varchar(100) NOT NULL,
        country varchar(100) NOT NULL,
        status enum('active','inactive') NOT NULL DEFAULT 'active',
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Deactivation hook 
register_deactivation_hook(__FILE__, 'ccm_deactivate');
function ccm_deactivate() {

}

// Enqueue scripts and styles
add_action('admin_enqueue_scripts', 'ccm_admin_scripts');
function ccm_admin_scripts() {
    wp_enqueue_style('ccm-admin-styles', plugin_dir_url(__FILE__) . 'assets/css/admin-styles.css');
    wp_enqueue_script('ccm-admin-scripts', plugin_dir_url(__FILE__) . 'assets/js/admin-scripts.js', array('jquery'), '1.0', true);
}

add_action('wp_enqueue_scripts', 'ccm_frontend_scripts');
function ccm_frontend_scripts() {
    // Only enqueue on pages with the shortcode to avoid loading everywhere
    if (is_page() && has_shortcode(get_post()->post_content, 'customer_list')) {
        wp_enqueue_style('ccm-frontend-styles', plugin_dir_url(__FILE__) . 'assests/css/frontend-styles.css');
        wp_enqueue_script('ccm-frontend-scripts', plugin_dir_url(__FILE__) . 'assests/js/frontend-scripts.js', array('jquery'), '1.0', true);
        wp_localize_script('ccm-frontend-scripts', 'ccm_ajax', array('ajax_url' => admin_url('admin-ajax.php')));
    }
}

// Add admin menu
add_action('admin_menu', 'ccm_admin_menu');
function ccm_admin_menu() {
    add_menu_page('Customers', 'Customers', 'manage_options', 'customers', 'ccm_customers_page', 'dashicons-groups', 20);
    add_submenu_page('customers', 'Add Customer', 'Add Customer', 'manage_options', 'add-customer', 'ccm_add_customer_page');
    add_submenu_page('customers', 'Edit Customer', 'Edit Customer', 'manage_options', 'edit-customer', 'ccm_edit_customer_page');
    add_submenu_page('customers', 'Delete Customer', 'Delete Customer', 'manage_options', 'delete-customer', 'ccm_delete_customer_page');
}

// Admin pages
function ccm_customers_page() {
    if (!current_user_can('manage_options')) {
        wp_die('You do not have permission to access this page.');
    }
    $list_table = new Customer_List_Table();
    $list_table->prepare_items();
    ?>
    <div class="wrap">
        <h1>Customers</h1>
        <form method="post">
            <?php $list_table->search_box('Search Customers', 'search_id'); ?>
            <?php $list_table->display(); ?>
        </form>
    </div>
    <?php
}

function ccm_add_customer_page() {
    if (!current_user_can('manage_options')) {
        wp_die('You do not have permission to access this page.');
    }
    // Handle form submission
    if (isset($_POST['submit']) && wp_verify_nonce($_POST['ccm_nonce'], 'ccm_add_customer')) {
        Customer_Database::add_customer($_POST);
        echo '<div class="notice notice-success"><p>Customer added successfully.</p></div>';
    }
    // Display form
    ?>
    <div class="wrap">
        <h1>Add Customer</h1>
        <form method="post">
            <?php wp_nonce_field('ccm_add_customer', 'ccm_nonce'); ?>
            <label>Name: <input type="text" name="name" required></label><br>
            <label>Email: <input type="email" name="email" required></label><br>
            <label>Phone: <input type="text" name="phone" required></label><br>
            <label>DOB: <input type="date" name="dob" required></label><br>
            <label>Gender: <select name="gender"><option>Male</option><option>Female</option><option>Other</option></select></label><br>
            <label>CR Number: <input type="text" name="cr_number" required></label><br>
            <label>Address: <textarea name="address" required></textarea></label><br>
            <label>City: <input type="text" name="city" required></label><br>
            <label>Country: <input type="text" name="country" required></label><br>
            <label>Status: <select name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></label><br>
            <input type="submit" name="submit" value="Add Customer">
        </form>
    </div>
    <?php
}

function ccm_edit_customer_page() {
    if (!current_user_can('manage_options')) {
        wp_die('You do not have permission to access this page.');
    }
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if (!$id) {
        wp_die('Invalid customer ID.');
    }
    $customer = Customer_Database::get_customer($id);
    if (!$customer) {
        wp_die('Customer not found.');
    }

    // Handle form submission
    if (isset($_POST['submit']) && wp_verify_nonce($_POST['ccm_nonce'], 'ccm_edit_customer')) {
        $data = array(
            'name' => sanitize_text_field($_POST['name']),
            'email' => sanitize_email($_POST['email']),
            'phone' => sanitize_text_field($_POST['phone']),
            'dob' => sanitize_text_field($_POST['dob']),
            'gender' => sanitize_text_field($_POST['gender']),
            'cr_number' => sanitize_text_field($_POST['cr_number']),
            'address' => sanitize_textarea_field($_POST['address']),
            'city' => sanitize_text_field($_POST['city']),
            'country' => sanitize_text_field($_POST['country']),
            'status' => sanitize_text_field($_POST['status'])
        );
        // Basic validation
        if (!is_numeric($data['phone'])) {
            echo '<div class="notice notice-error"><p>Phone must be numeric.</p></div>';
        } elseif (!preg_match('/^[a-zA-Z0-9]+$/', $data['cr_number'])) {
            echo '<div class="notice notice-error"><p>CR Number must be alphanumeric.</p></div>';
        } else {
            Customer_Database::update_customer($id, $data);
            echo '<div class="notice notice-success"><p>Customer updated successfully.</p></div>';
            $customer = Customer_Database::get_customer($id);  // Refresh data
        }
    }

    // Display form
    ?>
    <div class="wrap">
        <h1>Edit Customer</h1>
        <form method="post">
            <?php wp_nonce_field('ccm_edit_customer', 'ccm_nonce'); ?>
            <label>Name: <input type="text" name="name" value="<?php echo esc_attr($customer->name); ?>" required></label><br>
            <label>Email: <input type="email" name="email" value="<?php echo esc_attr($customer->email); ?>" required></label><br>
            <label>Phone: <input type="text" name="phone" value="<?php echo esc_attr($customer->phone); ?>" required></label><br>
            <label>DOB: <input type="date" name="dob" value="<?php echo esc_attr($customer->dob); ?>" required></label><br>
            <label>Gender: <select name="gender">
                <option value="Male" <?php selected($customer->gender, 'Male'); ?>>Male</option>
                <option value="Female" <?php selected($customer->gender, 'Female'); ?>>Female</option>
                <option value="Other" <?php selected($customer->gender, 'Other'); ?>>Other</option>
            </select></label><br>
            <label>CR Number: <input type="text" name="cr_number" value="<?php echo esc_attr($customer->cr_number); ?>" required></label><br>
            <label>Address: <textarea name="address" required><?php echo esc_textarea($customer->address); ?></textarea></label><br>
            <label>City: <input type="text" name="city" value="<?php echo esc_attr($customer->city); ?>" required></label><br>
            <label>Country: <input type="text" name="country" value="<?php echo esc_attr($customer->country); ?>" required></label><br>
            <label>Status: <select name="status">
                <option value="active" <?php selected($customer->status, 'active'); ?>>Active</option>
                <option value="inactive" <?php selected($customer->status, 'inactive'); ?>>Inactive</option>
            </select></label><br>
            <input type="submit" name="submit" value="Update Customer">
        </form>
    </div>
    <?php
}

function ccm_delete_customer_page() {
    if (!current_user_can('manage_options')) {
        wp_die('You do not have permission to access this page.');
    }
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if (!$id) {
        wp_die('Invalid customer ID.');
    }
    $customer = Customer_Database::get_customer($id);
    if (!$customer) {
        wp_die('Customer not found.');
    }

    // Handle form submission
    if (isset($_POST['confirm_delete']) && wp_verify_nonce($_POST['ccm_nonce'], 'ccm_delete_customer')) {
        Customer_Database::delete_customer($id);
        echo '<div class="notice notice-success"><p>Customer deleted successfully.</p></div>';
        echo '<a href="' . admin_url('admin.php?page=customers') . '">Back to Customers List</a>';
        return;
    }

    // Display confirmation form
    ?>
    <div class="wrap">
        <h1>Delete Customer</h1>
        <p>Are you sure you want to delete <strong><?php echo esc_html($customer->name); ?></strong>? This action cannot be undone.</p>
        <form method="post">
            <?php wp_nonce_field('ccm_delete_customer', 'ccm_nonce'); ?>
            <input type="submit" name="confirm_delete" value="Yes, Delete" class="button button-danger">
            <a href="<?php echo admin_url('admin.php?page=customers'); ?>" class="button">Cancel</a>
        </form>
    </div>
    <?php
}

// Shortcode for frontend
add_shortcode('customer_list', 'ccm_customer_list_shortcode');
function ccm_customer_list_shortcode() {
    ob_start();
    include plugin_dir_path(__FILE__) . 'templates/customer-list-template.php';
    return ob_get_clean();
}

// AJAX handlers
add_action('wp_ajax_ccm_search_customers', 'ccm_ajax_search_customers');
add_action('wp_ajax_nopriv_ccm_search_customers', 'ccm_ajax_search_customers');
function ccm_ajax_search_customers() {
    // Handle AJAX search/pagination
    Customer_Ajax_Handler::handle_search();
    wp_die();
}