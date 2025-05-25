<?php
/**
 * Theme functions and definitions
 *
 * @package HelloElementorChild
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Change Add to Cart button text for WooCommerce
 */
add_filter('woocommerce_product_single_add_to_cart_text', 'ccr_custom_add_to_cart_text');
add_filter('woocommerce_product_add_to_cart_text', 'ccr_custom_add_to_cart_text');

function ccr_custom_add_to_cart_text() {
    return 'רכשו עכשיו';
}

/**
 * Redirect to checkout after adding a product to cart
 */
add_action('wp_footer', 'redirect_to_checkout_after_add_to_cart');
function redirect_to_checkout_after_add_to_cart() {
    if (!is_product()) return; // Only on single product pages
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        // Handle both AJAX and non-AJAX add to cart
        $(document).on('added_to_cart', function() {
            setTimeout(function() {
                window.location.href = '<?php echo esc_js(wc_get_checkout_url()); ?>';
            }, 500); // Small delay to ensure cart is updated
        });

        // Fallback for non-AJAX add to cart
        $('form.cart').on('submit', function(e) {
            var $form = $(this);
            var $button = $form.find('.single_add_to_cart_button');
            
            // Only if it's the purchase now button
            if ($button.text().trim() === 'רכשו עכשיו') {
                e.preventDefault();
                
                // Add the product to cart
                $.ajax({
                    type: 'POST',
                    url: wc_add_to_cart_params.ajax_url,
                    data: $form.serialize() + '&add-to-cart=' + $button.val(),
                    success: function(response) {
                        if (response.error && response.product_url) {
                            window.location = response.product_url;
                            return;
                        }
                        window.location.href = '<?php echo esc_js(wc_get_checkout_url()); ?>';
                    },
                    error: function() {
                        // If AJAX fails, let the form submit normally
                        $form.off('submit').submit();
                    }
                });
            }
        });
    });
    </script>
    <?php
}

/**
 * Enqueue scripts and styles
 */
function hello_elementor_child_scripts_styles() {
    // Parent theme styles
    wp_enqueue_style(
        'hello-elementor-child-style',
        get_stylesheet_directory_uri() . '/style.css',
        ['hello-elementor-theme-style'],
        filemtime(get_stylesheet_directory() . '/style.css')
    );
    
    // Enqueue custom scripts only if they exist
    if (file_exists(get_stylesheet_directory() . '/assets/js/custom.js')) {
        wp_enqueue_script(
            'hello-elementor-child-script',
            get_stylesheet_directory_uri() . '/assets/js/custom.js',
            ['jquery'],
            filemtime(get_stylesheet_directory() . '/assets/js/custom.js'),
            true
        );
    }
    
    // Enqueue quiz answer handler on quiz pages
    if (is_singular('sfwd-quiz')) {
        wp_enqueue_script(
            'quiz-answer-handler',
            get_stylesheet_directory_uri() . '/assets/js/quiz-answer-handler.js',
            ['jquery'],
            filemtime(get_stylesheet_directory() . '/assets/js/quiz-answer-handler.js'),
            true
        );
        
        // Enqueue quiz styles
        wp_enqueue_style(
            'quiz-styles',
            get_stylesheet_directory_uri() . '/assets/css/quiz-styles.css',
            [],
            filemtime(get_stylesheet_directory() . '/assets/css/quiz-styles.css')
        );
    }
}
add_action('wp_enqueue_scripts', 'hello_elementor_child_scripts_styles', 20);

// Load other theme files
require_once get_stylesheet_directory() . '/inc/shortcodes/loader.php';

// Load Ultimate Member integration if UM is active
function ccr_load_um_integration() {
    if (class_exists('UM')) {
        require_once get_stylesheet_directory() . '/includes/integrations/class-ultimate-member-integration.php';
    }
}
add_action('after_setup_theme', 'ccr_load_um_integration', 5);

// Load Messaging System
function ccr_load_messaging_system() {
    require_once get_stylesheet_directory() . '/includes/messaging/notifications.php';
    
    if (is_admin()) {
        require_once get_stylesheet_directory() . '/includes/messaging/admin-functions.php';
    }
}
add_action('after_setup_theme', 'ccr_load_messaging_system', 10);

// Load Login System
function ccr_load_login_system() {
    if (!is_admin()) {
        require_once get_stylesheet_directory() . '/src/Login/LoginManager.php';
        require_once get_stylesheet_directory() . '/src/Login/Captcha.php';
        require_once get_stylesheet_directory() . '/src/Login/UserAccountWidget.php';
    }
}
add_action('after_setup_theme', 'ccr_load_login_system', 10);

// Add body class for quiz types
add_filter('body_class', function($classes) {
    if (is_singular('sfwd-quiz')) {
        $quiz_id = get_the_ID();
        $enforce_hint = get_post_meta($quiz_id, 'enforce_hint', true);
        
        // Always add quiz type class
        if ($enforce_hint === 'yes') {
            $classes[] = 'quiz-type-forced-hint';
        } else {
            $classes[] = 'quiz-type-normal';
        }
        
        // Backward compatibility
        if ($enforce_hint === 'yes') {
            $classes[] = 'forced-hint-quiz';
        }
    }
    return $classes;
}, 5); // Lower priority to ensure it runs early

// Debug helper function
if (!function_exists('write_log')) {
    function write_log($log) {
        if (true === WP_DEBUG) {
            if (is_array($log) || is_object($log)) {
                error_log(print_r($log, true));
            } else {
                error_log($log);
            }
        }
    }
}


function remove_css_js_version_query( $src ) {
    if ( strpos( $src, '?ver=' ) !== false ) {
        $src = remove_query_arg( 'ver', $src );
    }
    return $src;
}
add_filter( 'style_loader_src', 'remove_css_js_version_query', 9999 );
add_filter( 'script_loader_src', 'remove_css_js_version_query', 9999 );
