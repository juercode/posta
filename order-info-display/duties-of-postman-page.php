<?php
// duties-of-postman-page.php
function duties_of_postman_page() {
    global $wpdb;

    // Retrieve data for the report
    $report_data = $wpdb->get_results(
        "SELECT pm_courier.meta_value as courier, COUNT(os.order_id) as item_count, SUM(os.total_sales) as total_price
        FROM {$wpdb->prefix}wc_order_stats os
        LEFT JOIN {$wpdb->prefix}postmeta pm_courier ON os.order_id = pm_courier.post_id AND pm_courier.meta_key = '_courier'
        WHERE os.status = 'wc-processing'  -- Include only orders with 'processing' status
        GROUP BY pm_courier.meta_value"
    );

    
    ?>
    <div class="wrap">
        <h1>Duties of the Postman - Courier Report</h1>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Courier</th>
                    <th>Number of Items Assigned</th>
                    <th>Total Price</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($report_data as $row) : ?>
                    <tr>
                        <td><?php echo esc_html($row->courier); ?></td>
                        <td><?php echo esc_html($row->item_count); ?></td>
                        <td><?php echo wc_price($row->total_price); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}
?>
