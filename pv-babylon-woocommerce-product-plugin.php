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
    wp_enqueue_script('babylon-viewer', esc_url_raw('https://cdn.babylonjs.com/viewer/babylon.viewer.js'), [], null, true);

//    if ( is_admin() ) {
//        wp_enqueue_script('babylon-viewer', esc_url_raw('https://cdn.babylonjs.com/viewer/babylon.viewer.js'), [], null, true);
//    }else if (strpos(get_the_content(), '[babylon]') !== false || strpos(get_the_content(), '</babylon>') !== false) {
//        wp_enqueue_script('babylon-viewer', esc_url_raw('https://cdn.babylonjs.com/viewer/babylon.viewer.js'), [], null, true);
//    }
}
add_action( 'wp_enqueue_scripts', 'pv_babylonviewer_call' );
add_action( 'admin_enqueue_scripts', 'pv_babylonviewer_call' );

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
//add_shortcode('pvbabylon', 'pv_babylon_product_view');
function char_example_babs($atts=[],$content = null){
    $url = esc_url_raw($content);

    $content = '<babylon ';
    $content .=	'model="';
    $content .= $url;
    $content .= '"></babylon>';

$example =<<<HTML
<babylon extends="minimal">
    <!-- Ground that receives shadows -->
    <ground receive-shadows="true"></ground>
    <!-- Default skybox
   <skybox></skybox>
   -->
    <model url="$url">
    </model>
    <!-- enable antialiasing -->
    <engine antialiasing="true"></engine>
    <!-- camera configuration -->
    <camera>
        <!-- add camera behaviors -->
        <behaviors>
            <!-- enable default auto-rotate behavior -->
            <auto-rotate type="0"></auto-rotate>
            <!-- enable and configure the framing behavior -->
            <framing type="2" zoom-on-bounding-info="true" zoom-stops-animation="false"></framing>
            <!-- enable default bouncing behavior -->
            <bouncing type="1"></bouncing>
        </behaviors>
        <position x="250" y="80" z="-280"></position>
    </camera>
    <scene>
        <clear-color r="1" g="1" b="1"></clear-color>
    </scene>
</babylon>
HTML;


    return $example;
}
add_shortcode('pvchairtest','char_example_babs');

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
                <!--<babylon model="https://ccpatiodev.primeview.com/wp-content/uploads/2024/06/3d-prefinal-transparent-p-1.13-1-2.glb"></babylon>-->
                <babylon extends="minimal">
                    <!-- Ground that receives shadows -->
                    <ground receive-shadows="true"></ground>
                    <!-- Default skybox
                   <skybox></skybox>
                   -->
                    <model url="https://ccpatiodev.primeview.com/wp-content/uploads/2024/06/3SM.obj">
                    </model>
                    <!-- enable antialiasing -->
                    <engine antialiasing="true"></engine>
                    <!-- camera configuration -->
                    <camera>
                        <!-- add camera behaviors -->
                        <behaviors>
                            <!-- enable default auto-rotate behavior -->
                            <auto-rotate type="0"></auto-rotate>
                            <!-- enable and configure the framing behavior -->
                            <framing type="2" zoom-on-bounding-info="true" zoom-stops-animation="false"></framing>
                            <!-- enable default bouncing behavior -->
                            <bouncing type="1"></bouncing>
                        </behaviors>
                        <position x="250" y="80" z="-280"></position>
                    </camera>
                    <scene>
                        <clear-color r="1" g="1" b="1"></clear-color>
                    </scene>
                </babylon>
            </div>
        </div>
    </div>
    <?php
}
// Displaying the value on single product pages
function pv_babylon_product_view($product_id) {

    //if($product_id == '9016'){
    //$new_meta2 = get_post_meta(get_the_ID(),'_new_meta', true);
    $viewer =<<<HTML
                <babylon style="max-height:350px;" extends="minimal">
                    <!-- Ground that receives shadows -->
                    <ground receive-shadows="true"></ground>
                    <!-- Default skybox
                   <skybox></skybox>
                   -->
                    <model url="https://ccpatiodev.primeview.com/wp-content/uploads/2024/06/3SM.obj">
                    </model>
                    <!-- enable antialiasing -->
                    <engine antialiasing="true"></engine>
                    <!-- camera configuration -->
                    <camera>
                        <!-- add camera behaviors -->
                        <behaviors>
                            <!-- enable default auto-rotate behavior -->
                            <auto-rotate type="0"></auto-rotate>
                            <!-- enable and configure the framing behavior -->
                            <framing type="2" zoom-on-bounding-info="true" zoom-stops-animation="false"></framing>
                            <!-- enable default bouncing behavior -->
                            <bouncing type="1"></bouncing>
                        </behaviors>
                        <position x="250" y="80" z="-280"></position>
                    </camera>
                    <scene>
                        <clear-color r="1" g="1" b="1"></clear-color>
                    </scene>
                </babylon>
HTML;
    echo $viewer;
}
add_action('woocommerce_single_product_summary', 'pv_babylon_product_view');
//add_action('brizy_template_content', 'pv_babylon_product_view');
