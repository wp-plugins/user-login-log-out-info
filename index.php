<?php
/**
 * Plugin Name: User  Login logout  info
 * plugin URI: http://www.woothemes.com/Login_logout_info
 * Description: show the user login time logout time and time
 * Author: Vikas Gautam
 */
/* 9780024354*/

add_action('wp_login','vikas_last_login_time');
function vikas_last_login_time($login) {
    global $user_ID;
    $user = get_user_by('login', $login);
    $time_start = time();
	echo date_default_timezone_get();
	//date_default_timezone_set("Asia/Kolkata");
    $start_date = date("Y-m-d h:i:s");
    update_user_meta($user->ID, 'start_time', $time_start);
	update_user_meta($user->ID, 'start_date', $start_date);
}

add_action('wp_logout', 'vikas_time_on_logout');
function vikas_time_on_logout($user_id) {
    global $user_ID;
    $user = get_user_by('id', $user_ID);
	
	echo date_default_timezone_get();
	//date_default_timezone_set("Asia/Kolkata");
    $end_date = date("Y-m-d h:i:s");
	update_user_meta($user->ID, 'end_date', $end_date);
	
    $time_end = time();
    $time_start = get_user_meta($user->ID, 'start_time', true);
    $total_time = (intval($time_end) - intval($time_start));
    $total_time = round($total_time/60);
    $total_all_time = get_user_meta($user->ID, 'total_time', true);
    $total_time = $total_all_time + $total_time;
    update_user_meta($user->ID, 'total_time', $total_time);


    $logged_in_amount = get_user_meta($user->ID, 'logged_in_amount', true);
    $logged_in_amount = $logged_in_amount + 1;
    update_user_meta($user->ID, 'logged_in_amount', $logged_in_amount);

    $average_time = ($total_time/$logged_in_amount);
    update_user_meta($user->ID, 'average_time', $average_time);
}

add_filter('manage_users_columns', 'vikas_user_minutes_column');
function vikas_user_minutes_column($columns) {
    $columns['total_time'] = 'Total Time in  Minutes';
    $columns['logged_in_amount'] = 'Total time of Logins';
    $columns['average_time'] = 'Ave. Time Min./Login';
	
	$columns['start_date'] = 'Start Date';
	$columns['end_date'] = 'End date';
	 
    return $columns;
}
 
add_action('manage_users_custom_column',  'vikas_user_minutes_column_content', 10, 3);
function vikas_user_minutes_column_content($value, $column_name, $user_id) {
    $output = " ";
    $user = get_userdata( $user_id );
    if ( 'total_time' == $column_name )
        $output .= ($user->total_time);
    if ( 'logged_in_amount' == $column_name )
        $output .= ($user->logged_in_amount);
    if ( 'average_time' == $column_name )
        $output .= ($user->average_time);
		
	if('start_date' == $column_name)
       $output .=($user->start_date);	
	if('end_date' == $column_name)
       $output .=($user->end_date);
	   
    return $output;
}

add_action('admin_footer', 'vikas__custom_user_buttons');
function vikas__custom_user_buttons() {
    $screen = get_current_screen();
    if ( $screen->id != "users" )   // Only add to users.php page
        return;
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('<option>').val('del_user_meta').text('Delete User Logs').appendTo("select[name='action']");
            $('<option>').val('export_user_meta').text('Export User Logs').appendTo("select[name='action']");
        });
    </script>
    <?php
} 

add_action('load-users.php', 'vikas_delete_users_info');
function vikas_delete_users_info() {
    if(isset($_GET['action']) && $_GET['action'] === 'del_user_meta') {  // Check if our custom action was selected
        $del_users = $_GET['users'];  // Get array of user id's which were selected for meta deletion
        if ($del_users) {  // If any users were selected
            foreach ($del_users as $del_user) {
            delete_user_meta($del_user, 'logged_in_amount');
            delete_user_meta($del_user, 'total_time');
            delete_user_meta($del_user, 'average_time');
		    delete_user_meta($del_user, 'start_date');
		    delete_user_meta($del_user, 'end_date');
            }
        }
    }
}

