<?php

    if( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit();

    global $wpdb;
    $table = $wpdb->prefix.'auto_write';
    // $posts = $wpdb->prefix . 'auto_write_posts';
    $wpdb->query( "DROP TABLE IF EXISTS $table" );
    // $wpdb->query( "DROP TABLE IF EXISTS $posts" );
    // delete_option("my_plugin_db_version");

?>