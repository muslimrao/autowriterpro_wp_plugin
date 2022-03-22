<?php

/**
 * Token authentication
 */
function at_rest_authenticate_endpoint($request_data)
{
    global $wpdb;
    $table = $wpdb->prefix.'auto_write';
    $results = $wpdb->get_row("SELECT keycode FROM $table");
    $_POST = $_POST;
    $token = isset($_POST['token']) ? $_POST['token'] : false;
    if(!$token){
        $data = array(
            "status" => "error",
            "message" => "Required parameter missing.",
        );
        return $data;
    }
    if($results->keycode !="" && $results->keycode == $token){
        $data = array(
            "status" => "success",
            "token" => $results->keycode
        );
        return $data;
    }else{
        $data = array(
            "status" => "error",
            "message" => "Invalid API Key",
        );
        return $data;
    }
}
function at_rest_init()
{
    // route url: domain.com/wp-json/$namespace/$route
    $namespace = 'api/v2';
    $route     = 'autowriterpro_authenticate';
    register_rest_route($namespace, $route, array(
        'methods'   => 'POST',
        'callback'  => 'at_rest_authenticate_endpoint'
    ));
}
add_action('rest_api_init', 'at_rest_init');
/**
 * Get categories and authors
 */
function at_rest_get_data_endpoint($request_data)
{
    global $wpdb;
    $table = $wpdb->prefix.'auto_write';
    $results = $wpdb->get_row("SELECT keycode FROM $table");
    $_POST = $_POST;
    $token = isset($_POST['token']) ? $_POST['token'] : false;
  
    if(!$token){
        $data = array(
            "status" => "error",
            "message" => "Required parameter missing.",
        );
        return $data;
    }
    if($results->keycode !="" && $results->keycode == $token){
        $args = array(
            'hide_empty' => 0,
            'pad_counts' => true
        );
        $data = array(
            "status" => "success",
            "categories" => get_categories($args),
            "authors" => get_users( array( 'fields' => array( 'user_login', 'ID' ), 'role__in' => array( 'Author' )  ) ), 
        );
        return $data;
    }else{
        $data = array(
            "status" => "error",
            "message" => "Invalid token",
        );
        return $data;
    }
}
function at_get_data_init()
{
    // route url: domain.com/wp-json/$namespace/$route
    $namespace = 'api/v2';
    $route     = 'autowriterpro_get_post_info';
    register_rest_route($namespace, $route, array(
        'methods'   => 'POST',
        'callback'  => 'at_rest_get_data_endpoint'
    ));
}
add_action('rest_api_init', 'at_get_data_init');
/**
 * create new post
 */
function at_rest_new_POST_endpoint($request_data)
{
    global $wpdb;
    $table = $wpdb->prefix.'auto_write';
    $results = $wpdb->get_row("SELECT keycode FROM $table");
    $_POST = $_POST;
    $data = array();
    $table        = 'wp_POSTs';
    $post_type = 'post';
    // Fetching values from API
    $_POST = $_POST;
    $token = isset($_POST['token']) ? $_POST['token'] : '';
    $author = isset($_POST['author_id']) ? $_POST['author_id'] : '';
    $post_title = isset($_POST['post_title']) ? $_POST['post_title'] : '';
    $the_content = isset($_POST['post_content']) ? $_POST['post_content'] : '';
    $cats = isset($_POST['cats']) ? $_POST['cats'] : '';
    $the_excerpt = isset($_POST['post_excerpt']) ? $_POST['post_excerpt'] : '';
    $feature_img = isset($_POST['featured_image']) ? $_POST['featured_image'] : '';
    $post_date = isset($_POST['post_date']) ? $_POST['post_date'] : '';
    $post_status = isset($_POST['post_status']) ? $_POST['post_status'] : '';
    $template_id = isset($_POST['template_id']) ? $_POST['template_id'] : '';
    // return $feature_img;
    // die();
    if($author!='' && $post_title!='' && $token !='' && $template_id !=''){
        if($results->keycode !="" && $results->keycode == $_POST['token']){
            // Create post object
            $date1 = $post_date;
            $date = DateTime::createFromFormat('m-d-Y', $date1);
            $dat2 = $date->format('Y-m-d');
            $newformat = $dat2;
            // return $dat2;
            // die();
            // $today = date("Y-m-d") ;
            // if ($dat2 > $today ){
            //     $time = strtotime($post_date);
            //     $newformat = date('Y-m-d',$time);
            // }else if($dat2 == $today){
            //     $newformat = date('Y-m-d');
            // }
            // else{
            //     $time = strtotime($post_date);
            //     $newformat = date('Y-m-d',$time);
            // }

            // $x = str_replace("-","/",$post_date);
            // $newformat = date("d-m-Y", strtotime($x));

            $my_POST = array(
                'post_title' => wp_strip_all_tags( $post_title),
                'post_content' => $the_content,
                'post_author' => $author,
                'post_excerpt' => $the_excerpt,
                'post_type' => 'post',
                'post_date' => $newformat,
                'post_status' => $post_status,
            );
            $post_id = wp_insert_POST( $my_POST );
            
            add_POST_meta( $post_id, 'createdby', 'c_autowrite');
            add_POST_meta( $post_id, 'template_id', $template_id);

            // Set post categories
            $catss = explode(',', $cats);
            if (!empty($catss)) {
                // wp_set_object_terms( $post_id, $catss, 'category', false );
                if ($post_type == 'post') {
                wp_set_object_terms( $post_id, $catss, 'category', false );
                }
                else{
                wp_set_object_terms( $post_id, $catss, 'Categories', false );   // Executes if posttype is other
                }
            }
            // Set featured Image
            
            if(isset($feature_img) && $feature_img != ''){
                // wp_insert_attachment
                include_once(ABSPATH . 'wp-admin/includes/admin.php' );
                require_once(ABSPATH . 'wp-admin/includes/media.php');
                require_once(ABSPATH . 'wp-admin/includes/file.php');
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                function url_get_contents ($Url) {
                    if (!function_exists('curl_init')){ 
                        die('CURL is not installed!');
                    }

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $Url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $output = curl_exec($ch);
                    curl_close($ch);
                    return $output;
                }
                function ajax_check_image_url($url){
                    // $url = isset($_POST['url']) ? $_POST['url'] : false;
                    if(empty($url) || !$url){
                        echo json_encode(array('status' => 'error', 'message' => "Invalid URL Provided."));
                    }
            
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL,$url);
                    // curl_setopt($ch, CURLOPT_NOBODY, 1);
                    curl_setopt($ch, CURLOPT_FAILONERROR, 1);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    
                    
                    if(curl_exec($ch)!==FALSE) {
                        $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
                        $content_length = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
                        $content_redirect = curl_getinfo($ch, CURLINFO_REDIRECT_COUNT );
                        
                        $imageTypes = array('image/png','image/jpeg','image/gif','image/webp');
                
                        if(in_array($content_type,$imageTypes) && $content_redirect == 0 && $content_length >0){
                            // echo json_encode(array('status' => 'success', 'message' => 'URL is a valid image'));
                            $output = curl_exec($ch);
                            curl_close($ch);
                            return $output;
                        }
                        else {
                            return false;
                        }
                        
                    }
                    else {
                        return false;
                    }
                    curl_close($ch);
                }
                
                
                // Add Featured Image to Post
                if(ajax_check_image_url($feature_img)){
                    $image_url        = $feature_img; // Define the image URL here
                    $image_name       = basename($feature_img);
                    $upload_dir       = wp_upload_dir(); // Set upload folder
                    $image_data       = ajax_check_image_url($feature_img); // Get image data
                    $unique_file_name = wp_unique_filename( $upload_dir['path'], $image_name ); // Generate unique name
                    $filename         = basename( $unique_file_name ); // Create image file name
    
                    // Check folder permission and define file location
                    if( wp_mkdir_p( $upload_dir['path'] ) ) {
                    $file = $upload_dir['path'] . '/' . $filename;
                    } else {
                    $file = $upload_dir['basedir'] . '/' . $filename;
                    }
                    // $file = $upload_dir['baseurl'] . '/' . $filename;
    
                    // Create the image  file on the server
                    $put_con = file_put_contents( $file, $image_data );
    
                    // Check image file type
                    $wp_filetype = wp_check_filetype( $filename, null );
    
                    // Set attachment data
                    $attachment = array(
                        'post_mime_type' => $wp_filetype['type'],
                        'post_title'     => sanitize_file_name( $filename ),
                        'post_content'   => '',
                        'post_status'    => 'inherit'
                    );
    
                    // Create the attachment
                    $attach_id = wp_insert_attachment( $attachment, $file, $post_id );
    
                    // Include image.php
                    require_once(ABSPATH . 'wp-admin/includes/image.php');
    
                    // Define attachment metadata
                    $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
    
                    // Assign metadata to attachment
                    wp_update_attachment_metadata( $attach_id, $attach_data );
    
                    // And finally assign featured image to post
                    set_POST_thumbnail( $post_id, $attach_id );
                }else{
                    $data['image_error']= "Sorry, Can't upload the image.";
                }



                // if(!$attach_data){
                //     $data['warning']= "Sorry, can't upload the image.";
                // }
                
            }
            if ($post_id) {
                $data['post_id']= $post_id;  
                $data['status']= 'success';  
                $data['message']= 'Post added successfully.';  
                return $data;
            }
            else{
                $data['status']= 'error';
                $data['message']= 'Post failed.';
                return $data;
            }
        }else{
            $data['status']= 'error';
            $data['message']= 'Invalid token.';
            return $data;
        }
    }else{
        $data['status']= 'error';
        $data['status']= 'token, post_title, author_id and template_id are required parameters.';
        return ($data);
    }
}
function at_newpost_init()
{
    // route url: domain.com/wp-json/$namespace/$route
    $namespace = 'api/v2';
    $route     = 'autowriterpro_add_new_post';
    register_rest_route($namespace, $route, array(
        'methods'   => 'POST',
        'callback'  => 'at_rest_new_POST_endpoint'
    ));
}
add_action('rest_api_init', 'at_newpost_init');



/**
 * Update post
 */
function at_rest_update_post_endpoint($request_data)
{
    global $wpdb;
    $table = $wpdb->prefix.'auto_write';
    $results = $wpdb->get_row("SELECT keycode FROM $table");
    $_POST = $_POST;
    $data = array();
    $table        = 'wp_POSTs';
    $post_type = 'post';
    // Fetching values from API
    $token = isset($_POST['token']) ? $_POST['token'] : '';
    $post_id = isset($_POST['post_id']) ? $_POST['post_id'] : '';
    $author = isset($_POST['author_id']) ? $_POST['author_id'] : '';
    $post_title = isset($_POST['post_title']) ? $_POST['post_title'] : '';
    $the_content = isset($_POST['post_content']) ? $_POST['post_content'] : '';
    $cats = isset($_POST['cats']) ? $_POST['cats'] : '';
    $the_excerpt = isset($_POST['post_excerpt']) ? $_POST['post_excerpt'] : '';
    $feature_img = isset($_POST['featured_image']) ? $_POST['featured_image'] : '';
    $post_date = isset($_POST['post_date']) ? $_POST['post_date'] : '';
    $post_status = isset($_POST['post_status']) ? $_POST['post_status'] : '';









    // return $feature_img;
    // die();
    if($author!='' && $post_title!='' && $token !='' && $post_id !=''){
        if($results->keycode !="" && $results->keycode == $token){
            // Create post object
            // $time = strtotime($post_date);

            // $newformat = date('Y-m-d',$time);
            // $x = str_replace("-","/",$post_date);
            // $newformat = date("d-m-Y", strtotime($x));
            $date1 = $post_date;
            $date = DateTime::createFromFormat('m-d-Y', $date1);
            $dat2 = $date->format('Y-m-d');
            $newformat = $dat2;

            if(isset($post_title) && $post_title != ''){
                $update_title = array(
                    'ID'           => $post_id,
                    'post_title'   => wp_strip_all_tags( $post_title),
                );
                wp_update_post( $update_title );
            }
            if(isset($author) && $author != ''){
                $update_author = array(
                    'ID'           => $post_id,
                    'post_author'   => $author,
                );
                wp_update_post( $update_author );
            }
            if(isset($the_content) && $the_content != ''){
                $update_content = array(
                    'ID'           => $post_id,
                    'post_content'   => $the_content,
                );
                wp_update_post( $update_content );
            }
            if(isset($post_date) && $post_date != ''){
                $update_date = array(
                    'ID'           => $post_id,
                    'post_date'   => $newformat,
                );
                wp_update_post( $update_date );
            }
            if(isset($post_status) && $post_status != ''){
                $update_status = array(
                    'ID'           => $post_id,
                    'post_status'   => $post_status,
                );
                wp_update_post( $update_status );
            }

            // Set post categories
            $catss = explode(',', $cats);
            if (!empty($catss)) {
                // wp_set_object_terms( $post_id, $catss, 'category', false );
                if ($post_type == 'post') {
                wp_set_object_terms( $post_id, $catss, 'category', false );
                }
                else{
                wp_set_object_terms( $post_id, $catss, 'Categories', false );   // Executes if posttype is other
                }
            }
            // Set featured Image
            // $url = $feature_img;
            
            if(isset($feature_img) && $feature_img != ''){
                // wp_insert_attachment
                include_once(ABSPATH . 'wp-admin/includes/admin.php' );
                require_once(ABSPATH . 'wp-admin/includes/media.php');
                require_once(ABSPATH . 'wp-admin/includes/file.php');
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                function url_get_contents ($Url) {
                    if (!function_exists('curl_init')){ 
                        die('CURL is not installed!');
                    }

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $Url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $output = curl_exec($ch);
                    curl_close($ch);
                    return $output;
                }
                function ajax_check_image_url($url){
                    // $url = isset($_POST['url']) ? $_POST['url'] : false;
                    if(empty($url) || !$url){
                        echo json_encode(array('status' => 'error', 'message' => "Invalid URL provided"));
                    }
            
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL,$url);
                    // curl_setopt($ch, CURLOPT_NOBODY, 1);
                    curl_setopt($ch, CURLOPT_FAILONERROR, 1);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    
                    
                    if(curl_exec($ch)!==FALSE) {
                        $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
                        $content_length = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
                        $content_redirect = curl_getinfo($ch, CURLINFO_REDIRECT_COUNT );
                        
                        $imageTypes = array('image/png','image/jpeg','image/gif','image/webp');
                
                        if(in_array($content_type,$imageTypes) && $content_redirect == 0 && $content_length >0){
                            // echo json_encode(array('status' => 'success', 'message' => 'URL is a valid image'));
                            $output = curl_exec($ch);
                            curl_close($ch);
                            return $output;
                        }
                        else {
                            return false;
                        }
                    }
                    else {
                        return false;
                    }
                    curl_close($ch);
                }
                // Add Featured Image to Post
                if(ajax_check_image_url($feature_img)){
                    $image_url        = $feature_img; // Define the image URL here
                    $image_name       = basename($feature_img);
                    $upload_dir       = wp_upload_dir(); // Set upload folder
                    $image_data       = ajax_check_image_url($feature_img); // Get image data
                    $unique_file_name = wp_unique_filename( $upload_dir['path'], $image_name ); // Generate unique name
                    $filename         = basename( $unique_file_name ); // Create image file name
    
                    // Check folder permission and define file location
                    if( wp_mkdir_p( $upload_dir['path'] ) ) {
                    $file = $upload_dir['path'] . '/' . $filename;
                    } else {
                    $file = $upload_dir['basedir'] . '/' . $filename;
                    }
                    // $file = $upload_dir['baseurl'] . '/' . $filename;
    
                    // Create the image  file on the server
                    $put_con = file_put_contents( $file, $image_data );
    
                    // Check image file type
                    $wp_filetype = wp_check_filetype( $filename, null );
    
                    // Set attachment data
                    $attachment = array(
                        'post_mime_type' => $wp_filetype['type'],
                        'post_title'     => sanitize_file_name( $filename ),
                        'post_content'   => '',
                        'post_status'    => 'inherit'
                    );
    
                    // Create the attachment
                    $attach_id = wp_insert_attachment( $attachment, $file, $post_id );
    
                    // Include image.php
                    require_once(ABSPATH . 'wp-admin/includes/image.php');
    
                    // Define attachment metadata
                    $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
    
                    // Assign metadata to attachment
                    wp_update_attachment_metadata( $attach_id, $attach_data );
    
                    // And finally assign featured image to post
                    set_POST_thumbnail( $post_id, $attach_id );
                }else{
                    $data['image_error']= "Sorry, can't upload the image.";
                }



                // if(!$attach_data){
                //     $data['warning']= "Sorry, can't update the image.";
                // }
                
            }
            else {
                delete_post_thumbnail($post_id);
            }
            
            if ($post_id) {
                $data['post_id']= $post_id;  
                $data['status']= 'success';  
                $data['message']= 'Post edited successfully.';  
                return $data;
            }
            else{
                $data['status']= 'error';
                $data['message']= 'Post failed.';
                return $data;
            }
        }else{
            $data['status']= 'error';
            $data['message']= 'Invalid token.';
            return $data;
        }
    }else{
        $data['status']= 'error';
        $data['status']= 'token, post_id, post_title, author_id are required parameters.';
        return $data;
    }
}
function at_updatepost_init()
{
    // route url: domain.com/wp-json/$namespace/$route
    $namespace = 'api/v2';
    $route     = 'autowriterpro_update_post';
    register_rest_route($namespace, $route, array(
        'methods'   => 'POST',
        'callback'  => 'at_rest_update_post_endpoint'
    ));
}
add_action('rest_api_init', 'at_updatepost_init');