<div class="wrap">
   <h2>Blogs.Network Post List</h2>
   <div class="aw-container bg-blank p-0">
      <?php
         global $wpdb;
        //  $results = $wpdb->get_results( "select post_id from $wpdb->postmeta where meta_value = 'c_autowrite'", ARRAY_A );
         $args = array(
           'hide_empty' => 0,
           'pad_counts' => true
         );
         $all_cats = get_categories($args);
         $order = (isset($_GET['or']) && $_GET['or'] != "") ? $_GET['or'] : 'desc';
         $cat_id = (isset($_GET['cat']) && $_GET['cat'] != "") ? $_GET['cat'] : 0;
         function title_filter( $where, $wp_query ){
            global $wpdb;
            if ( $search_term = $wp_query->get( 'search_prod_title' ) ) {
                $where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'%' . esc_sql( like_escape( $search_term ) ) . '%\'';
            }
            return $where;
         }
         ?>
      <div id="aw-filters" class="tablenav top">
         <form method="GET" class="alignleft actions W-100">
            <input type="hidden" name="page" value="BlogsNetwork_all_posts">
            <p class="search-box">
               <label class="screen-reader-text" for="post-search-input">Search Posts:</label>
               <input type="search" id="post-search-input" name="s" value="<?php _e(isset($_GET['s']) ? $_GET['s'] : " ") ?>">
               <input type="submit" id="search-submit" class="button" value="Search Posts">
            </p>
            <select name="or" id="aw-filter-by-date">
               <option value="">Sort</option>
               <?php 
                  if(isset($_GET['cat'])){
                     switch($_GET['or']){
                        case "DESC":
                        //    $ids = array_reverse($results); ?>
                              <option value="ASC">Oldest </option>
                              <option selected value="DESC">Newest</option>
                        <?php break;
                        case "ASC":
                        //    $ids = $results; ?>
                              <option selected value="ASC">Oldest</option>
                              <option value="DESC">Newest </option>
                           <?php break;
                        default:
                        //    $ids = $results; ?>
                              <option value="ASC">Oldest</option>
                              <option value="DESC">Newest </option>
                     <?php
                     }
                  }else{
                    //  $ids = $results; ?>
                        <option value="ASC">Oldest</option>
                        <option value="DESC">Newest </option>
                     <?php
                  }
                  ?>
            </select>
            <select name="cat" id="aw-cat-filter" class="postform">
               <option value="0">All Categories</option>
               <?php 
                  foreach($all_cats as $c){
                    if($cat_id == $c->cat_ID){ $c_sel = 'selected';}else{$c_sel = '';}
                    echo'<option '.$c_sel.' class="'.$c->slug.'" value="'.$c->cat_ID.'">'.$c->name.'</option>';
                  }
                  ?>
            </select>
            <input type="submit" id="post-query-submit" class="button" value="Filter">		
         </form>
      </div>
      <table class="wp-list-table widefat fixed striped table-view-list posts">
         <thead>
            <tr>
               <th scope="col" id="title" class="manage-column column-title column-primary"><span>Title</span><span class="sorting-indicator"></span></th>
               <th scope="col" id="author" class="manage-column column-author">Author</th>
               <th scope="col" id="categories" class="manage-column column-categories">Categories</th>
               <th scope="col" id="tags" class="manage-column column-tags">Tags</th>
               <th scope="col" id="date" class="manage-column column-date "><span>Date</span><span class="sorting-indicator"></span></th>
            </tr>
         </thead>
         <tbody id="the-list">
            <?php
            //    foreach($ids as $result){
            //      $id = $result['post_id'];
                 ?>
               <?php
                  $paged =  isset($_GET['paged']) ? $_GET['paged'] : 1;
                  $search_q = isset($_GET['s']) ? $_GET['s'] : '';
                  $post_args = array(
                    'order' => $order,
                    'cat' => $cat_id,
                    'search_prod_title' => $search_q,
                    'posts_per_page' => 15,
                    'paged' => $paged,
                    'meta_query' => array(
                        array(
                            'key' => 'createdby',
                            'value' => 'c_autowrite',
                            'compare' => '=',
                        )
                    )
                  );
                  add_filter( 'posts_where', 'title_filter', 10, 2 );
                  $all_post = new WP_Query($post_args);
                  remove_filter( 'posts_where', 'title_filter', 10, 2 );
                  if($all_post->have_posts()){
                     while ( $all_post->have_posts() ) : $all_post->the_post();
                     $this_id = get_the_ID();
                     // if($this_id != $id){
                     //   continue;get count 
                     // }
                     $my_post = get_post($this_id);
                     $author = get_the_author_meta( 'display_name', $my_post->post_author );
                     $date = $my_post->post_date;
                     $status = $my_post->post_status;
                     $edit = get_edit_post_link($this_id);
                     $view = get_post_permalink($this_id);
                     $cats = array();
                     $tags_arr = array();
                     //   print_r(wp_get_post_tags($this_id));
                     $tag_detail = wp_get_post_tags($this_id);//$post->ID
                     foreach($tag_detail as $tg){
                        $tags_arr[] = $tg->name;
                     }
                     $tags = implode(", ",$tags_arr);
                     $category_detail = get_the_category($this_id);//$post->ID
                     foreach($category_detail as $cd){
                        $cats[] = $cd->cat_name;
                     }
                     $categories = implode(", ",$cats);
                     $title = get_the_title();
                     ?>
                     <tr id="<?php _e($this_id)?>" class="iedit author-other level-0 <?php _e($this_id)?> type-post status-<?php _e($status)?> format-standard has-post-thumbnail hentry category-custom category-new">
                        <td class="title column-title has-row-actions column-primary page-title" data-colname="Title">
                           <div class="locked-info"><span class="locked-avatar"></span> <span class="locked-text"></span></div>
                           <strong><a class="row-title" href="<?php _e($edit)?>" aria-label="“<?php _e($title)?>” (Edit)"><?php _e($title)?></a></strong>
                           <div class="row-actions"><span class="edit"><a href="<?php _e($edit)?>" aria-label="Edit “<?php _e($title)?>”">Edit</a> | </span><span class="view"><a href="<?php _e($view)?>" rel="bookmark" aria-label="View “<?php _e($title)?>”">View</a></span></div>
                           <button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button>
                        </td>
                        <td class="author column-author" data-colname="Author"><a href="edit.php?post_type=post&amp;author=<?php _e($my_post->post_author)?>"><?php _e($author)?></a></td>
                        <td class="categories column-categories" data-colname="Categories"><?php _e($categories)?></td>
                        <td class="tags column-tags" data-colname="Tags"><span aria-hidden="true"><?php _e($tags ? $tags : "—")?></span></td>
                        <td class="date column-date" data-colname="Date"><?php _e($status)?><br><?php _e($date)?></td>
                     </tr>
                  <?php
                     endwhile;
                  }else {
                     ?><p class="text-center">Sorry there are no posts to show.</p><?php
                  }
                  ?>
            <?php
            //    }
               ?>
         </tbody>
         <tfoot>
            <tr>
               <th scope="col" class="manage-column column-title column-primary"><span>Title</span><span class="sorting-indicator"></span></th>
               <th scope="col" class="manage-column column-author">Author</th>
               <th scope="col" class="manage-column column-categories">Categories</th>
               <th scope="col" class="manage-column column-tags">Tags</th>
               <th scope="col" class="manage-column column-date"><span>Date</span><span class="sorting-indicator"></span></th>
            </tr>
         </tfoot>
      </table>
      <span class="d-block text-left"><?php echo $all_post->found_posts;?> items found</span>
      <div class="pagination justify-content-center">
         <?php 
            echo paginate_links( array(
                  // 'base'         => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
                  'total'        => $all_post->max_num_pages,
                  'current'      => max( 1, $paged ),
                  'format'       => '?paged=%#%',
                  'show_all'     => false,
                  'type'         => 'plain',
                  'prev_next'    => true,
                  'prev_text'    => sprintf( '<i></i> %1$s', __( 'Previous', 'text-domain' ) ),
                  'next_text'    => sprintf( '%1$s <i></i>', __( 'Next', 'text-domain' ) ),
                  'add_args'     => true,
            ) );
            wp_reset_postdata();
         ?>
      </div>
   </div>
</div>