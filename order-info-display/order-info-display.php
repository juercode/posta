<?php
/*
Plugin Name: Order Info Display
Description: Display and export order information in the WordPress admin.
Version: 1.1
Author: Your Name
*/

// Add a menu item under the Settings menu
add_action('admin_menu', 'order_info_display_menu');

function order_info_display_menu() {
    add_menu_page(
        'Order Information',    // Page title
        'Order Information',    // Menu title
        'manage_options',       // Capability
        'order_info_display',   // Menu slug
        'order_info_display_page', // Callback function to display the page
        'dashicons-cart',       // Icon (use dashicons classes, e.g., 'dashicons-cart')
        26                      // Position in the menu (adjust as needed)
    );

    add_submenu_page(
        'order_info_display',       // Parent menu slug
        'Duties of the Postman',    // Page title
        'Duties of the Postman',    // Menu title
        'manage_options',           // Capability
        'duties_of_postman',        // Menu slug
        'duties_of_postman_page'    // Callback function to display the page
    );
}

// Display the order information page
function order_info_display_page() {
    // Initialize variables for filtering
    $selected_courier = '';
    $start_date = '';
    $end_date = '';

    // Check if the form is submitted
    if (isset($_POST['filter_data'])) {
        $selected_courier = sanitize_text_field($_POST['courier']);
        $start_date = sanitize_text_field($_POST['start_date']);
        $end_date = sanitize_text_field($_POST['end_date']);
    }

    // Check if the form is submitted for downloading
    if (isset($_POST['download_data'])) {
        $selected_courier_download = sanitize_text_field($_POST['courier']);
        $start_date_download = sanitize_text_field($_POST['start_date']);
        $end_date_download = sanitize_text_field($_POST['end_date']);
        download_order_data($selected_courier_download, $start_date_download, $end_date_download);
    }

    // Display the HTML form
    ?>
    <div class="wrap order-info-display">
        <h1>Order Information</h1>

        <form method="post" action="" class="order-info-form">
            <label for="courier">Select Courier:</label>
            <select name="courier" id="courier">
                <option value="Courier 1" <?php selected($selected_courier, 'Courier 1'); ?>>Courier 1</option>
                <option value="Courier 2" <?php selected($selected_courier, 'Courier 2'); ?>>Courier 2</option>
            </select>

            <label for="start_date">Start Date:</label>
            <input type="date" name="start_date" id="start_date" value="<?php echo esc_attr($start_date); ?>">

            <label for="end_date">End Date:</label>
            <input type="date" name="end_date" id="end_date" value="<?php echo esc_attr($end_date); ?>">

            <button type="submit" name="filter_data" class="button button-primary">Show Data</button>
            <button type="submit" name="download_data" class="button button-secondary">Download Data</button>
        </form>

        <table class="wp-list-table widefat fixed striped order-info-table">
            <thead>
                <tr>
                    <th>Courier</th>
                    <th>Assignment Time</th>
                    <th>First Name</th>
                    <th>Address</th>
                    <th>State</th>
                    <th>Phone</th>
                    <th>Order ID</th>
                    <th>Total Sales</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Display all records if the form is not submitted or filter based on the selected courier and date range
                global $wpdb;
                $results = $wpdb->get_results($wpdb->prepare(
                    "SELECT os.order_id, os.total_sales, pm_courier.meta_value as courier,
                    pm_assignment_time.meta_value as assignment_time,
                    wcoa.first_name, wcoa.phone, wcoa.address_1 as billing_address, wcoa.state
                    FROM {$wpdb->prefix}wc_order_stats os
                    LEFT JOIN {$wpdb->prefix}postmeta pm_courier ON os.order_id = pm_courier.post_id AND pm_courier.meta_key = '_courier'
                    LEFT JOIN {$wpdb->prefix}postmeta pm_assignment_time ON os.order_id = pm_assignment_time.post_id AND pm_assignment_time.meta_key = '_courier_assignment_time'
                    LEFT JOIN {$wpdb->prefix}wc_order_addresses wcoa ON os.order_id = wcoa.order_id AND wcoa.address_type = 'billing'
                    WHERE (%s = '' OR pm_courier.meta_value = %s)
                    AND (%s = '' OR pm_assignment_time.meta_value BETWEEN %s AND %s)
                    ORDER BY os.order_id DESC",
                    $selected_courier,
                    $selected_courier,
                    $start_date,
                    $start_date,
                    $end_date
                ));

                foreach ($results as $result) :
                    ?>
                    <tr>
                        <td><?php echo $result->courier ? esc_html($result->courier) : 'N/A'; ?></td>
                        <td><?php echo $result->assignment_time ? esc_html($result->assignment_time) : 'N/A'; ?></td>
                        <td><?php echo $result->first_name ? esc_html($result->first_name) : 'N/A'; ?></td>
                        <td><?php echo $result->billing_address ? esc_html($result->billing_address) : 'N/A'; ?></td>
                        <td><?php echo map_state_to_city($result->state); ?></td>
                        <td><?php echo $result->phone ? esc_html($result->phone) : 'N/A'; ?></td>
                        <td><?php echo $result->order_id; ?></td>
                        <td><?php echo wc_price($result->total_sales); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}

// Function to download order data based on selected courier and date range
function download_order_data($selected_courier, $start_date, $end_date) {
    global $wpdb;

    // Check if start_date and end_date are not empty, otherwise, set them to a very early and very late date
    $start_date = !empty($start_date) ? $start_date : '0000-01-01';
    $end_date = !empty($end_date) ? $end_date : '9999-12-31';

    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT os.order_id, os.total_sales, pm_courier.meta_value as courier,
        pm_assignment_time.meta_value as assignment_time,
        wcoa.first_name, wcoa.phone, wcoa.address_1 as billing_address, wcoa.state
        FROM {$wpdb->prefix}wc_order_stats os
        LEFT JOIN {$wpdb->prefix}postmeta pm_courier ON os.order_id = pm_courier.post_id AND pm_courier.meta_key = '_courier'
        LEFT JOIN {$wpdb->prefix}postmeta pm_assignment_time ON os.order_id = pm_assignment_time.post_id AND pm_assignment_time.meta_key = '_courier_assignment_time'
        LEFT JOIN {$wpdb->prefix}wc_order_addresses wcoa ON os.order_id = wcoa.order_id AND wcoa.address_type = 'billing'
        WHERE pm_courier.meta_value = %s
        AND pm_assignment_time.meta_value BETWEEN %s AND %s
        ORDER BY os.order_id DESC",
        $selected_courier,
        $start_date,
        $end_date
    ));

    $csv_data = "Nr,Courier,Assignment Time,First Name,Address,State,Phone,Order ID,Total Sales\n";

    $row_number = 1; // Initialize row number
    $total_sales = 0; // Initialize total sales

    foreach ($results as $result) :
        // Use assignment_time directly without conversion
        $csv_data .= "{$row_number},{$result->courier},{$result->assignment_time},{$result->first_name},{$result->billing_address}," . map_state_to_city($result->state) . ",{$result->phone},{$result->order_id},{$result->total_sales}\n";

        $total_sales += $result->total_sales; // Accumulate total sales
        $row_number++; // Increment row number
    endforeach;

    // Add the row for total sales at the end
    $csv_data .= ",,,,,,Total Sales,{$total_sales}\n";

    // Download CSV file
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename=order_data.csv');

    // Make sure no output is sent before the headers
    ob_clean();
    flush();

    echo $csv_data;
    exit();
}

// Function to map state to city
function map_state_to_city($state) {
    switch ($state) {
        case 'AL-01':
            return 'Berat';
        case 'AL-02':
            return 'Durres';
        case 'AL-03':
            return 'Elbasan';
        case 'AL-04':
            return 'Fier';
        case 'AL-05':
            return 'Gjirokastër';
        case 'AL-06':
            return 'Korçë';
        case 'AL-07':
            return 'Kukes';
        case 'AL-08':
            return 'Lezhe';
        case 'AL-09':
            return 'Diber';
        case 'AL-10':
            return 'Shkoder';
        case 'AL-11':
            return 'Tirana';
        case 'AL-12':
            return 'Vlore';
        default:
            return 'N/A';
    }
}

include_once plugin_dir_path(__FILE__) . 'duties-of-postman-page.php';

?>
