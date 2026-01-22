<?php
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Customer_List_Table extends WP_List_Table {
    public function __construct() {
        parent::__construct(array(
            'singular' => 'customer',
            'plural' => 'customers',
            'ajax' => false
        ));
    }

    public function get_columns() {
        return array(
            'cb' => '<input type="checkbox" />',
            'name' => 'Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'age' => 'Age',
            'status' => 'Status',
            'actions' => 'Actions'
        );
    }

    public function prepare_items() {
        $per_page = 10;
        $current_page = $this->get_pagenum();
        $search = isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : '';
        
        // Get data from updated Customer_Database
        $data = Customer_Database::get_customers($per_page, $current_page, $search);
        $customers = $data['customers']; // Extract the array of customer objects
        $total_items = $data['total_count']; // Use the accurate total count from the database

        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page' => $per_page,
            'total_pages' => $data['total_pages'] // For better pagination display
        ));

        $this->_column_headers = array($this->get_columns(), array(), $this->get_sortable_columns());
        $this->items = $customers; // Now $customers is the array of objects
    }

    public function column_default($item, $column_name) {
        return $item->$column_name; // $item is now a customer object
    }

    public function column_actions($item) {
        $edit_url = admin_url('admin.php?page=edit-customer&id=' . $item->id);
        $delete_url = admin_url('admin.php?page=delete-customer&id=' . $item->id);
        return '<a href="' . esc_url($edit_url) . '">Edit</a> | <a href="' . esc_url($delete_url) . '">Delete</a>';
    }
}