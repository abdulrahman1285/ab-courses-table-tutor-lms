<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="abct-wrapper" dir="rtl">

    <?php if ( $atts['show_categories'] === 'yes' && ! empty( $categories ) ) : ?>
    <div class="abct-categories-tabs">
        <button class="abct-cat-tab abct-active" data-cat="all"><?php esc_html_e( 'All', 'ab-lms-courses-table' ); ?></button>
        <?php foreach ( $categories as $cat ) : ?>
            <button class="abct-cat-tab" data-cat="<?php echo esc_attr( $cat->slug ); ?>">
                <?php echo esc_html( $cat->name ); ?>
            </button>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="abct-controls-bar">
        <?php if ( $atts['show_mode_toggle'] === 'yes' ) : ?>
        <div class="abct-mode-toggle">
            <button class="abct-mode-btn abct-mode-active" data-mode="offline"><?php esc_html_e( 'In-person', 'ab-lms-courses-table' ); ?></button>
            <button class="abct-mode-btn" data-mode="online"><?php esc_html_e( 'Online', 'ab-lms-courses-table' ); ?></button>
        </div>
        <?php endif; ?>

        <?php if ( $atts['show_search'] === 'yes' ) : ?>
        <div class="abct-search-wrap">
            <span class="abct-search-label"><?php esc_html_e( 'Course name', 'ab-lms-courses-table' ); ?></span>
            <input type="text" class="abct-search" placeholder="<?php esc_attr_e( 'Type here...', 'ab-lms-courses-table' ); ?>" />
        </div>
        <?php endif; ?>
    </div>

    <div class="abct-table-wrap">
        <table class="abct-table">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Course', 'ab-lms-courses-table' ); ?></th>
                    <th><?php esc_html_e( 'Start date', 'ab-lms-courses-table' ); ?></th>
                    <th><?php esc_html_e( 'Start time', 'ab-lms-courses-table' ); ?></th>
                    <?php if ( $atts['show_price']    === 'yes' ) : ?><th><?php esc_html_e( 'Price', 'ab-lms-courses-table' ); ?></th><?php endif; ?>
                    <?php if ( $atts['show_students'] === 'yes' ) : ?><th><?php esc_html_e( 'Students', 'ab-lms-courses-table' ); ?></th><?php endif; ?>
                    <?php if ( $atts['show_seats']    === 'yes' ) : ?><th><?php esc_html_e( 'Seats', 'ab-lms-courses-table' ); ?></th><?php endif; ?>
                    <?php if ( $atts['show_level']    === 'yes' ) : ?><th><?php esc_html_e( 'Level', 'ab-lms-courses-table' ); ?></th><?php endif; ?>
                    <?php if ( $atts['show_duration'] === 'yes' ) : ?><th><?php esc_html_e( 'Duration', 'ab-lms-courses-table' ); ?></th><?php endif; ?>
                    <th><?php esc_html_e( 'Details', 'ab-lms-courses-table' ); ?></th>
                    <th><?php esc_html_e( 'Register', 'ab-lms-courses-table' ); ?></th>
                </tr>
            </thead>
            <tbody id="abct-tbody">
            <?php if ( empty( $courses ) ) : ?>
                <tr><td colspan="10" class="abct-empty"><?php esc_html_e( 'No courses available at the moment', 'ab-lms-courses-table' ); ?></td></tr>
            <?php else : ?>
            <?php foreach ( $courses as $abct_course ) :
                $abct_reg_url        = ! empty( $atts['registration_page'] ) ? $atts['registration_page'] : $abct_course['permalink'];
                $abct_is_full        = $abct_course['seats_full'];
                $abct_no_seats_label = ! empty( $atts['no_seats_label'] ) ? $atts['no_seats_label'] : __( 'Course is full', 'ab-lms-courses-table' );
            ?>
                <tr class="abct-row<?php echo $abct_is_full ? ' abct-row-full' : ''; ?>"
                    data-title="<?php echo esc_attr( mb_strtolower( $abct_course['title'] ) ); ?>"
                    data-delivery="<?php echo esc_attr( $abct_course['delivery'] ); ?>"
                    data-cats="<?php echo esc_attr( $abct_course['cat_slugs'] ); ?>"
                >
                    <td class="abct-course-name">
                        <?php echo esc_html( $abct_course['title'] ); ?>
                        <?php if ( $abct_is_full ) : ?>
                            <span class="abct-badge-full"><?php echo esc_html( $abct_no_seats_label ); ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="abct-date-cell"><?php echo esc_html( $abct_course['date'] ); ?></td>
                    <td class="abct-time-cell"><?php echo esc_html( $abct_course['time'] ) ?: '—'; ?></td>

                    <?php if ( $atts['show_price'] === 'yes' ) : ?>
                    <td class="abct-price-cell <?php echo $abct_course['is_free'] ? 'abct-free' : ''; ?>">
                        <?php echo $abct_course['is_free'] ? esc_html__( 'Free', 'ab-lms-courses-table' ) : esc_html( number_format( (float) $abct_course['price'], 0 ) ) . ' ' . esc_html( function_exists('get_woocommerce_currency') ? get_woocommerce_currency() : 'EGP' ); ?>
                    </td>
                    <?php endif; ?>

                    <?php if ( $atts['show_students'] === 'yes' ) : ?>
                    <td class="abct-meta-cell">👤 <?php echo esc_html( number_format( $abct_course['enrolled'] ) ); ?></td>
                    <?php endif; ?>

                    <?php if ( $atts['show_seats'] === 'yes' ) : ?>
                    <td class="abct-meta-cell <?php echo $abct_is_full ? 'abct-seats-full' : ''; ?>">
                        <?php
                        if ( $abct_course['seats_left'] < 0 ) {
                            echo '—';
                        } elseif ( $abct_is_full ) {
                            echo '<span class="abct-badge-full">' . esc_html( $abct_no_seats_label ) . '</span>';
                        } else {
                            /* translators: %d = number of seats left */
                            echo esc_html( sprintf( _n( '%d seat', '%d seats', $abct_course['seats_left'], 'ab-lms-courses-table' ), $abct_course['seats_left'] ) );
                        }
                        ?>
                    </td>
                    <?php endif; ?>

                    <?php if ( $atts['show_level'] === 'yes' ) : ?>
                    <td class="abct-meta-cell"><?php echo esc_html( $abct_course['level'] ) ?: '—'; ?></td>
                    <?php endif; ?>

                    <?php if ( $atts['show_duration'] === 'yes' ) : ?>
                    <td class="abct-meta-cell"><?php echo esc_html( $abct_course['duration'] ) ?: '—'; ?></td>
                    <?php endif; ?>

                    <td>
                        <a href="<?php echo esc_url( $abct_course['permalink'] ); ?>" class="abct-link-detail" target="_blank"><?php esc_html_e( 'View details', 'ab-lms-courses-table' ); ?></a>
                    </td>
                    <td>
                        <?php if ( $abct_is_full ) : ?>
                            <span class="abct-btn-disabled"><?php echo esc_html( $abct_no_seats_label ); ?></span>
                        <?php else : ?>
                            <a href="<?php echo esc_url( $abct_reg_url ); ?>" class="abct-btn-register">&#8635; <?php esc_html_e( 'Send registration request', 'ab-lms-courses-table' ); ?></a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="abct-pagination" data-per-page="<?php echo intval( $atts['per_page'] ); ?>"></div>
</div>
