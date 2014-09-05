<?php

global $woosocio, $is_IE;
if(isset($_GET['action']) && $_GET['action'] === 'logout'){
    $woosocio -> facebook -> destroySession();
}
$fb_user = $woosocio -> facebook -> getUser();

// Login or logout url will be needed depending on current user state.

if ($fb_user) {
	$next_url = array( 'next' => admin_url().'admin.php?page=woosocio&logout=yes&action=logout' );
  	$logoutUrl = $woosocio -> facebook -> getLogoutUrl( $next_url );
	$user_profile = $woosocio -> facebook -> api('/me');
	$user_pages = $woosocio -> facebook -> api("/me/accounts");
} else {
  	$statusUrl = $woosocio->facebook->getLoginStatusUrl();
  	$loginUrl = $woosocio->facebook->getLoginUrl(array('scope' => 'publish_stream, manage_pages, publish_actions'));
}

?>
<!doctype html>
<html xmlns:fb="http://www.facebook.com/2008/fbml">
  <head>
    <title><?php _e( 'WooSocio Options', 'woosocio' ) ?></title>
</head>
<body>
<div class="woosocio_wrap">
  <h1><?php _e( 'WooSocio Logins', 'woosocio' ) ?></h1>
  <h3 id="woosocio"><?php _e( 'WooSocio', 'woosocio' ) ?></h3>
  <p>
  <?php esc_html_e( 'Connect your site to social networking sites and automatically share new products with your friends.', 'woosocio' ) ?>
  </p>
  <p style="font-size:12px">
  <?php esc_html_e( "You can use like/share buttons without connecting or App ID", 'woosocio' ) ?>
  </p>
  <?php 
	if ($is_IE){
	  echo "<p style='font-size:18px; color:#F00;'>" . __( 'Important Notice:', 'woosocio') . "</p>";
	  echo "<p style='font-size:16px; color:#F00;'>" . 
	  		__( 'You are using Internet Explorer. This plugin may not work properly with IE. Please use any other browser.', 'woosocio') . "</p>";
	  echo "<p style='font-size:16px; color:#F00;'>" . __( 'Recommended: Google Chrome.', 'woosocio') . "</p>";
	}
  ?>
  <div id="woosocio-services-block">
	<div class="woosocio-service-entry" >
		<div id="facebook" class="woosocio-service-left">
			<a href="https://www.facebook.com" id="service-link-facebook" target="_top">Facebook</a><br>
		</div>
		<div class="woosocio-service-right">
			<?php if($fb_user!==0):?>
            <?php _e( 'Connected as:', 'woosocio') ?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <a class="woosocio-profile-link" href="https://www.facebook.com" target="_top"><?php echo $user_profile['name'] ?></a><br>
            <a id="pub-disconnect-button1" class="woosocio-add-connection button" href="<?php echo $logoutUrl; ?>" target="_top"><?php _e('Disconnect', 'woosocio')?></a><br>
            <?php else: ?>
            <!--Not Connected...&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-->
            <a id="facebook" class="woosocio-add-connection button" href="<?php echo esc_url( $loginUrl ); ?>" target="_top"><?php _e('Connect', 'woosocio')?></a>
            <img id="working" src="<?php echo $woosocio->assets_url.'/spinner.gif' ?>" alt="Wait..." height="22" width="22" style="display: none;"><br>
            <?php endif; ?>

            <?php 
			if (get_option( 'fb_app_id' ) && get_option( 'fb_app_secret' )): 
			    echo '<a id="app-details" href="javascript:">' . __('Show App Details', 'woosocio') . '</a>';
				echo '<div id="app-info" style="display: none;">';
			else:            
            	echo '<div id="app-info">';
			endif;
			?>
            <table class="form-table">
            <tr valign="top">
	  			<th scope="row"><label><?php _e('Your App ID:', 'woosocio') ?></label></th>
	  			<td>
	  				<input type="text" name="app_id" id="fb-app-id" placeholder="<?php _e('App ID', 'woosocio') ?>" value="<?php echo get_option( 'fb_app_id' ); ?>"><br>
                    <p style="font-size:10px"><?php _e("Don't have an app? You can get from ", 'woosocio') ?>
                    <a href="https://developers.facebook.com/apps" target="_new" style="font-size:10px">developers.facebook.com/apps</a>
	  			</td>
	  		</tr>
            <tr valign="top">
	  			<th scope="row"><label><?php _e('Your App Secret:', 'woosocio') ?></label></th>
	  			<td>
	  				<input type="text" name="app_secret" id="fb-app-secret" placeholder="<?php _e('App Secret', 'woosocio') ?>" value="<?php echo get_option( 'fb_app_secret' ); ?>"><br>
                    <p style="font-size:11px"><?php _e('Need more help? ', 'woosocio') ?>
                    <a href="https://developers.facebook.com/docs/opengraph/getting-started/#create-app" target="_new" style="font-size:11px"><?php _e('Click here', 'woosocio') ?></a>
	  			</td>
	  		</tr>
            <tr valign="top">
     	  		<th scope="row"></th>
	  			<td>
                	<a id="btn-save" class="button-primary button" href="javascript:"><?php _e('Save', 'woosocio') ?></a>
	  			</td>
	  		</tr>
            </table>
            </div>
		</div>
	</div>
    			
		<?php
        if($fb_user!==0){
			$user_sign = $user_profile['id'].'_fb_page_id';
			//echo get_option( $user_sign);
			$fb_page_value = get_option( $user_sign, $user_profile['id'] );
            echo "<h4>" . __( 'Post to:', 'woosocio' ) . "</h4>";
        ?>
            <img src="http://graph.facebook.com/<?php echo $user_profile['id'] ?>/picture" alt="No Image">
            <input type="radio" name="pages" value="<?php echo $user_profile['id'] ?>" <?php echo ($fb_page_value == $user_profile['id'])?'checked':''?>><?php _e('Personal Page (Wall)', 'woosocio') ?><br>
        <?php
        $page_names = $user_pages['data'];
        foreach($page_names as $key => $page)
        {
        ?>
            <img src="http://graph.facebook.com/<?php echo $page['id'] ?>/picture" alt="No Image">
            <input type="radio" name="pages" value="<?php echo $page['id'] ?>" <?php echo ($fb_page_value == $page['id']) ? 'checked':''?>><?php echo $page['name'] ?><br>
        <?php
        }}	//$woosocio->pa($user_profile);		 
        ?>
        <img id="working-page" src="<?php echo $woosocio->assets_url.'/spinner.gif' ?>" alt="Wait..." height="15" width="15" style="display: none;"><br>
        
  
    <div class="woosocio-service-entry" style="font-size:18px; color:#03C">
    <?php
        _e('* WooSocio Pro version *', 'woosocio'); echo "</br></br>";
		_e('* Post as page owner rather highlighted post.', 'woosocio'); echo "</br>";
		_e('* post to multiple pages at once.', 'woosocio'); echo "</br>";
		_e('* Post products multiple times (Post more than once)', 'woosocio'); echo "</br>";
		_e('* Bulk posts to pages (multiple posts at once)', 'woosocio'); echo "</br>";
		_e('* Bulk edit message', 'woosocio'); echo "</br>";
		_e('* Bulk like/share button on/off option', 'woosocio'); echo "</br>";
		_e('* Rich product page', 'woosocio'); echo "</br>";
		_e('* And many more to come...', 'woosocio'); echo "</br>";
        
	?>
    </div>
  </div>
    <!-- Right Area Widgets -->  

    <div class="woosocio-about-us">
    	<!-- WooSocio Pro Features -->
    	<!--<div class="box">
          <ul>
            <li type="circle">WooSocio Pro</li>
            <li>Post as page owner rather personal user.
                No more highlighted posts</li>
            <li>Bulk Edit</li>
            <li>Bulk Delete</li>
            <li>Bulk Post to facebook</li>                            
          </ul>        
        </div>-->
    	<!-- Like Box WooSocio on Facebook -->
        <div class="box">
        	<div class="fb-like-box" 
            	 data-href="https://www.facebook.com/WooSocio" 
            	 data-colorscheme="light" 
                 data-show-faces="true" 
                 data-header="false" 
                 data-stream="false" 
                 data-show-border="false">
            </div>
        </div>
            <div id="fb-root"></div>
			<script>
			(function(d, s, id) {
              var js, fjs = d.getElementsByTagName(s)[0];
              if (d.getElementById(id)) return;
              js = d.createElement(s); js.id = id;
              js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";
              fjs.parentNode.insertBefore(js, fjs);
            }(document, 'script', 'facebook-jssdk'));
            </script>

            <!-- Hire Me PPH Widget --> 
            <!--<div id="pph-hireme"></div>
            <script type="text/javascript">
            (function(d, s) {
                var useSSL = 'https:' == document.location.protocol;
                var js, where = d.getElementsByTagName(s)[0],
                js = d.createElement(s);
                js.src = (useSSL ? 'https:' : 'http:') +  '//www.peopleperhour.com/hire/4041333554/85162.js?width=300&height=255&orientation=vertical&theme=light&hourlies=38934&rnd='+parseInt(Math.random()*10000, 10);
                try { where.parentNode.insertBefore(js, where); } catch (e) { if (typeof console !== 'undefined' && console.log && e.stack) { console.log(e.stack); } }
            }(document, 'script'));
            </script>-->
    	<div class="box" align="center"  style="font-family:'Times New Roman', Times, serif">
            <!-- Donation -->
            <h2><?php _e( 'Liked WooSocio?', 'woosocio') ?></h2>
           <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
            <input type="hidden" name="cmd" value="_s-xclick">
            <input type="hidden" name="hosted_button_id" value="YNF4H9FJY4HU4">
            <input type="image" src="https://www.paypalobjects.com/en_US/GB/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal – The safer, easier way to pay online.">
            <img alt="" border="0" src="https://www.paypalobjects.com/en_GB/i/scr/pixel.gif" width="1" height="1">
           </form>
		</div>
        <div class="box" align="center" style="font-family:'Times New Roman', Times, 'serif'; font-size:18px">
        	<?php _e('Need more help? Please contact: ', 'woosocio') ?><a href="mailto:qsheeraz@yahoo.com?Subject=WooSocio%20Help" target="_top">qsheeraz@yahoo.com</a>
        </div>
    </div>  
</div>
  </body>
</html>
<script type="text/javascript">
jQuery(document).ready(function($){
		//$("#app-info").hide();
		
	$("#btn-save").click(function(){
		$("#working").show();
		
		var data = {
			action: 'save_app_info',
			fb_app_id: $("#fb-app-id").val(),
			fb_app_secret: $("#fb-app-secret").val()
		};
		
		$.post(ajaxurl, data, function(response) {
			console.log('Got this from the server: ' + response);
		location.reload();
		});	
		
		$("#app-info").hide(2000);
	});

	$("input:radio[name=pages]").click(function() {
		$("#working-page").show();
			
		var data = {
			action: 'update_page_info',
			fb_page_id: $(this).val()
		};
		
		$.post(ajaxurl, data, function(response) {
			console.log('Got this from the server: ' + response);
			$("#working-page").hide();
			alert(response);
		});
	});
	
	$("#app-details").click(function(){
		$("#app-info").toggle(1000);
	});
});
</script>