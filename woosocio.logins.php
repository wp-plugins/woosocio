<?php

global $woosocio;
if(isset($_GET['action']) && $_GET['action'] === 'logout'){
    $woosocio->facebook->destroySession();
}
$fb_user = $woosocio->facebook->getUser();


// Login or logout url will be needed depending on current user state.

if ($fb_user) {
	$next_url = array( 'next' => admin_url().'options-general.php?page=woosocio&logout=yes&action=logout' );
  	$logoutUrl = $woosocio->facebook->getLogoutUrl( $next_url );
	$user_profile = $woosocio->facebook->api('/me');
} else {
  	$statusUrl = $woosocio->facebook->getLoginStatusUrl();
  	$loginUrl = $woosocio->facebook->getLoginUrl(array('scope' => 'publish_actions'));
}

?>
<!doctype html>
<html> <!--xmlns:fb="http://www.facebook.com/2008/fbml">-->
  <head>
    <title>WooSocio Options</title>
</head>
<body>
<div class="woosocio_wrap">
  <h1>WooSocio Logins</h1>
  <h3 id="woosocio"><?php _e( 'WooSocio', 'woosocio' ) ?></h3>
  <p>
  <?php esc_html_e( 'Connect your site to social networking sites and automatically share new products with your friends.', 'woosocio' ) ?>
  </p>
  <p style="font-size:12px">
  <?php esc_html_e( "You can use like/share buttons without connecting or App ID", 'woosocio' ) ?>
  </p>
  <div id="woosocio-services-block">
	<div class="woosocio-service-entry" >
		<div id="facebook" class="woosocio-service-left">
			<a href="https://www.facebook.com" id="service-link-facebook" target="_top">Facebook</a><br>
		</div>
		<div class="woosocio-service-right">
			<?php if($fb_user!==0): ?>
            Connected as:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <a class="woosocio-profile-link" href="https://www.facebook.com" target="_top"><?php echo $user_profile['name'] ?></a><br>
            <a id="pub-disconnect-button1" class="woosocio-add-connection button" href="<?php echo $logoutUrl; ?>" target="_top">Disconnect</a><br>
            <?php else: ?>
            <!--Not Connected...&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-->
            <a id="facebook" class="woosocio-add-connection button" href="<?php echo esc_url( $loginUrl ); ?>" target="_top">Connect</a>
            <img id="working" src="<?php echo $woosocio->assets_url.'/spinner.gif' ?>" alt="Wait..." height="22" width="22" style="display: none;"><br>
            <?php endif; ?>
            
            <?php 
			if (get_option( 'fb_app_id' ) && get_option( 'fb_app_secret' )): 
			    echo '<a id="app-details" href="javascript:">Show App Details</a>';
				echo '<div id="app-info" style="display: none;">';
			else:            
            	echo '<div id="app-info">';
			endif;
			?>
            <table class="form-table">
            <tr valign="top">
	  			<th scope="row"><label>Your App ID:</label></th>
	  			<td>
	  				<input type="text" name="app_id" id="fb-app-id" placeholder="App ID" value="<?php echo get_option( 'fb_app_id' ); ?>"><br>
                    <p style="font-size:10px">Don't have an app? You can get from 
                    <a href="https://developers.facebook.com/apps" target="_new" style="font-size:10px">developers.facebook.com/apps</a>
	  			</td>
	  		</tr>
            <tr valign="top">
	  			<th scope="row"><label>Your App Secret:</label></th>
	  			<td>
	  				<input type="text" name="app_secret" id="fb-app-secret" placeholder="App Secret" value="<?php echo get_option( 'fb_app_secret' ); ?>"><br>
                    <p style="font-size:11px">Need more help? 
                    <a href="https://developers.facebook.com/docs/opengraph/getting-started/#create-app" target="_new" style="font-size:11px">Click here</a>
	  			</td>
	  		</tr>
            <tr valign="top">
     	  		<th scope="row"></th>
	  			<td>
                	<a id="btn-save" class="button-primary button" href="javascript:">Save</a>
	  			</td>
	  		</tr>
            </table>
            </div>
		</div>
	</div>
  </div>
    <!-- Right Area Widgets -->  
    <div class="woosocio-about-us">
        <!--<div class="box">-->
            <!-- Hire Me PPH Widget --> 
            <div id="pph-hireme"></div>
            <script type="text/javascript">
            (function(d, s) {
                var useSSL = 'https:' == document.location.protocol;
                var js, where = d.getElementsByTagName(s)[0],
                js = d.createElement(s);
                js.src = (useSSL ? 'https:' : 'http:') +  '//www.peopleperhour.com/hire/4041333554/85162.js?width=300&height=255&orientation=vertical&theme=light&hourlies=38934&rnd='+parseInt(Math.random()*10000, 10);
                try { where.parentNode.insertBefore(js, where); } catch (e) { if (typeof console !== 'undefined' && console.log && e.stack) { console.log(e.stack); } }
            }(document, 'script'));
            </script>
    	<div class="box" align="center"  style="font-family:'Times New Roman', Times, serif">
            <!-- Donation -->
            <h2>Like WooSocio?</h2>
           <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
            <input type="hidden" name="cmd" value="_s-xclick">
            <input type="hidden" name="hosted_button_id" value="YNF4H9FJY4HU4">
            <input type="image" src="https://www.paypalobjects.com/en_US/GB/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal – The safer, easier way to pay online.">
            <img alt="" border="0" src="https://www.paypalobjects.com/en_GB/i/scr/pixel.gif" width="1" height="1">
           </form>
		</div>
        <div class="box" align="center" style="font-family:'Times New Roman', Times, 'serif'; font-size:18px">
        	Need more help? Please contact: <a href="mailto:qsheeraz@yahoo.com?Subject=WooSocio%20Help" target="_top">qsheeraz@yahoo.com</a>
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
	
	$("#app-details").click(function(){
		$("#app-info").toggle(1000);
	});
});
</script>