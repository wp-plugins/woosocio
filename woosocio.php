<?php
/**
 * Plugin Name: WooSocio
 * Plugin URI: http://genialsouls.com/
 * Description: This plugin will upload/post your Woo products to facebook wall as well as facebook pages automatically, when published. Also adds like/share buttons in Woo products.
 * Author: Qamar Sheeraz
 * Author URI: http://genialsouls.com/
 * Version: 0.7.2
 * Stable tag: 0.7.2
 * License: GPL v2 - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

 if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

 require_once( 'classes/class-woo-socio.php' );

 // FaceBook integrations.
 require_once( 'classes/facebook.php' );

 global $woosocio;
 $woosocio = new Woo_Socio( __FILE__ );
 $woosocio->version = '0.7.2';
 $woosocio->init();
?>