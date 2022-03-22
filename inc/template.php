<div class="wrap">

      <h2>Blogs.Network Setup (Generate Key)</h2>
      <div class="aw-container">
          <form action="" method="post" class="aw-form">
              <?php
            //   print_r(get_the_category(1)->name);
            //   die();
                global $wpdb;
                $table = $wpdb->prefix.'auto_write';
                $results = $wpdb->get_results("SELECT * FROM $table");

                if($results){
                    foreach($results as $result){

                        echo "<p>This is your key, no need to generate this again. <small style='color:red; font-weight:bold;'>(The API key will be only generated once. Don't share this API key to anyone)</small></p> ";
                        echo '<label class="tooltip" for="token"><input id="token" type="text" value="'.$result->keycode.'" name="token"> <button id="BlogsNetwork_copykey">
                        <span class="tooltiptext" id="myTooltip">Copy to clipboard</span>
                        Copy
                        </button></label>';
                    }

                }else{
                    echo "<p>Generate your authentication key once and for all.</p>";
                    echo '<label for="aw-submit"><input id="aw-submit" type="submit" value="generate key" name="generate_key"></label>';
                }
              ?>
          </form>
    </div>
</div>