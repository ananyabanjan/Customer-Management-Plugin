<?php
class Customer_Ajax_Handler {
    public static function handle_search() {
        $page = intval($_POST['page']);
        $per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 10; // Default to 10 if not sent
        $search = sanitize_text_field($_POST['search']);
        
        // Call the updated get_customers method
        $customers_data = Customer_Database::get_customers($per_page, $page, $search);
        
        // Send the full response (customers array + pagination metadata)
        wp_send_json($customers_data);
    }
}