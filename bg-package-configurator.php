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

if (! defined('ABSPATH')) {
    exit;
}

if (! defined('PC_PLUGIN_DIR')) {
    define('PC_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

if (! defined('PC_PLUGIN_URL')) {
    define('PC_PLUGIN_URL', plugin_dir_url(__FILE__));
}

// MAIN PLUGIN CLASS
class Package_Configurator
{

    public function __construct()
    {        
        
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));

        add_shortcode('package_configurator', array($this, 'render_package_configurator'));

        // add_filter('get_terms', array($this, 'filter_out_configurator_product_cat'), 10, 3);

        add_filter('wpseo_robots', array($this, 'set_robots_for_configurator_products'), 999);
        
        add_filter('wpseo_exclude_from_sitemap_by_post_ids', array($this, 'exclude_configurator_products_from_sitemap'));
        
        add_action('save_post_product', array( $this, 'bgpk_set_visibility_for_configurator_products'), 20, 3);

    }

    function bgpk_set_visibility_for_configurator_products($post_id, $post, $update) {
    
        if (wp_is_post_revision($post_id) || defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
    
        if ('product' !== $post->post_type) {
            return;
        }
    
        if (has_term('configurator', 'product_cat', $post_id)) {
    
            wp_set_object_terms($post_id, array('exclude-from-catalog', 'exclude-from-search'), 'product_visibility');
    
        } 

    }
    
    public function filter_out_configurator_product_cat($terms, $taxonomies, $args)
    {
        $new_terms = array();

        if (in_array('product_cat', $taxonomies) && ! is_admin() ) {

            foreach ($terms as $key => $term) {

                if (is_object($term) && ! in_array($term->slug, array('configurator'))) {
                    $new_terms[] = $term;
                }

            }

            $terms = $new_terms;

        }

        return $terms;
    }  

    public function exclude_configurator_products_from_sitemap($excluded_posts)
    {
        $args = array(
            'post_type'      => 'product',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'tax_query'      => array(
                array(
                    'taxonomy' => 'product_cat',
                    'field'    => 'slug',
                    'terms'    => array('configurator'),
                ),
            ),
        );

        $products = get_posts($args);

        if (! empty($products)) {
            $excluded_posts = array_merge($excluded_posts, $products);
        }

        return $excluded_posts;
    }

    public function set_robots_for_configurator_products($string)
    {

        if (is_singular('product')) {
            $product_id = get_queried_object_id();

            if (has_term('configurator', 'product_cat', $product_id)) {
                return 'noindex, nofollow';
            }
        } else {
            return $string;
        }
    }

    public function load_textdomain()
    {
        load_plugin_textdomain('bgpk', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    public function enqueue_scripts()
    {
        wp_enqueue_script('jquery');
        $version = PC_PLUGIN_DIR . 'assets/package-configurator.js';
        wp_enqueue_script('package-configurator-js', PC_PLUGIN_URL . 'assets/package-configurator.js', array('jquery'), $version, true);
    }

    function package_configurator_get_template($template_name, $args = [])
    {
        $template_path = PC_PLUGIN_DIR . 'templates/' . $template_name;

        if (file_exists($template_path)) {
            if (! empty($args)) {
                extract($args);
            }
            ob_start();
            include $template_path;
            return ob_get_clean();
        }
        return '';
    }

    public function render_package_configurator()
    {
        ob_start();

        echo '<div class="package-wrapper">';
        echo '<div class="package-product-list">';
        echo $this->package_configurator_get_template('products-list.php');
        echo '</div>';
        echo '<div>';
        echo $this->package_configurator_get_template('overview.php');
        echo '</div>';
        echo '</div>';

        return ob_get_clean();
    }
}

new Package_Configurator();
