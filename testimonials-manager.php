<?php
/**
 * Plugin Name: Testimonials Manager
 * Plugin URI: https://example.com/testimonials-manager
 * Description: A complete testimonials management system with custom post type, meta boxes, and frontend display with carousel.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: testimonials-manager
 * Domain Path: /languages
 */

// Prevent direct access to the file
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('TESTIMONIALS_VERSION', '1.0.0');
define('TESTIMONIALS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TESTIMONIALS_PLUGIN_URL', plugin_dir_url(__FILE__));

// Register activation and deactivation hooks
register_activation_hook(__FILE__, 'testimonials_manager_activate');
register_deactivation_hook(__FILE__, 'testimonials_manager_deactivate');

/**
 * Plugin activation function
 */
function testimonials_manager_activate() {
    // Register post type first
    $plugin = new Testimonials_Manager();
    $plugin->register_testimonials_post_type();
    
    // Flush rewrite rules
    flush_rewrite_rules();
}

/**
 * Plugin deactivation function
 */
function testimonials_manager_deactivate() {
    // Flush rewrite rules
    flush_rewrite_rules();
}

/**
 * Main Plugin Class
 */
class Testimonials_Manager {

    /**
     * Constructor
     */
    public function __construct() {
        // Hook to initialize the plugin
        add_action('init', array($this, 'register_testimonials_post_type'));
        add_action('add_meta_boxes', array($this, 'add_testimonial_meta_box'));
        add_action('save_post', array($this, 'save_testimonial_meta'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
        add_shortcode('testimonials', array($this, 'render_testimonials_shortcode'));
        
        // Admin scripts
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }

    /**
     * Register Custom Post Type: Testimonials
     */
    public function register_testimonials_post_type() {
        $labels = array(
            'name'               => esc_html__('Testimonials', 'testimonials-manager'),
            'singular_name'      => esc_html__('Testimonial', 'testimonials-manager'),
            'menu_name'          => esc_html__('Testimonials', 'testimonials-manager'),
            'name_admin_bar'     => esc_html__('Testimonial', 'testimonials-manager'),
            'add_new'            => esc_html__('Add New', 'testimonials-manager'),
            'add_new_item'       => esc_html__('Add New Testimonial', 'testimonials-manager'),
            'edit_item'          => esc_html__('Edit Testimonial', 'testimonials-manager'),
            'new_item'           => esc_html__('New Testimonial', 'testimonials-manager'),
            'view_item'          => esc_html__('View Testimonial', 'testimonials-manager'),
            'search_items'       => esc_html__('Search Testimonials', 'testimonials-manager'),
            'not_found'          => esc_html__('No testimonials found', 'testimonials-manager'),
            'not_found_in_trash' => esc_html__('No testimonials found in trash', 'testimonials-manager'),
        );

        $args = array(
            'label'               => esc_html__('Testimonials', 'testimonials-manager'),
            'description'         => esc_html__('Manage client testimonials', 'testimonials-manager'),
            'labels'              => $labels,
            'supports'            => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
            'taxonomies'          => array(),
            'public'              => true,
            'show_in_rest'        => true, // Gutenberg support
            'has_archive'         => true,
            'show_in_menu'        => true,
            'menu_position'       => 30,
            'menu_icon'           => 'dashicons-testimonial',
            'rewrite'             => array('slug' => 'testimonials'),
            'capability_type'     => 'post',
            'show_in_admin_bar'   => true,
            'exclude_from_search' => false,
        );

        register_post_type('testimonial', $args);
    }

    /**
     * Add Meta Box for Testimonial Details
     */
    public function add_testimonial_meta_box() {
        add_meta_box(
            'testimonial_details',
            esc_html__('Testimonial Details', 'testimonials-manager'),
            array($this, 'render_testimonial_meta_box'),
            'testimonial',
            'normal',
            'high'
        );
    }

    /**
     * Render Meta Box Fields
     */
    public function render_testimonial_meta_box($post) {
        // Add nonce for security
        wp_nonce_field('testimonial_meta_nonce', 'testimonial_meta_nonce');

        // Get existing values
        $client_name = get_post_meta($post->ID, '_testimonial_client_name', true);
        $client_position = get_post_meta($post->ID, '_testimonial_client_position', true);
        $company_name = get_post_meta($post->ID, '_testimonial_company_name', true);
        $rating = get_post_meta($post->ID, '_testimonial_rating', true);
        
        // Default rating to 5 if not set
        if (empty($rating)) {
            $rating = 5;
        }
        ?>
        <div class="testimonial-meta-box">
            <p>
                <label for="testimonial_client_name">
                    <strong><?php esc_html_e('Client Name', 'testimonials-manager'); ?></strong>
                </label>
                <input type="text" 
                       id="testimonial_client_name" 
                       name="testimonial_client_name" 
                       value="<?php echo esc_attr($client_name); ?>" 
                       class="widefat" 
                       required />
                <span class="description"><?php esc_html_e('Required field', 'testimonials-manager'); ?></span>
            </p>
            
            <p>
                <label for="testimonial_client_position">
                    <strong><?php esc_html_e('Client Position/Title', 'testimonials-manager'); ?></strong>
                </label>
                <input type="text" 
                       id="testimonial_client_position" 
                       name="testimonial_client_position" 
                       value="<?php echo esc_attr($client_position); ?>" 
                       class="widefat" />
            </p>
            
            <p>
                <label for="testimonial_company_name">
                    <strong><?php esc_html_e('Company Name', 'testimonials-manager'); ?></strong>
                </label>
                <input type="text" 
                       id="testimonial_company_name" 
                       name="testimonial_company_name" 
                       value="<?php echo esc_attr($company_name); ?>" 
                       class="widefat" />
            </p>
            
            <p>
                <label for="testimonial_rating">
                    <strong><?php esc_html_e('Rating', 'testimonials-manager'); ?></strong>
                </label>
                <select id="testimonial_rating" name="testimonial_rating" class="widefat">
                    <?php for ($i = 1; $i <= 5; $i++) : ?>
                        <option value="<?php echo esc_attr($i); ?>" <?php selected($rating, $i); ?>>
                            <?php echo esc_html($i); ?> <?php esc_html_e('Star' . ($i > 1 ? 's' : ''), 'testimonials-manager'); ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </p>
        </div>
        
        <style>
            .testimonial-meta-box p {
                margin-bottom: 15px;
            }
            .testimonial-meta-box label {
                display: block;
                margin-bottom: 5px;
            }
            .testimonial-meta-box .description {
                font-size: 12px;
                color: #666;
            }
        </style>
        <?php
    }

    /**
     * Save Meta Box Data
     */
    public function save_testimonial_meta($post_id) {
        // Check if nonce is set
        if (!isset($_POST['testimonial_meta_nonce'])) {
            return $post_id;
        }

        // Verify nonce
        if (!wp_verify_nonce($_POST['testimonial_meta_nonce'], 'testimonial_meta_nonce')) {
            return $post_id;
        }

        // Check if autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $post_id;
        }

        // Check user permission
        if (!current_user_can('edit_post', $post_id)) {
            return $post_id;
        }

        // Sanitize and save Client Name (required)
        if (isset($_POST['testimonial_client_name'])) {
            $client_name = sanitize_text_field(wp_unslash($_POST['testimonial_client_name']));
            update_post_meta($post_id, '_testimonial_client_name', $client_name);
        }

        // Sanitize and save Client Position
        if (isset($_POST['testimonial_client_position'])) {
            $client_position = sanitize_text_field(wp_unslash($_POST['testimonial_client_position']));
            update_post_meta($post_id, '_testimonial_client_position', $client_position);
        }

        // Sanitize and save Company Name
        if (isset($_POST['testimonial_company_name'])) {
            $company_name = sanitize_text_field(wp_unslash($_POST['testimonial_company_name']));
            update_post_meta($post_id, '_testimonial_company_name', $company_name);
        }

        // Save Rating
        if (isset($_POST['testimonial_rating'])) {
            $rating = intval($_POST['testimonial_rating']);
            if ($rating >= 1 && $rating <= 5) {
                update_post_meta($post_id, '_testimonial_rating', $rating);
            }
        }
    }

    /**
     * Enqueue Admin Scripts
     */
    public function enqueue_admin_scripts($hook) {
        global $post_type;
        
        if ('testimonial' === $post_type && in_array($hook, array('post.php', 'post-new.php'))) {
            wp_enqueue_style('testimonials-admin', plugins_url('css/admin.css', __FILE__), array(), '1.0.0');
        }
    }

    /**
     * Enqueue Frontend Scripts and Styles
     */
    public function enqueue_frontend_scripts() {
        wp_enqueue_style('testimonials-frontend', plugins_url('css/frontend.css', __FILE__), array(), '1.0.0');
        wp_enqueue_script('testimonials-slider', plugins_url('js/slider.js', __FILE__), array('jquery'), '1.0.0', true);
        
        // Pass variables to JavaScript
        wp_localize_script('testimonials-slider', 'testimonialsVars', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('testimonials_nonce'),
        ));
    }

    /**
     * Render Testimonials Shortcode
     * 
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    public function render_testimonials_shortcode($atts) {
        // Set default attributes
        $atts = shortcode_atts(array(
            'count'   => -1,     // -1 means all testimonials
            'orderby' => 'date', // date, title, menu_order, rand
            'order'   => 'DESC', // ASC or DESC
        ), $atts, 'testimonials');

        // Query arguments
        $args = array(
            'post_type'      => 'testimonial',
            'posts_per_page' => intval($atts['count']),
            'orderby'        => sanitize_text_field($atts['orderby']),
            'order'          => sanitize_text_field($atts['order']),
            'post_status'    => 'publish',
        );

        $testimonials_query = new WP_Query($args);

        // Start output buffering
        ob_start();

        if ($testimonials_query->have_posts()) {
            ?>
            <div class="testimonials-container" id="testimonials-slider">
                <div class="testimonials-wrapper">
                    <?php 
                    $testimonial_count = 0;
                    while ($testimonials_query->have_posts()) : $testimonials_query->the_post();
                        $post_id = get_the_ID();
                        $client_name = get_post_meta($post_id, '_testimonial_client_name', true);
                        $client_position = get_post_meta($post_id, '_testimonial_client_position', true);
                        $company_name = get_post_meta($post_id, '_testimonial_company_name', true);
                        $rating = get_post_meta($post_id, '_testimonial_rating', true);
                        $featured_image = get_the_post_thumbnail($post_id, 'testimonial-thumb', array('class' => 'testimonial-photo'));
                        
                        // Default values
                        if (empty($rating)) $rating = 5;
                        if (empty($client_name)) $client_name = get_the_title();
                        
                        // Get testimonial content
                        $content = get_the_content();
                        $content = apply_filters('the_content', $content);
                        $content = str_replace(']]>', ']]>', $content);
                        ?>
                        <div class="testimonial-slide" data-index="<?php echo esc_attr($testimonial_count); ?>">
                            <div class="testimonial-content-wrapper">
                                <?php if ($featured_image) : ?>
                                    <div class="testimonial-photo-wrapper">
                                        <?php echo $featured_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                    </div>
                                <?php else : ?>
                                    <div class="testimonial-photo-wrapper testimonial-photo-placeholder">
                                        <span class="dashicons dashicons-businessman"></span>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="testimonial-text">
                                    <div class="testimonial-stars">
                                        <?php 
                                        for ($i = 1; $i <= 5; $i++) {
                                            if ($i <= $rating) {
                                                echo '<span class="star filled">&#9733;</span>';
                                            } else {
                                                echo '<span class="star">&#9734;</span>';
                                            }
                                        }
                                        ?>
                                    </div>
                                    
                                    <blockquote class="testimonial-quote">
                                        <?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                    </blockquote>
                                    
                                    <div class="testimonial-author-info">
                                        <h4 class="testimonial-client-name"><?php echo esc_html($client_name); ?></h4>
                                        <?php if (!empty($client_position)) : ?>
                                            <span class="testimonial-client-position"><?php echo esc_html($client_position); ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($company_name)) : ?>
                                            <span class="testimonial-company-name"><?php echo esc_html($company_name); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php 
                        $testimonial_count++;
                    endwhile; 
                    ?>
                </div>
                
                <?php if ($testimonial_count > 1) : ?>
                <div class="testimonials-navigation">
                    <button class="testimonial-prev" aria-label="<?php esc_attr_e('Previous testimonial', 'testimonials-manager'); ?>">
                        <span class="dashicons dashicons-arrow-left-alt2"></span>
                    </button>
                    <div class="testimonial-dots">
                        <?php for ($i = 0; $i < $testimonial_count; $i++) : ?>
                            <span class="testimonial-dot <?php echo $i === 0 ? 'active' : ''; ?>" data-index="<?php echo esc_attr($i); ?>"></span>
                        <?php endfor; ?>
                    </div>
                    <button class="testimonial-next" aria-label="<?php esc_attr_e('Next testimonial', 'testimonials-manager'); ?>">
                        <span class="dashicons dashicons-arrow-right-alt2"></span>
                    </button>
                </div>
                <?php endif; ?>
            </div>
            <?php
            wp_reset_postdata();
        } else {
            echo '<p class="testimonials-not-found">' . esc_html__('No testimonials found.', 'testimonials-manager') . '</p>';
        }

        return ob_get_clean();
    }
}

// Initialize the plugin
new Testimonials_Manager();
