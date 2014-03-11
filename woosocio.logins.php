<?php

// Get User ID
//$login_user = null;
//$login_user = $_POST['fuser'];
global $woosocio;
if(isset($_GET['action']) && $_GET['action'] === 'logout'){
    $woosocio->facebook->destroySession();
}
$fb_user = $woosocio->facebook->getUser();


// Login or logout url will be needed depending on current user state.

if ($fb_user) {
	$next_url = array( 'next' => admin_url().'options-general.php?page=woosocio&logout=yes&action=logout' );
  $logoutUrl = $woosocio->facebook->getLogoutUrl( $next_url );
} else {
  $statusUrl = $woosocio->facebook->getLoginStatusUrl();
  $loginUrl = $woosocio->facebook->getLoginUrl(array('scope' => 'publish_actions'));
}

if ($fb_user) {
  try {
	//$fb_user = $woosocio->facebook->getUser();
    $user_profile = $woosocio->facebook->api('/me');
	//$logoutUrl = $woosocio->facebook->getLogoutUrl();
  } catch (FacebookApiException $e) {
	  //$woosocio->facebook->setSession(null);
    error_log($e);
    $woosocio->facebook->destroySession();
	//$loginUrl = $woosocio->facebook->getLoginUrl(array('scope' => 'publish_actions'));
  }
}

?>
<!doctype html>
<html> <!--xmlns:fb="http://www.facebook.com/2008/fbml">-->
  <head>
    <title>WooSocio Options</title>
</head>
<body>
  <h1>WooSocio Logins</h1>
  <h3 id="woosocio"><?php _e( 'WooSocio', 'woosocio' ) ?></h3>
  <p>
  <?php esc_html_e( 'Connect your site to social networking sites and automatically share new products with your friends.', 'woosocio' ) ?>
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
		$("#app-info").show(1000);
	});
});
</script>