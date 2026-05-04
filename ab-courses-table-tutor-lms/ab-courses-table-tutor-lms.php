<?php
/**
 * Plugin Name: AB Courses Table for Tutor LMS
 * Plugin URI:  https://github.com/abdulrahman1285/ab-courses-table-tutor-lms
 * Description: Displays Tutor LMS courses in a filterable Arabic table with category tabs, online/offline toggle, search, and pagination.
 * Version:     5.1.0
 * Author:      Abdulrahman Barakat
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: lms-courses-table
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'ABCT_VERSION', '5.1.0' );
define( 'ABCT_PATH', plugin_dir_path( __FILE__ ) );
define( 'ABCT_URL',  plugin_dir_url( __FILE__ ) );


// ──────────────────────────────────────────────
// Activation / Deactivation / Uninstall
// ──────────────────────────────────────────────
register_activation_hook( __FILE__, function () {
    // Set default options on first activation
    if ( ! get_option( 'abct_settings' ) ) {
        add_option( 'abct_settings', [
            'per_page'         => 8,
            'show_search'      => '1',
            'show_mode_toggle' => '1',
            'show_categories'  => '1',
            'primary_color'    => '#1A5276',
            'accent_color'     => '#B7950B',
            'sort_by'          => 'date',
            'sort_order'       => 'DESC',
            'enable_cache'     => '0',
            'cache_hours'      => 12,
        ] );
    }
} );

register_deactivation_hook( __FILE__, function () {
    // Clear all transient cache on deactivation
    global $wpdb;
    $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_abct_courses_%' OR option_name LIKE '_transient_timeout_abct_courses_%'" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
} );

// ──────────────────────────────────────────────
// Helper: get option with default
// ──────────────────────────────────────────────
function abct_opt( $key, $default = '' ) {
    $opts = get_option( 'abct_settings', [] );
    return isset( $opts[ $key ] ) && $opts[ $key ] !== '' ? $opts[ $key ] : $default;
}

// ──────────────────────────────────────────────
// Admin Menu
// ──────────────────────────────────────────────
add_action( 'admin_menu', function () {
    add_options_page(
        __( 'LMS Courses Table Settings', 'lms-courses-table' ),
        __( 'Courses Table', 'lms-courses-table' ),
        'manage_options',
        'abct-settings',
        'abct_render_settings_page'
    );
} );

// ──────────────────────────────────────────────
// Register Settings
// ──────────────────────────────────────────────
add_action( 'admin_init', function () {
    register_setting( 'abct_settings_group', 'abct_settings', [
        'sanitize_callback' => 'abct_sanitize_settings',
    ] );
} );

function abct_sanitize_settings( $input ) {
    $clean = [];
    $clean['per_page']           = absint( $input['per_page']  ?? 8 );
    $clean['show_search']        = isset( $input['show_search'] )        ? '1' : '0';
    $clean['show_mode_toggle']   = isset( $input['show_mode_toggle'] )   ? '1' : '0';
    $clean['show_categories']    = isset( $input['show_categories'] )    ? '1' : '0';
    $clean['primary_color']      = sanitize_hex_color( $input['primary_color']  ?? '#1A5276' );
    $clean['accent_color']       = sanitize_hex_color( $input['accent_color']   ?? '#B7950B' );
    $clean['registration_page']  = esc_url_raw( $input['registration_page']     ?? '' );
    $clean['show_seats']         = isset( $input['show_seats'] )         ? '1' : '0';
    $clean['show_price']         = isset( $input['show_price'] )         ? '1' : '0';
    $clean['show_students']      = isset( $input['show_students'] )      ? '1' : '0';
    $clean['show_level']         = isset( $input['show_level'] )         ? '1' : '0';
    $clean['show_duration']      = isset( $input['show_duration'] )      ? '1' : '0';
    $clean['enable_cache']       = isset( $input['enable_cache'] )       ? '1' : '0';
    $clean['cache_hours']        = absint( $input['cache_hours']         ?? 12 );
    $clean['sort_by']            = sanitize_text_field( $input['sort_by']        ?? 'date' );
    $clean['sort_order']         = sanitize_text_field( $input['sort_order']     ?? 'DESC' );
    $clean['no_seats_label']     = sanitize_text_field( $input['no_seats_label'] ?? '' );
    $clean['category_taxonomy']  = sanitize_text_field( $input['category_taxonomy']  ?? 'course-category' );
    $clean['delivery_meta']      = sanitize_text_field( $input['delivery_meta']      ?? '_tutor_course_delivery' );
    $clean['start_date_meta']    = sanitize_text_field( $input['start_date_meta']    ?? '_tutor_course_start_date' );
    $clean['start_time_meta']    = sanitize_text_field( $input['start_time_meta']    ?? '_tutor_course_start_time' );
    $clean['end_time_meta']      = sanitize_text_field( $input['end_time_meta']      ?? '_tutor_course_end_time' );
    $clean['seats_meta']         = sanitize_text_field( $input['seats_meta']         ?? '_tutor_course_max_students' );
    $clean['price_meta']         = sanitize_text_field( $input['price_meta']         ?? '_tutor_course_price' );
    $clean['students_meta']      = sanitize_text_field( $input['students_meta']      ?? '_tutor_course_enrolled_users_count' );
    $clean['level_meta']         = sanitize_text_field( $input['level_meta']         ?? '_tutor_course_level' );
    $clean['duration_meta']      = sanitize_text_field( $input['duration_meta']      ?? '_tutor_course_duration' );
    return $clean;
}

// ──────────────────────────────────────────────
// Settings Page
// ──────────────────────────────────────────────
function abct_render_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;
    $updated = isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] === 'true'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    ?>
    <div class="wrap" dir="rtl">
        <h1 style="margin-bottom:20px;">⚙️ <?php esc_html_e( 'LMS Courses Table Settings', 'lms-courses-table' ); ?></h1>

        <?php if ( $updated ) : ?>
            <div class="notice notice-success is-dismissible"><p>✅ <?php esc_html_e( 'Settings saved successfully!', 'lms-courses-table' ); ?></p></div>
        <?php endif; ?>

        <form method="post" action="options.php">
            <?php settings_fields( 'abct_settings_group' ); ?>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;max-width:980px;">

                <!-- Display Settings -->
                <div style="background:#fff;border:1px solid #ddd;border-radius:8px;padding:20px;">
                    <h2 style="margin-top:0;font-size:15px;border-bottom:1px solid #eee;padding-bottom:8px;">
                        🎨 <?php esc_html_e( 'Display Settings', 'lms-courses-table' ); ?>
                    </h2>
                    <table class="form-table" style="margin:0;">
                        <tr>
                            <th style="width:170px;"><?php esc_html_e( 'Rows per page', 'lms-courses-table' ); ?></th>
                            <td><input type="number" name="abct_settings[per_page]" value="<?php echo esc_attr( abct_opt('per_page',8) ); ?>" min="1" max="100" class="small-text" /></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Sort by', 'lms-courses-table' ); ?></th>
                            <td>
                                <select name="abct_settings[sort_by]">
                                    <option value="date"       <?php selected( abct_opt('sort_by','date'), 'date' ); ?>><?php esc_html_e( 'Publish date', 'lms-courses-table' ); ?></option>
                                    <option value="title"      <?php selected( abct_opt('sort_by','date'), 'title' ); ?>><?php esc_html_e( 'Name', 'lms-courses-table' ); ?></option>
                                    <option value="meta_value" <?php selected( abct_opt('sort_by','date'), 'meta_value' ); ?>><?php esc_html_e( 'Start date', 'lms-courses-table' ); ?></option>
                                </select>
                                <select name="abct_settings[sort_order]">
                                    <option value="DESC" <?php selected( abct_opt('sort_order','DESC'), 'DESC' ); ?>><?php esc_html_e( 'Descending', 'lms-courses-table' ); ?></option>
                                    <option value="ASC"  <?php selected( abct_opt('sort_order','DESC'), 'ASC' ); ?>><?php esc_html_e( 'Ascending', 'lms-courses-table' ); ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Show search', 'lms-courses-table' ); ?></th>
                            <td><label><input type="checkbox" name="abct_settings[show_search]" value="1" <?php checked( abct_opt('show_search','1'), '1' ); ?> /> <?php esc_html_e( 'Yes', 'lms-courses-table' ); ?></label></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Show online / offline toggle', 'lms-courses-table' ); ?></th>
                            <td><label><input type="checkbox" name="abct_settings[show_mode_toggle]" value="1" <?php checked( abct_opt('show_mode_toggle','1'), '1' ); ?> /> <?php esc_html_e( 'Yes', 'lms-courses-table' ); ?></label></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Show category tabs', 'lms-courses-table' ); ?></th>
                            <td><label><input type="checkbox" name="abct_settings[show_categories]" value="1" <?php checked( abct_opt('show_categories','1'), '1' ); ?> /> <?php esc_html_e( 'Yes', 'lms-courses-table' ); ?></label></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Show available seats', 'lms-courses-table' ); ?></th>
                            <td><label><input type="checkbox" name="abct_settings[show_seats]" value="1" <?php checked( abct_opt('show_seats','0'), '1' ); ?> /> <?php esc_html_e( 'Yes', 'lms-courses-table' ); ?></label></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Show price', 'lms-courses-table' ); ?></th>
                            <td><label><input type="checkbox" name="abct_settings[show_price]" value="1" <?php checked( abct_opt('show_price','0'), '1' ); ?> /> <?php esc_html_e( 'Yes', 'lms-courses-table' ); ?></label></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Show enrolled students', 'lms-courses-table' ); ?></th>
                            <td><label><input type="checkbox" name="abct_settings[show_students]" value="1" <?php checked( abct_opt('show_students','0'), '1' ); ?> /> <?php esc_html_e( 'Yes', 'lms-courses-table' ); ?></label></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Show course level', 'lms-courses-table' ); ?></th>
                            <td><label><input type="checkbox" name="abct_settings[show_level]" value="1" <?php checked( abct_opt('show_level','0'), '1' ); ?> /> <?php esc_html_e( 'Yes', 'lms-courses-table' ); ?></label></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Show course duration', 'lms-courses-table' ); ?></th>
                            <td><label><input type="checkbox" name="abct_settings[show_duration]" value="1" <?php checked( abct_opt('show_duration','0'), '1' ); ?> /> <?php esc_html_e( 'Yes', 'lms-courses-table' ); ?></label></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( '"Course full" label', 'lms-courses-table' ); ?></th>
                            <td><input type="text" name="abct_settings[no_seats_label]" value="<?php echo esc_attr( abct_opt('no_seats_label') ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Course is full', 'lms-courses-table' ); ?>" /></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Primary color', 'lms-courses-table' ); ?></th>
                            <td><input type="color" name="abct_settings[primary_color]" value="<?php echo esc_attr( abct_opt('primary_color','#1A5276') ); ?>" /></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Accent color', 'lms-courses-table' ); ?></th>
                            <td><input type="color" name="abct_settings[accent_color]" value="<?php echo esc_attr( abct_opt('accent_color','#B7950B') ); ?>" /></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Registration page URL', 'lms-courses-table' ); ?></th>
                            <td><input type="url" name="abct_settings[registration_page]" value="<?php echo esc_attr( abct_opt('registration_page') ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Leave empty to use the course URL', 'lms-courses-table' ); ?>" /></td>
                        </tr>
                    </table>
                </div>

                <!-- Tutor LMS Settings -->
                <div style="background:#fff;border:1px solid #ddd;border-radius:8px;padding:20px;">
                    <h2 style="margin-top:0;font-size:15px;border-bottom:1px solid #eee;padding-bottom:8px;">
                        🔧 <?php esc_html_e( 'Tutor LMS Settings', 'lms-courses-table' ); ?>
                    </h2>
                    <table class="form-table" style="margin:0;">
                        <tr>
                            <th style="width:170px;"><?php esc_html_e( 'Category taxonomy', 'lms-courses-table' ); ?></th>
                            <td>
                                <input type="text" name="abct_settings[category_taxonomy]" value="<?php echo esc_attr( abct_opt('category_taxonomy','course-category') ); ?>" class="regular-text" />
                                <p class="description"><?php esc_html_e( 'Default:', 'lms-courses-table' ); ?> <code>course-category</code></p>
                            </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Start date meta key', 'lms-courses-table' ); ?></th>
                            <td><input type="text" name="abct_settings[start_date_meta]" value="<?php echo esc_attr( abct_opt('start_date_meta','_tutor_course_start_date') ); ?>" class="regular-text" /></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Start time meta key', 'lms-courses-table' ); ?></th>
                            <td><input type="text" name="abct_settings[start_time_meta]" value="<?php echo esc_attr( abct_opt('start_time_meta','_tutor_course_start_time') ); ?>" class="regular-text" /></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'End time meta key', 'lms-courses-table' ); ?></th>
                            <td><input type="text" name="abct_settings[end_time_meta]" value="<?php echo esc_attr( abct_opt('end_time_meta','_tutor_course_end_time') ); ?>" class="regular-text" /></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Delivery mode meta key', 'lms-courses-table' ); ?></th>
                            <td>
                                <input type="text" name="abct_settings[delivery_meta]" value="<?php echo esc_attr( abct_opt('delivery_meta','_tutor_course_delivery') ); ?>" class="regular-text" />
                                <p class="description"><?php esc_html_e( 'Expected values:', 'lms-courses-table' ); ?> <code>online</code> / <code>offline</code></p>
                            </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Max students meta key', 'lms-courses-table' ); ?></th>
                            <td><input type="text" name="abct_settings[seats_meta]" value="<?php echo esc_attr( abct_opt('seats_meta','_tutor_course_max_students') ); ?>" class="regular-text" /></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Price meta key', 'lms-courses-table' ); ?></th>
                            <td><input type="text" name="abct_settings[price_meta]" value="<?php echo esc_attr( abct_opt('price_meta','_tutor_course_price') ); ?>" class="regular-text" /></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Enrolled students meta key', 'lms-courses-table' ); ?></th>
                            <td><input type="text" name="abct_settings[students_meta]" value="<?php echo esc_attr( abct_opt('students_meta','_tutor_course_enrolled_users_count') ); ?>" class="regular-text" /></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Level meta key', 'lms-courses-table' ); ?></th>
                            <td><input type="text" name="abct_settings[level_meta]" value="<?php echo esc_attr( abct_opt('level_meta','_tutor_course_level') ); ?>" class="regular-text" /></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Duration meta key', 'lms-courses-table' ); ?></th>
                            <td><input type="text" name="abct_settings[duration_meta]" value="<?php echo esc_attr( abct_opt('duration_meta','_tutor_course_duration') ); ?>" class="regular-text" /></td>
                        </tr>
                    </table>

                    <h2 style="font-size:15px;border-bottom:1px solid #eee;padding-bottom:8px;margin-top:20px;">
                        ⚡ <?php esc_html_e( 'Cache', 'lms-courses-table' ); ?>
                    </h2>
                    <table class="form-table" style="margin:0;">
                        <tr>
                            <th style="width:170px;"><?php esc_html_e( 'Enable cache', 'lms-courses-table' ); ?></th>
                            <td><label><input type="checkbox" name="abct_settings[enable_cache]" value="1" <?php checked( abct_opt('enable_cache','0'), '1' ); ?> /> <?php esc_html_e( 'Yes (faster page load)', 'lms-courses-table' ); ?></label></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Cache duration (hours)', 'lms-courses-table' ); ?></th>
                            <td><input type="number" name="abct_settings[cache_hours]" value="<?php echo esc_attr( abct_opt('cache_hours',12) ); ?>" min="1" max="168" class="small-text" /></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div style="background:#f0f6fc;border:1px solid #c3d9ed;border-radius:8px;padding:16px;max-width:980px;margin-top:20px;">
                <strong>📋 <?php esc_html_e( 'Shortcode:', 'lms-courses-table' ); ?></strong>
                <code style="background:#fff;padding:4px 10px;border-radius:4px;margin-right:8px;">[abct_courses_table]</code>
            </div>

            <?php submit_button( __( 'Save Settings', 'lms-courses-table' ), 'primary', 'submit', true, ['style'=>'margin-top:16px;'] ); ?>
        </form>
    </div>
    <?php
}

// ──────────────────────────────────────────────
// Enqueue assets
// ──────────────────────────────────────────────
add_action( 'wp_enqueue_scripts', function () {
    wp_enqueue_style(  'abct-style',  ABCT_URL . 'assets/style.css',  [], ABCT_VERSION );
    wp_enqueue_script( 'abct-script', ABCT_URL . 'assets/script.js',  [], ABCT_VERSION, true );

    $primary = abct_opt( 'primary_color', '#1A5276' );
    $accent  = abct_opt( 'accent_color',  '#B7950B' );
    wp_add_inline_style( 'abct-style', ":root{--abct-primary:{$primary};--abct-accent:{$accent};}" );

    // Pass translatable strings to JS
    wp_localize_script( 'abct-script', 'abctI18n', [
        'noResults'  => __( 'No matching courses found', 'lms-courses-table' ),
        'of'         => __( 'of', 'lms-courses-table' ),
        'prev'       => __( 'Previous', 'lms-courses-table' ),
        'next'       => __( 'Next', 'lms-courses-table' ),
    ] );
} );

// ──────────────────────────────────────────────
// Shortcode
// ──────────────────────────────────────────────
add_shortcode( 'abct_courses_table', function ( $atts ) {
    $atts = shortcode_atts( [
        'per_page'          => abct_opt( 'per_page', 8 ),
        'show_search'       => abct_opt( 'show_search', '1' )       === '1' ? 'yes' : 'no',
        'show_mode_toggle'  => abct_opt( 'show_mode_toggle', '1' )  === '1' ? 'yes' : 'no',
        'show_categories'   => abct_opt( 'show_categories', '1' )   === '1' ? 'yes' : 'no',
        'show_seats'        => abct_opt( 'show_seats', '0' )        === '1' ? 'yes' : 'no',
        'show_price'        => abct_opt( 'show_price', '0' )        === '1' ? 'yes' : 'no',
        'show_students'     => abct_opt( 'show_students', '0' )     === '1' ? 'yes' : 'no',
        'show_level'        => abct_opt( 'show_level', '0' )        === '1' ? 'yes' : 'no',
        'show_duration'     => abct_opt( 'show_duration', '0' )     === '1' ? 'yes' : 'no',
        'registration_page' => abct_opt( 'registration_page', '' ),
        'category_taxonomy' => abct_opt( 'category_taxonomy', 'course-category' ),
        'delivery_meta'     => abct_opt( 'delivery_meta',     '_tutor_course_delivery' ),
        'start_date_meta'   => abct_opt( 'start_date_meta',   '_tutor_course_start_date' ),
        'start_time_meta'   => abct_opt( 'start_time_meta',   '_tutor_course_start_time' ),
        'end_time_meta'     => abct_opt( 'end_time_meta',     '_tutor_course_end_time' ),
        'seats_meta'        => abct_opt( 'seats_meta',        '_tutor_course_max_students' ),
        'price_meta'        => abct_opt( 'price_meta',        '_tutor_course_price' ),
        'students_meta'     => abct_opt( 'students_meta',     '_tutor_course_enrolled_users_count' ),
        'level_meta'        => abct_opt( 'level_meta',        '_tutor_course_level' ),
        'duration_meta'     => abct_opt( 'duration_meta',     '_tutor_course_duration' ),
        'no_seats_label'    => abct_opt( 'no_seats_label',    __( 'Course is full', 'lms-courses-table' ) ),
        'sort_by'           => abct_opt( 'sort_by',           'date' ),
        'sort_order'        => abct_opt( 'sort_order',        'DESC' ),
    ], $atts, 'abct_courses_table' );

    if ( ! function_exists( 'tutor' ) && ! defined( 'TUTOR_VERSION' ) ) {
        return '<p style="color:red;">' . esc_html__( 'Tutor LMS is not active or not installed.', 'lms-courses-table' ) . '</p>';
    }

    $courses    = abct_get_courses( $atts );
    $categories = abct_get_categories( $atts['category_taxonomy'] );

    ob_start();
    include ABCT_PATH . 'templates/table.php';
    return ob_get_clean();
} );

// ──────────────────────────────────────────────
// جلب الكتيجوريز
// ──────────────────────────────────────────────
function abct_get_categories( $taxonomy ) {
    $terms = get_terms( [
        'taxonomy'   => $taxonomy,
        'hide_empty' => true,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ] );
    if ( is_wp_error( $terms ) || empty( $terms ) ) return [];
    return $terms;
}

// ──────────────────────────────────────────────
// جلب الكورسات (مع Cache)
// ──────────────────────────────────────────────
function abct_get_courses( $atts ) {
    $cache_key = 'abct_courses_' . md5( serialize( $atts ) );

    if ( abct_opt( 'enable_cache', '0' ) === '1' ) {
        $cached = get_transient( $cache_key );
        if ( $cached !== false ) return $cached;
    }

    $query_args = [
        'post_type'      => 'courses',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => $atts['sort_by'] === 'meta_value' ? 'meta_value' : $atts['sort_by'],
        'order'          => $atts['sort_order'],
    ];
    if ( $atts['sort_by'] === 'meta_value' ) {
        $query_args['meta_key'] = $atts['start_date_meta']; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
    }

    $query   = new WP_Query( $query_args );
    $courses = [];

    foreach ( $query->posts as $post ) {
        $start_date = get_post_meta( $post->ID, $atts['start_date_meta'], true );
        if ( empty( $start_date ) ) {
            $start_date = get_the_date( 'Y/m/d', $post->ID );
        } else {
            $start_date = gmdate( 'Y/m/d', strtotime( $start_date ) );
        }

        $day_ar    = abct_day_arabic( $start_date );
        $time_from = get_post_meta( $post->ID, $atts['start_time_meta'], true );
        $time_to   = get_post_meta( $post->ID, $atts['end_time_meta'],   true );
        $time_str  = ( $time_from && $time_to ) ? 'من ' . $time_from . ' حتى ' . $time_to : '';

        $delivery = get_post_meta( $post->ID, $atts['delivery_meta'], true );
        if ( empty( $delivery ) ) $delivery = 'offline';

        $terms     = get_the_terms( $post->ID, $atts['category_taxonomy'] );
        $cat_slugs = [];
        if ( $terms && ! is_wp_error( $terms ) ) {
            foreach ( $terms as $term ) $cat_slugs[] = $term->slug;
        }

        $max_seats  = (int) get_post_meta( $post->ID, $atts['seats_meta'], true );
        $enrolled   = (int) get_post_meta( $post->ID, $atts['students_meta'], true );
        $seats_left = $max_seats > 0 ? max( 0, $max_seats - $enrolled ) : -1;
        $seats_full = $max_seats > 0 && $seats_left === 0;

        $price   = get_post_meta( $post->ID, $atts['price_meta'], true );
        $is_free = empty( $price ) || (float) $price === 0.0;

        $level_map = [
            'beginner'     => __( 'Beginner', 'lms-courses-table' ),
            'intermediate' => __( 'Intermediate', 'lms-courses-table' ),
            'expert'       => __( 'Advanced', 'lms-courses-table' ),
        ];
        $level_raw = get_post_meta( $post->ID, $atts['level_meta'], true );
        $level     = $level_map[ $level_raw ] ?? $level_raw;

        $duration = get_post_meta( $post->ID, $atts['duration_meta'], true );

        $courses[] = [
            'id'         => $post->ID,
            'title'      => get_the_title( $post->ID ),
            'date'       => $day_ar . ' ' . __( 'corresponding to', 'lms-courses-table' ) . ' ' . $start_date,
            'date_raw'   => $start_date,
            'time'       => $time_str,
            'permalink'  => get_permalink( $post->ID ),
            'delivery'   => $delivery,
            'cat_slugs'  => implode( ',', $cat_slugs ),
            'seats_left' => $seats_left,
            'seats_full' => $seats_full,
            'enrolled'   => $enrolled,
            'price'      => $price,
            'is_free'    => $is_free,
            'level'      => $level,
            'duration'   => $duration,
        ];
    }

    if ( abct_opt( 'enable_cache', '0' ) === '1' ) {
        set_transient( $cache_key, $courses, abct_opt( 'cache_hours', 12 ) * HOUR_IN_SECONDS );
    }

    return $courses;
}

// ──────────────────────────────────────────────
// تحويل اليوم للعربي (قابل للترجمة)
// ──────────────────────────────────────────────
function abct_day_arabic( $date_str ) {
    $days = [
        'Saturday'  => __( 'Saturday',  'lms-courses-table' ),
        'Sunday'    => __( 'Sunday',    'lms-courses-table' ),
        'Monday'    => __( 'Monday',    'lms-courses-table' ),
        'Tuesday'   => __( 'Tuesday',   'lms-courses-table' ),
        'Wednesday' => __( 'Wednesday', 'lms-courses-table' ),
        'Thursday'  => __( 'Thursday',  'lms-courses-table' ),
        'Friday'    => __( 'Friday',    'lms-courses-table' ),
    ];
    $day_en = gmdate( 'l', strtotime( $date_str ) );
    return $days[ $day_en ] ?? $day_en;
}

// ──────────────────────────────────────────────
// Clear cache on course save
// ──────────────────────────────────────────────
add_action( 'save_post_courses', function () {
    global $wpdb;
    $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_abct_courses_%'" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
} );
