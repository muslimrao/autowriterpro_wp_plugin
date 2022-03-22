<?php
/**
 * Plugin Name: Blogs.Network
 * Plugin URI: https://blogs.network/
 * Description: Quickly and easily publish to multiple WordPress sites through Blogs.Network's ultimate guest posting & multi site publishing platform
 * Version: 1.0
 * Author: Blogs.Network
 * Author URI: https://blogs.network/
 */

/*
*Create Table in Database
*/ 
require('inc/function.php');


register_activation_hook( __FILE__, 'database_keytable');

function database_keytable() {

    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    //token
    $token_table = $wpdb->prefix . 'auto_write';
    $sql1 = "CREATE TABLE `$token_table` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `keycode` VARCHAR(220) NOT NULL,
        PRIMARY KEY(id)
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1;
    ";

    if ($wpdb->get_var("SHOW TABLES LIKE '$token_table'") != $token_table) {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql1);
    }
}

/*
*Admin Menu
*/
function auto_write_setup_menu(){
    add_menu_page( 'BlogsNetwork', 'BlogsNetwork', 'manage_options', 'BlogsNetwork_setup', 'func_init' );
    add_submenu_page( 'BlogsNetwork_setup', 'Writer Posts', 'Writer Posts', 'manage_options', 'BlogsNetwork_all_posts', 'all_posts');
}

add_action('admin_menu', 'auto_write_setup_menu');
function func_init(){
    require('inc/template.php');
}

function all_posts(){
    require('inc/posts-list.php');
}

/*
*Plugin's scripts and styles.
*/
function admin_enqueue_scripts() {
    wp_enqueue_script( 'custom-js', plugin_dir_url( __FILE__ ) . 'assets/script.js?v='.time() );
    wp_enqueue_style( 'style-css', plugin_dir_url( __FILE__ ) . 'assets/style.css?v='.time() );
    // wp_enqueue_script( 'datatables-js', 'https://cdn.datatables.net/v/dt/dt-1.11.3/datatables.min.js' );
    // wp_enqueue_style( 'datatables-css', 'https://cdn.datatables.net/v/dt/dt-1.11.3/datatables.min.css' );
}
add_action( 'admin_enqueue_scripts', 'admin_enqueue_scripts');

 

/*
*Generating and inserting key in data base
*/
function RandomString()
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randstring = '';
    for ($i = 0; $i < 20; $i++) {
        $randstring = $characters[rand(0, strlen($characters))];
    }
    return $randstring;
}

global $wpdb;
$table = $wpdb->prefix.'auto_write';
$result = $wpdb->get_results("SELECT * FROM $table");

if (isset($_POST['generate_key'])) {

    if($result){
        echo "key is already created.";
    }else{
        $key = hash_hmac('md5', RandomString(), 'auth');
        $table = $wpdb->prefix.'auto_write';
        $data = array('keycode' => $key);
        $format = null;
        $wpdb->insert($table,$data,$format);
        $my_id = $wpdb->insert_id;
    }
}
?>