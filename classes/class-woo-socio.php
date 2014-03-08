<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
require_once( 'facebook.php' );
/**
 * WooSocio Base Class
 *
 * All functionality pertaining to core functionality of the WooSocio plugin.
 *
 * @package WordPress
 * @subpackage WooSocio
 * @author qsheeraz
 * @since 0.0.1
 *
 * TABLE OF CONTENTS
 *
 * public $version
 * private $file
 *
 * private $token
 * private $prefix
 *
 * private $plugin_url
 * private $assets_url
 * private $plugin_path
 *
 * public $facebook
 * private $fb_user_profile
 * private $app_id
 * private $secret
 *
 * - __construct()
 * - init()
 * - woosocio_meta_box()
 * - woosocio_ajax_action()
 * - woosocio_admin_init()
 * - socialize_post()
 * - woosocio_admin_menu()
 * - woosocio_admin_styles()
 * - socio_settings()
 * - check_connection()
 * - save_app_info()
 *
 * - load_localisation()
 * - activation()
 * - register_plugin_version()
 */

class Woo_Socio {
	public $version;
	private $file;

	private $token;
	private $prefix;

	private $plugin_url;
	private $assets_url;
	private $plugin_path;
	
	public $facebook;
	private $fb_user_profile = array();
	
	private $fb_app_id;
	private $fb_secret;

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct ( $file ) {
		$this->version = '';
		$this->file = $file;
		$this->prefix = 'woo_socio_';
		$this->fb_app_id = get_option( 'fb_app_id' );
		$this->fb_secret = get_option( 'fb_app_secret' );

		/* Plugin URL/path settings. */
		$this->plugin_url = str_replace( '/classes', '', plugins_url( plugin_basename( dirname( __FILE__ ) ) ) );
		$this->plugin_path = str_replace( 'classes', '', plugin_dir_path( __FILE__ ));
		$this->assets_url = $this->plugin_url . '/assets';
		
		$this->facebook = new Facebook(array('appId'  	  => $this->fb_app_id,
  											 'secret' 	  => $this->fb_secret,
											 'status' 	  => true,
											 'cookie' 	  => true,
											 'xfbml' 	  => true,
											 'fileUpload' => true   ));
		
	} // End __construct()

	/**
	 * init function.
	 *
	 * @access public
	 * @return void
	 */
	public function init () {
		add_action( 'init', array( $this, 'load_localisation' ) );

		add_action( 'admin_init', array( $this, 'woosocio_admin_init' ) );
		add_action( 'admin_menu', array( $this, 'woosocio_admin_menu' ) );
		add_action( 'post_submitbox_misc_actions', array( $this, 'woosocio_meta_box' ) );
		add_action( 'save_post', array( $this, 'socialize_post' ));
		add_action( 'wp_ajax_my_action', array( $this, 'woosocio_ajax_action' ));
		add_action( 'wp_ajax_save_app_info', array( $this, 'save_app_info' ));

		// Run this on activation.
		register_activation_hook( $this->file, array( $this, 'activation' ) );
	} // End init()

	/**
	 * woosocio_meta_box function.
	 *
	 * @access public
	 * @return void
	 */
	public function woosocio_meta_box() {
		global $post;
		global $post_type;
		$post_id = get_the_ID();
		
		if ( $post_type == 'product' )
		{
			?>

		<div id="woosocio" class="misc-pub-section misc-pub-section-last">
			<?php
			_e( 'WooSocio:', 'woosocio' );
			//metadata_exists('post', $post_id, '_woosocio_facebook');
			$chkbox_val = metadata_exists('post', $post_id, '_woosocio_facebook') ? get_post_meta( $post_id, '_woosocio_facebook', true ) : 'checked';
			$saved_msg = ( get_post_meta( $post_id, '_woosocio_msg', true ) ? get_post_meta( $post_id, '_woosocio_msg', true ) : $post->title );
			if ( $this->check_connection() ): 
				echo "&nbsp;" . __( 'Connected as: '."<b>".$this->fb_user_profile['name']."</b>", 'woosocio' );
			else:
				echo "&nbsp;<b>" . __( 'Not Connected', 'woosocio' )."</b>";
				?>&nbsp;<a href="<?php echo admin_url( 'options-general.php?page=woosocio' ); ?>" target="_blank"><?php _e( 'Connect', 'woosocio' ); ?></a>
			<?php endif; ?>
			<div id="woosocio-form" style="display: none;">
            	<br />
                <input type="checkbox" name="chk_facebook" id="chk-facebook" <?php echo $chkbox_val; ?> />
                <label for="chk-facebook"><?php _e( '<b>Post to Facebook?</b>', 'woosocio' ); ?></label><br />
				<label for="woosocio-custom-msg"><?php _e( 'Custom Message:', 'woosocio' ); ?></label>
				<textarea name="woosocio_custom_msg" id="woosocio-custom-msg"><?php echo $saved_msg; ?></textarea>
				<a href="#" id="woosocio-form-ok" class="button"><?php _e( 'Ok', 'woosocio' ); ?></a>
				<a href="#" id="woosocio-form-hide"><?php _e( 'Cancel', 'woosocio' ); ?></a>
                <input type="hidden" name="postid" id="postid" value="<?php echo get_the_ID()?>" />
			</div>
            <a href="#" id="woosocio-form-edit"><?php _e( ' Edit', 'woosocio' ); ?></a>
		</div> 
        
		<script type="text/javascript">
        jQuery(document).ready(function($){
                $("#woosocio-form").hide();
                
            $("#woosocio-form-edit").click(function(){
				$("#woosocio-form-edit").hide();
                $("#woosocio-form").show(1000);
            });
            
            $("#woosocio-form-hide").click(function(){
                $("#woosocio-form").hide(1000);
				$("#woosocio-form-edit").show();
            });
           
		    $("#woosocio-form-ok").click(function(){
				var data = {
					action: 'my_action',
					text1: $("#woosocio-custom-msg").val(),
					postid: $("#postid").val(),
					chk_facebook: $("#chk-facebook").attr("checked")
				};
				$.post(ajaxurl, data, function(response) {
					console.log('Got this from the server: ' + response);
				});
                $("#woosocio-form").hide(1000);
				$("#woosocio-form-edit").show();
            });

        });
        </script>
		<?php 
		}
	}

	/**
	 * woosocio_ajax_action function.
	 *
	 * @access public
	 * @return void
	 */	
	public function woosocio_ajax_action($post) {
		//global $post;
		//$post_id = get_the_ID();
		if ( ! update_post_meta ($_POST['postid'], '_woosocio_msg', 
								 $_POST['text1'] ) ) 
			   add_post_meta(    $_POST['postid'], '_woosocio_msg', 
			   				     $_POST['text1'], true );
		if ( ! update_post_meta ($_POST['postid'], '_woosocio_facebook', 
								 $_POST['chk_facebook'] ) ) 
			   add_post_meta(    $_POST['postid'], '_woosocio_facebook', 
			   				     $_POST['chk_facebook'], true );

		//echo $_POST['text1'].$_POST['postid'];
		die(0);
		//die(); // this is required to return a proper result
	}
	
	/**
	 * woosocio_admin_init function.
	 *
	 * @access public
	 * @return void
	 */		
	public function woosocio_admin_init() {
       /* Register stylesheet. */
       wp_register_style( 'woosocioStylesheet', $this->assets_url.'/woosocio.css' );
   }

	/**
	 * socialize_post function.
	 *
	 * @access public
	 * @return void
	 */		
	public function socialize_post($post_id){
		global $wpdb;
	
		$post_id = get_the_ID();
		//$fb_post = get_post_meta( $post_id, '_woosocio_facebook', true );
		$fb_post = metadata_exists('post', $post_id, '_woosocio_facebook') ? get_post_meta( $post_id, '_woosocio_facebook', true ) : 'checked';
		$querystr = "
    		SELECT $wpdb->posts.* 
   			FROM   $wpdb->posts
    		WHERE  $wpdb->posts.ID = $post_id
    		AND    $wpdb->posts.post_status = 'publish' 
    		AND    $wpdb->posts.post_type = 'product'
 			";
 		$socio_post = $wpdb->get_row($querystr, OBJECT);
		
		if ( $this->check_connection() && $fb_post && !$socio_post->ID == '' )
		{
			$message = get_post_meta( $post_id, '_woosocio_msg', true );

			$socio_link = get_permalink( $post_id );
	    	
			try {
	        	$ret_obj = $this->facebook->api('/me/feed', 'POST', array(	'link' => $socio_link,
                                         									'message' => $message)
                                      									  );
				if ( ! update_post_meta ($post_id, '_woosocio_facebook', 'checked' ) ) 
			   		   add_post_meta(    $post_id, '_woosocio_facebook', 'checked', true );

    	    	//echo '<pre>Photo ID: ' . $ret_obj['id'] . '</pre>';
        		//echo '<br /><a href="' . $facebook->getLogoutUrl() . '">logout</a>';
      		} 
			catch(FacebookApiException $e) {
        		//$login_url = $this->facebook->getLoginUrl( array('scope' => 'photo_upload')); 
        		//echo 'Please <a href="' . $login_url . '">login.</a>';
				console.log($e->getType());
        		console.log($e->getMessage());
      		}   
	  }
	  else
	  	return;
	}

	/**
	 * woosocio_admin_menu function.
	 *
	 * @access public
	 * @return void
	 */		
	public function woosocio_admin_menu () {
		$page = add_options_page( 'Socio Logins', 'WooSocio Options', 'manage_options', 'woosocio', array( $this, 'socio_settings' ) );
		add_action( 'admin_print_styles-' . $page, array( $this, 'woosocio_admin_styles' ) );
	}

	/**
	 * woosocio_admin_styles function.
	 *
	 * @access public
	 * @return void
	 */			
	public function woosocio_admin_styles() {
       /*
        * It will be called only on plugin admin page, enqueue stylesheet here
        */
       wp_enqueue_style( 'woosocioStylesheet' );
   }

	/**
	 * socio_settings function.
	 *
	 * @access public
	 * @return void
	 */		
	public function socio_settings () {
		
		$filepath = $this->plugin_path.'woosocio.logins.php';
		if (file_exists($filepath))
			include_once($filepath);
		else
			die('Could not load file '.$filepath);
	}


	/**
	 * check connection function.
	 *
	 * @access public
	 */
	public function check_connection() {

 		try { 
			$this->fb_user_profile = $this->facebook->api('/me');
		 	return $this->fb_user_profile;
		} catch (FacebookApiException $e) {
			return false;
		}
	}


	/**
	 * check connection function.
	 *
	 * @access public
	 */
	public function save_app_info() {
		update_option( 'fb_app_id', $_POST['fb_app_id'] );
		update_option( 'fb_app_secret', $_POST['fb_app_secret'] );
 	}

	/**
	 * load_localisation function.
	 *
	 * @access public
	 * @return void
	 */
	public function load_localisation () {
		$lang_dir = trailingslashit( str_replace( 'classes', 'lang', plugin_basename( dirname(__FILE__) ) ) );
		load_plugin_textdomain( 'woosocio', false, $lang_dir );
	} // End load_localisation()

	/**
	 * activation function.
	 *
	 * @access public
	 * @return void
	 */
	public function activation () {
		$this->register_plugin_version();
	} // End activation()

	/**
	 * register_plugin_version function.
	 *
	 * @access public
	 * @return void
	 */
	public function register_plugin_version () {
		if ( $this->version != '' ) {
			update_option( 'woosocio' . '-version', $this->version );
		}
	} // End register_plugin_version()
} // End Class
?>