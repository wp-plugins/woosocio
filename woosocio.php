<?php
/**
 * Plugin Name: WooSocio
 * Plugin URI: http://shafiamall.co.uk/woosocio/
 * Description: This plugin will upload/post your Woo products to facebook automatically, when published.
 * Author: Qamar Sheeraz
 * Author URI: http://shafiamall.co.uk/woosocio/
 * Version: 0.0.1
 * Stable tag: 0.0.1
 * License: GPL v2 - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

 if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

 require_once( 'classes/class-woo-socio.php' );

 // FaceBook integrations.
 require_once( 'classes/facebook.php' );

 global $woosocio;
 $woosocio = new Woo_Socio( __FILE__ );
 $woosocio->version = '0.0.1';
 $woosocio->init();
?>