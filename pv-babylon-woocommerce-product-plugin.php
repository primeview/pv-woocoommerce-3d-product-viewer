<?php
/**
 * Plugin Name: Primeview Woocommerce Product Babylon Viewer
 * Plugin URI: https://www.primeview.com
 * Description: Display 3D models on Woocommerce Product Details page
 * Version: 0.31
 * Author: Yardi Fox
 * Author URI: https://www.primeview.com
 * Text Domain: pv-woo-babylon
 * GitHub Plugin URI: https://github.com/primeview/pv-babylon-woocoommerce-product-plugin
 */

defined('ABSPATH') or die('0_0');

function pv_babylonviewer_upload_mime_types( $mimes ) {

// Add new allowed MIME types here.
    $mimes['gltf'] = 'model/gltf+json';
    $mimes['glb'] = 'model/gltf-binary';
    $mimes['obj'] = 'model/obj';
    $mimes['mtl'] = 'model/mtl';
    $mimes['stl'] = 'model/stl';
    $mimes['babylon'] = 'model/babylon+json';
// Return the array back to the function with our added MIME type(s).
    return $mimes;
}
add_filter( 'upload_mimes', 'pv_babylonviewer_upload_mime_types' );

// Add allowed filetypes.
function pv_babylonviewer_correct_filetypes( $data, $file, $filename, $mimes, $real_mime ) {

    if ( ! empty( $data['ext'] ) && ! empty( $data['type'] ) ) {
        return $data;
    }

    $wp_file_type = wp_check_filetype( $filename, $mimes );

// Check for the file type you want to enable, e.g. 'gltf'.
    if ( 'gltf' === $wp_file_type['ext'] ) {
        $data['ext']  = 'gltf';
        $data['type'] = 'model/gltf+json';
    }
    if ( 'glb' === $wp_file_type['ext'] ) {
        $data['ext']  = 'glb';
        $data['type'] = 'model/glb-binary';
    }
    if ( 'babylon' === $wp_file_type['ext'] ) {
        $data['ext']  = 'babylon';
        $data['type'] = 'model/babylon+json';
    }
    if ( 'obj' === $wp_file_type['ext'] ) {
        $data['ext']  = 'obj';
        $data['type'] = 'model/obj';
    }
    if ( 'mtl' === $wp_file_type['ext'] ) {
        $data['ext']  = 'mtl';
        $data['type'] = 'model/mtl';
    }
    if ( 'stl' === $wp_file_type['ext'] ) {
        $data['ext']  = 'stl';
        $data['type'] = 'model/stl';
    }

    return $data;
}
add_filter( 'wp_check_filetype_and_ext', 'pv_babylonviewer_correct_filetypes' , 10, 5 );


// Adding Babylon Viewer into header
function pv_babylonviewer_call()
{
    /*
    if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'babylon') ) {
    wp_enqueue_script( 'babylon-viewer', esc_url_raw( 'https://cdn.babylonjs.com/viewer/babylon.viewer.js' ), array(), null, true );
    }
    */
// write inside the loop


    if (strpos(get_the_content(), '[babylon]') !== false || strpos(get_the_content(), '</babylon>') !== false) {
        wp_enqueue_script('babylon-viewer', esc_url_raw('https://cdn.babylonjs.com/viewer/babylon.viewer.js'), array(), null, true);
    }
}
add_action( 'wp_enqueue_scripts', 'pv_babylonviewer_call' );

// Adding Babylon Viewer shortcode
function pv_babylonviewer_shortcode($atts = [], $content = null) {
    $url = esc_url_raw($content);
    $content = '<babylon ';
    $content .=	'model="';
    $content .= $url;
    $content .= '"></babylon>';

    return $content;
}
add_shortcode('pvbabylon', 'pv_babylonviewer_shortcode');

// Create metabox
function pv_babylon_product_metabox(){
    add_meta_box(
        'pv_babylon_product_model_url',
        'Babylon 3D Viewer Model',
        'pv_babylon_product_metabox_callback',
        'product',
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'pv_babylon_product_metabox');

function pv_babylon_product_metabox_callback($post){
    wp_nonce_field(basename(__FILE__), 'pv_babylon_product_nonce');
    $pv_babylon_product_stored_meta = get_post_meta($post->ID);
    ?>
    <div>
        <div class="meta-row">
            <div class="meta-th">
                <label for="pv-babylon-product-url" class="pv-babylon-product-url"><?php _e('Babylon 3D Model URL', 'pv-babylon-product-url'); ?></label>
            </div>
            <div class="meta-td">
                <input type="text" name="pv-babylon-product-url" id="pv-babylon-product-url" value="<?php if(!empty($pv_babylon_product_stored_meta['pv-babylon-product-url'])) echo esc_attr($pv_babylon_product_stored_meta['pv-babylon-product-url'][0]); ?>" />
            </div>
        </div>
    </div>
    <?php
}