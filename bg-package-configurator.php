<?php
/*
    Plugin Name: Bas Grave - Package Configurator
    Description: A configurator for building a package from separate products.
    Version:     1.0
    Author:      Bas Grave
    Author URI:  https://basgrave.com
    Text Domain: bgpk
    Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}

if ( ! defined( 'PC_PLUGIN_DIR' ) ) {
    define( 'PC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'PC_PLUGIN_URL' ) ) {
    define( 'PC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

// MAIN PLUGIN CLASS
class Package_Configurator {

    public function __construct() {
        add_shortcode( 'package_configurator', array( $this, 'render_package_configurator' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
    }

    public function load_textdomain() {
        load_plugin_textdomain( 'bgpk', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    }

    public function enqueue_scripts() {
        wp_enqueue_script( 'jquery' );
        $version = PC_PLUGIN_DIR . 'assets/package-configurator.js';
        wp_enqueue_script( 'package-configurator-js', PC_PLUGIN_URL . 'assets/package-configurator.js', array( 'jquery' ), $version, true );
    }

    function package_configurator_get_template( $template_name, $args = [] ) {
        $template_path = PC_PLUGIN_DIR . 'templates/' . $template_name;
    
        if ( file_exists( $template_path ) ) {
            if ( ! empty( $args ) ) {
                extract( $args );
            }
            ob_start();
            include $template_path;
            return ob_get_clean();
        }
        return '';
    }

    public function render_package_configurator() {
        ob_start();
    
        echo '<div class="package-wrapper">';
        echo '<div class="package-product-list">';
        echo $this->package_configurator_get_template( 'products-list.php' );
        echo '</div>';
        echo '<div>';
        echo $this->package_configurator_get_template( 'overview.php' );
        echo '</div>';
        echo '</div>';
    
        return ob_get_clean();
    }
}

// Initialize plugin
new Package_Configurator();
