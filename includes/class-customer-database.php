<?php
class Customer_Database {
    public static function add_customer($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'custom_customers';

        // Validate inputs
        if (!is_numeric($data['phone'])) {
            wp_die('Phone must be numeric.');
        }
        if (!preg_match('/^[a-zA-Z0-9]+$/', $data['cr_number'])) {
            wp_die('CR Number must be alphanumeric.');
        }

        // Check email uniqueness in WP users
        if (email_exists($data['email'])) {
            wp_die('Email already exists in WordPress users.');
        }

        // Insert customer
        $wpdb->insert($table, array(
            'name' => sanitize_text_field($data['name']),
            'email' => sanitize_email($data['email']),
            'phone' => sanitize_text_field($data['phone']),
            'dob' => sanitize_text_field($data['dob']),
            'gender' => sanitize_text_field($data['gender']),
            'cr_number' => sanitize_text_field($data['cr_number']),
            'address' => sanitize_textarea_field($data['address']),
            'city' => sanitize_text_field($data['city']),
            'country' => sanitize_text_field($data['country']),
            'status' => sanitize_text_field($data['status'])
        ));

        // Create WP user
        wp_create_user($data['email'], $data['phone'], $data['email']);
        $user = get_user_by('email', $data['email']);
        $user->set_role('contributor');
    }

    public static function get_customers($per_page = 10, $page = 1, $search = '') {
        global $wpdb;
        $table = $wpdb->prefix . 'custom_customers';
        $offset = ($page - 1) * $per_page;
        $where = $search ? $wpdb->prepare("WHERE name LIKE %s OR email LIKE %s", '%' . $search . '%', '%' . $search . '%') : '';
        
        // Get total count for pagination
        $total_count = $wpdb->get_var("SELECT COUNT(*) FROM $table $where");
        $total_pages = ceil($total_count / $per_page);
        
        // Get paginated results
        $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table $where LIMIT %d OFFSET %d", $per_page, $offset));
        foreach ($results as $customer) {
            $customer->age = self::calculate_age($customer->dob);
        }
        
        // Return array with customers and pagination metadata
        return array(
            'customers' => $results,
            'total_pages' => $total_pages,
            'current_page' => $page,
            'total_count' => $total_count
        );
    }

    public static function get_customer($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'custom_customers';
        $customer = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
        if ($customer) {
            $customer->age = self::calculate_age($customer->dob);
        }
        return $customer;
    }

    public static function update_customer($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'custom_customers';
        // Similar validation as add
        $wpdb->update($table, $data, array('id' => $id));
    }

    public static function delete_customer($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'custom_customers';
        $wpdb->delete($table, array('id' => $id));
    }

    private static function calculate_age($dob) {
        $birth_date = new DateTime($dob);
        $current_date = new DateTime();
        $age = $current_date->diff($birth_date)->y;
        return $age;
    }
}