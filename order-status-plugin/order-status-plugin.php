<?php
/*
Plugin Name: Order Status Plugin
Description: Display a page with buttons for changing order status.
Version: 1.0
Author: Your Name
*/

// Hook to add a custom query variable
add_filter('query_vars', 'add_order_status_query_var');

function add_order_status_query_var($vars) {
    $vars[] = 'order_status';
    return $vars;
}

// Hook to handle the custom query parameter
add_action('template_redirect', 'handle_order_status_query_param');

function handle_order_status_query_param() {
    if (is_user_logged_in() && current_user_can('manage_options')) {
        $order_status = get_query_var('order_status');
        if ($order_status) {
            // Display a page with buttons for order completion and failure
            display_order_status_page($order_status);
            exit();
        }
    }
}

// Function to display the order status page
function display_order_status_page($order_status) {
    // Get the order object
    $order_id = $order_status;
    $order = wc_get_order($order_id);

    // Output HTML for the order status page
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detajet e porosise</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #333;
            color: #fff;
            padding: 10px;
            text-align: center;
        }

        main {
            max-width: 800px;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1, h2 {
            color: #333;
        }

        p {
            color: #555;
        }

        button {
            background-color: #4caf50;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            margin-right: 10px;
        }

        button:hover {
            background-color: #45a049;
        }

        button#failOrderBtn {
            background-color: #f44336;
        }

        button#failOrderBtn:hover {
            background-color: #d32f2f;
        }

        button#assignCourier1Btn {
            background-color: #2196F3;
        }

        button#assignCourier1Btn:hover {
            background-color: #1565C0;
        }

        button#assignCourier2Btn {
            background-color: #9C27B0;
        }

        button#assignCourier2Btn:hover {
            background-color: #7B1FA2;
        }

        #orderpagetitle {
            color: white;
        }
    </style>
    <body>

    <header>
    <h1 id="orderpagetitle">Detajet e fatures</h1>
</header>

    <main>
    <p>ID e porosise: <?php echo $order_id; ?></p>

    <h2>Detajet e fatures</h2>
    <p>Emri i klientit: <?php echo $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(); ?></p>
    <p>Email: <?php echo $order->get_billing_email(); ?></p>
    <p>Telefoni: <?php echo $order->get_billing_phone(); ?></p>
    <p>Adresa: <?php echo $order->get_formatted_billing_address(); ?></p>

    <h2>Informacioni i korrierit</h2>
    <p>Korrieri: <?php echo get_post_meta($order_id, '_courier', true); ?></p>

    <h2>Detajet e porosise</h2>
    <p>Total: <?php echo wc_price($order->get_total()); ?></p>
    <p>Statusi i porosise: <?php echo wc_get_order_status_name($order->get_status()); ?></p>

    <button id="completeOrderBtn">Kompleto porosine</button>
    <button id="failOrderBtn">Porosia deshtoj</button>

    <button id="assignCourier1Btn">Jepja korrierit Courier 1</button>
    <button id="assignCourier2Btn">Jepja korrierit Courier 2</button>

    <h2>Shto nje koment</h2>
    <textarea id="orderComment" rows="4" placeholder="Shkruaj komentin tuaj ketu..."></textarea>
    <button id="commentNowBtn">Komento tani</button>
</main>

        <script>

            // Attach event listener to the "Comment now" button
             document.getElementById('commentNowBtn').addEventListener('click', function() {
            // Call a function to handle adding comments
            handleAddComment('<?php echo $order_status; ?>', document.getElementById('orderComment').value);
            });

            // Attach event listeners to the buttons
            document.getElementById('completeOrderBtn').addEventListener('click', function() {
                // Call a function to handle order completion
                handleOrderStatus('<?php echo $order_status; ?>', 'completed');
            });

            document.getElementById('failOrderBtn').addEventListener('click', function() {
                // Call a function to handle order failure
                handleOrderStatus('<?php echo $order_status; ?>', 'failed');
            });

            document.getElementById('assignCourier1Btn').addEventListener('click', function() {
                // Call a function to handle assigning to Courier 1
                handleAssignCourier('<?php echo $order_status; ?>', 'Courier 1');
            });

            document.getElementById('assignCourier2Btn').addEventListener('click', function() {
                // Call a function to handle assigning to Courier 2
                handleAssignCourier('<?php echo $order_status; ?>', 'Courier 2');
            });


        // Function to handle adding comments
            function handleAddComment(orderId, comment) {
            // Simulate an API request to add a comment to the order notes
            // In a real-world scenario, this would be an AJAX request to your server

            // Get the order object
            var data = new FormData();
            data.append('action', 'add_order_comment');
            data.append('order_id', orderId);
            data.append('comment', comment);

            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: data
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(response => {
                if (response.success) {
                    console.log(`Comment added to Order ID ${orderId}.`);
                    alert('Comment added successfully!');

                    // Reload the page to reflect the changes
                    location.reload();
                } else {
                    console.error('Error adding comment.', response.data.message);
                    alert('Error adding comment. Please try again.');
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                alert('Error adding comment. Please try again.');
            });
        }


            // Function to handle order status
            function handleOrderStatus(orderId, status) {
                // Simulate an API request to update the order status
                // In a real-world scenario, this would be an AJAX request to your server

                // Get the order object
                var data = new FormData();
                data.append('action', 'update_order_status');
                data.append('order_id', orderId);
                data.append('status', status);

                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: data
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(response => {
                    if (response.success) {
                        console.log(`Order ID ${orderId} marked as ${status}.`);
                        alert(`Order marked as ${status}!`);

                        // Redirect to a thank you page or any other page as needed
                        window.location.href = '<?php echo get_permalink(get_option('woocommerce_thanks_page_id')); ?>';
                    } else {
                        console.error('Error updating order status.', response.data.message);
                        alert('Error updating order status. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    alert('Error updating order status. Please try again.');
                });
            }

            // Function to handle assigning to couriers
            function handleAssignCourier(orderId, courier) {
                // Get the current date in YYYY-MM-DD format
                var currentDate = new Date().toISOString().split('T')[0];

                // Simulate an API request to update the order data
                // In a real-world scenario, this would be an AJAX request to your server

                // Get the order object
                var data = new FormData();
                data.append('action', 'assign_courier');
                data.append('order_id', orderId);
                data.append('courier', courier);
                data.append('assignment_time', currentDate); // Pass the current date

                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: data
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(response => {
                    if (response.success) {
                        console.log(`Order ID ${orderId} assigned to ${courier}.`);
                        alert(`Order assigned to ${courier}!`);

                        // Reload the page to reflect the changes (you can implement a more efficient approach if needed)
                        location.reload();
                    } else {
                        console.error('Error assigning courier.', response.data.message);
                        alert('Error assigning courier. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    alert('Error assigning courier. Please try again.');
                });
            }
        </script>

    </body>
    </html>
    <?php
}

// Hook to handle AJAX request for adding comments
add_action('wp_ajax_add_order_comment', 'add_order_comment_callback');

function add_order_comment_callback() {
    $order_id = isset($_POST['order_id']) ? absint($_POST['order_id']) : 0;
    $comment = isset($_POST['comment']) ? sanitize_text_field($_POST['comment']) : '';

    // Get the order object
    $order = wc_get_order($order_id);

    if ($order) {
        // Add a comment to the order notes
        $order->add_order_note($comment);

        // Return a success response
        wp_send_json_success();
    } else {
        // Log additional information about the error
        error_log('Order Status Plugin: Invalid order ID or order not found.');

        // Return an error response with more details
        wp_send_json_error(array('message' => 'Invalid order ID or order not found.'), 500);
    }
}




// Hook to handle AJAX request for updating order status
add_action('wp_ajax_update_order_status', 'update_order_status_callback');

function update_order_status_callback() {
    $order_id = isset($_POST['order_id']) ? absint($_POST['order_id']) : 0;
    $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';

    // Get the order object
    $order = wc_get_order($order_id);

    if ($order) {
        // Update the order status
        $order->update_status($status);

        // Return a success response
        wp_send_json_success();
    } else {
        // Log additional information about the error
        error_log('Order Status Plugin: Invalid order ID or order not found.');

        // Return an error response with more details
        wp_send_json_error(array('message' => 'Invalid order ID or order not found.'), 500);
    }
}

// Hook to handle AJAX request for assigning courier
add_action('wp_ajax_assign_courier', 'assign_courier_callback');

function assign_courier_callback() {
    error_log('Assign Courier Callback Triggered');

    $order_id = isset($_POST['order_id']) ? absint($_POST['order_id']) : 0;
    $courier = isset($_POST['courier']) ? sanitize_text_field($_POST['courier']) : '';
    $assignment_time = isset($_POST['assignment_time']) ? sanitize_text_field($_POST['assignment_time']) : '';

    error_log('Order ID: ' . $order_id);
    error_log('Courier: ' . $courier);
    error_log('Assignment Time: ' . $assignment_time);

    // Update order meta with courier information and assignment time
    update_post_meta($order_id, '_courier', $courier);
    update_post_meta($order_id, '_courier_assignment_time', $assignment_time);

    // Return a success response
    wp_send_json_success();
}
