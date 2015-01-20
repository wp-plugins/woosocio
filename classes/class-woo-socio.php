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
 * - products_list()
 * - check_connection()
 * - save_app_info()
 * - update_page_info()
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
	public $fb_user_profile = array();
	public $fb_user_pages = array();
	
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
		add_action( 'wp_ajax_update_page_info', array( $this, 'update_page_info' ));
		add_action( 'woocommerce_single_product_summary', array( $this, 'show_sharing_buttons'), 50, 2  );
		add_filter( 'manage_edit-product_columns', array($this, 'woosocio_columns'), 998);
		//add_filter( 'manage_product_posts_columns' , array($this, 'woosocio_columns'),9990,1);
		add_action( 'manage_product_posts_custom_column', array($this, 'woosocio_custom_product_columns') );
		//add_filter( 'manage_post_posts_columns' , array($this, 'woosocio_columns'),9990);
		

		// Run this on activation.
		register_activation_hook( $this->file, array( $this, 'activation' ) );
	} // End init()
	
	function pa($arr){

		echo '<pre>';
		print_r($arr);
		echo '</pre>';
	}

		/**
	 * woosocio_columns function.
	 *
	 * @access public
	 * @return columns
	 */
	function woosocio_columns($columns) {
		if ( isset( $_REQUEST['list'] ) && $_REQUEST['list'] == 'woosocio' ) {
			echo '<style>';
			echo '.actions {display: none;}';
			echo '.search-box {display: none;}';
			echo '.subsubsub {display: none;}';
			echo '</style>';

		    $columns = array();
			$columns["cb"] = "<input type=\"checkbox\" />";
			$columns["woo_name"] = __( 'Name', 'woosocio' );
			$columns["like_btn"] = __('Like/ Share Button?', 'woosocio');
			$columns["fb_post"] = __('Posted to Facebook?', 'woosocio');
			$columns["custom_msg"] = __('Custom Message', 'woosocio');

			return $columns;
		}
		else
			return $columns;
	}
		
	/**
	 * woosocio_custom_product_columns function.
	 *
	 * @access public
	 * @return void
	 */
	function woosocio_custom_product_columns( $column ) {
	global $post, $woocommerce, $the_product;

	if ( empty( $the_product ) || $the_product->id != $post->ID )
		$the_product = get_product( $post );

	switch ($column) {
		case "woo_name" :
			$edit_link = get_edit_post_link( $post->ID );
			$title = _draft_or_post_title();
			$post_type_object = get_post_type_object( $post->post_type );
			$can_edit_post = current_user_can( $post_type_object->cap->edit_post, $post->ID );

			echo '<strong><a class="row-title" href="'.$edit_link.'">' . $title.'</a>';
		break;
		case "like_btn" :
			$woo_like_fb = metadata_exists('post', $post -> ID, '_woosocio_like_facebook') ? get_post_meta( $post -> ID, '_woosocio_like_facebook', true ) : 'No';
			echo $woo_like_fb == 'checked' ? '<img src="'.$this->assets_url.'/yes.png" alt="Yes" width="25">' : '<img src="'.$this->assets_url.'/no.png" alt="No" width="25">';
		break;
		case "fb_post" :
			$woo_post_fb = metadata_exists('post', $post -> ID, '_woosocio_facebook') ? get_post_meta( $post -> ID, '_woosocio_facebook', true ) : 'No';
			echo $woo_post_fb == 'checked' ? '<img src="'.$this->assets_url.'/yes.png" alt="Yes" width="25">' : '<img src="'.$this->assets_url.'/no.png" alt="No" width="25">';			
		break;
		case "custom_msg" :
			echo get_post_meta( $post -> ID, '_woosocio_msg', true );
		break;
	}
}

	/**
	 * show_sharing_buttons function.
	 *
	 * @access public
	 * @return void
	 */
	public function show_sharing_buttons() {
		$post_id = get_the_ID();
		$socio_link = get_permalink( $post_id );
		$fb_like = metadata_exists('post', $post_id, '_woosocio_like_facebook') ? get_post_meta( $post_id, '_woosocio_like_facebook', true ) : 'checked';
		if ($fb_like) {
			if($this->fb_app_id)
				$fb_appid_option = '&appId='.$this->fb_app_id;
		  ?>
		  <div class="fb-like" data-href="<?php echo $socio_link; ?>" data-layout="button_count" data-action="like" data-show-faces="true" data-share="true"></div>
		  <div id="fb-root"></div>
		  <script>(function(d, s, id) {
			var js, fjs = d.getElementsByTagName(s)[0];
			if (d.getElementById(id)) return;
			js = d.createElement(s); js.id = id;
			js.src = "//connect.facebook.net/en_US/all.js#xfbml=1<?php echo $fb_appid_option; ?>";
			fjs.parentNode.insertBefore(js, fjs);
		  }(document, 'script', 'facebook-jssdk'));</script> 
		  <?php
		}
	}

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
			$content = '';

			_e( 'WooSocio:', 'woosocio' );
			//metadata_exists('post', $post_id, '_woosocio_facebook');
			$like_chkbox_val = metadata_exists('post', $post_id, '_woosocio_like_facebook') ? get_post_meta( $post_id, '_woosocio_like_facebook', true ) : 'checked';
			$chkbox_val = metadata_exists('post', $post_id, '_woosocio_facebook') ? get_post_meta( $post_id, '_woosocio_facebook', true ) : 'checked';
			$saved_msg = ( get_post_meta( $post_id, '_woosocio_msg', true ) ? get_post_meta( $post_id, '_woosocio_msg', true ) : $post->title );
			if ( $this->check_connection() ): 
				echo '&nbsp;<img src="'.$this->assets_url.'/connected.gif" alt="Connected "> as: '."<b>".$this->fb_user_profile['name']."</b>";
				//echo "&nbsp;" . __( 'Connected as: '."<b>".$this->fb_user_profile['name']."</b>", 'woosocio' );
			else:
				echo "&nbsp;<b>" . __( 'Not Connected', 'woosocio' )."</b>";
				?>&nbsp;<a href="<?php echo admin_url( 'options-general.php?page=woosocio' ); ?>" target="_blank"><?php _e( 'Connect', 'woosocio' ); ?></a>
			<?php endif; ?>
			<div id="woosocio-form" style="display: none;">
            	<br />
                <input type="checkbox" name="like_facebook" id="like-facebook" <?php echo $like_chkbox_val; ?> />
                <label for="like-facebook"><b><?php _e( 'Show Like/Share buttons?', 'woosocio' ); ?></b></label><br />
                <input type="checkbox" name="chk_facebook" id="chk-facebook" <?php echo $chkbox_val; ?> />
                <label for="chk-facebook"><b><?php _e( 'Post to Facebook?', 'woosocio' ); ?></b></label><br />
				<label for="woosocio-custom-msg"><?php _e( 'Custom Message: (No html tags)', 'woosocio' ); ?></label>
				<textarea name="woosocio_custom_msg" id="woosocio-custom-msg"><?php echo $saved_msg; ?></textarea>
				<a href="#" id="woosocio-form-ok" class="button"><?php _e( 'Save', 'woosocio' ); ?></a>
				<a href="#" id="woosocio-form-hide"><?php _e( 'Cancel', 'woosocio' ); ?></a>
                <input type="hidden" name="postid" id="postid" value="<?php echo get_the_ID()?>" />
			</div>
             &nbsp; <a href="#" id="woosocio-form-edit"><?php _e( 'Edit', 'woosocio' ); ?></a>
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
				var custom_msg;
       			custom_msg = $("#woosocio-custom-msg").val();
				var data = {
					action: 'my_action',
					text1: custom_msg,
					postid: $("#postid").val(),
					chk_facebook: $("#chk-facebook").attr("checked"),
					like_facebook: $("#like-facebook").attr("checked")
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
	    if ( ! update_post_meta ($_POST['postid'], '_woosocio_like_facebook', 
								 $_POST['like_facebook'] ) ) 
			   add_post_meta(    $_POST['postid'], '_woosocio_like_facebook', 
			   				     $_POST['like_facebook'], true );

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
		$message = get_the_title($post_id);
		$this->check_connection();
		//$fb_post = get_post_meta( $post_id, '_woosocio_facebook', true );
		$fb_post = metadata_exists('post', $post_id, '_woosocio_facebook') ? get_post_meta( $post_id, '_woosocio_facebook', true ) : 'checked';
		$message.= metadata_exists('post', $post_id, '_woosocio_msg') ? " - ".get_post_meta( $post_id, '_woosocio_msg', true ) : '';
		$fb_page_value = get_option( $this->fb_user_profile['id'].'_fb_page_id', $this->fb_user_profile['id'] );
		
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
			//$message = get_post_meta( $post_id, '_woosocio_msg', true );
			$socio_link = get_permalink( $post_id );
	    	
			try {
				$ret_obj = $this -> facebook -> api('/'.$fb_page_value.'/feed', 'POST', array(  'link' 		=> $socio_link,
                                         														'message'	=> $message)
                                      		   );
				if ($ret_obj) {
					if ( ! update_post_meta ($post_id, '_woosocio_fb_posted', 'checked' ) ) 
						   add_post_meta(    $post_id, '_woosocio_fb_posted', 'checked', true );
				}
				if ( ! update_post_meta ($post_id, '_woosocio_facebook', 'checked' ) ) 
			   		   add_post_meta(    $post_id, '_woosocio_facebook', 'checked', true );
      		} 
			catch(FacebookApiException $e) {
        		//$login_url = $this->facebook->getLoginUrl( array('scope' => 'photo_upload')); 
        		//echo 'Please <a href="' . $login_url . '">login.</a>';
				//console.log($e->getType());
      		}   
		}
	}

	/**
	 * woosocio_admin_menu function.
	 *
	 * @access public
	 * @return void
	 */		
	public function woosocio_admin_menu () {
		add_menu_page( 'WooSocio', 'WooSocio', 'manage_options', 'woosocio', '', $this->assets_url.'/menu_icon_wc.png', 50 );
		$page_logins   = add_submenu_page( 'woosocio', 'WooSocio Options', 'WooSocio Logins', 'manage_options', 'woosocio', array( $this, 'socio_settings' ) );
		$page_products = add_submenu_page( 'woosocio', 'WooSocio Products', 'WooSocio Products', 'manage_options', 'products_list', array( $this, 'products_list' ) );
		/*$page = add_options_page( 'Socio Logins', 'WooSocio Options', 'manage_options', 'woosocio', array( $this, 'socio_settings' ) );*/
		add_action( 'admin_print_styles-' . $page_logins, array( $this, 'woosocio_admin_styles' ) );
		add_action( 'admin_print_styles-' . $page_products, array( $this, 'woosocio_admin_styles' ) );
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
	 * products_list function.
	 *
	 * @access public
	 * @return void
	 */		
	public function products_list () {
		
		?>
		<script type="text/javascript">
			url = '<?php echo add_query_arg( array('post_type' => 'product',
											   	   'list'	   => 'woosocio'), admin_url('edit.php')) ?>';
			window.location.replace(url);											   
		</script>
        <?php
		
    	/*wp_safe_redirect( add_query_arg( array('post_type' => 'product',
											   'list'	   => 'woosocio'), $url) );*/
		//wp_safe_redirect( 'www.yahoo.com' );

	}

	/**
	 * check connection function.
	 *
	 * @access public
	 */
	public function check_connection() {

 		try { 
			$this->fb_user_profile = $this->facebook->api('/me');
			$this->fb_user_pages = $this->facebook->api('/me/accounts');
		 	return $this->fb_user_profile;
		} catch (FacebookApiException $e) {
			return false;
		}
	}


	/**
	 * save facebook app id and secret function.
	 *
	 * @access public
	 */
	public function save_app_info() {
		update_option( 'fb_app_id', $_POST['fb_app_id'] );
		update_option( 'fb_app_secret', $_POST['fb_app_secret'] );
 	}

	/**
	 * update facebook page id function.
	 *
	 * @access public
	 */
	public function update_page_info() {
		$this->check_connection();
		$user_sign = $this->fb_user_profile['id'].'_fb_page_id';
		if(update_option( $user_sign, $_POST['fb_page_id'] ))
			_e( 'Page Info Updated!', 'woosocio');
		else
			_e( 'Unable to update page info! Please try again.', 'woosocio');
		die(0);
		//update_option( 'fb_app_secret', $_POST['fb_app_secret'] );
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