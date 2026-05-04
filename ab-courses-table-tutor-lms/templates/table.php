<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="abct-wrapper" dir="rtl">

    <?php if ( $atts['show_categories'] === 'yes' && ! empty( $categories ) ) : ?>
    <div class="abct-categories-tabs">
        <button class="abct-cat-tab abct-active" data-cat="all"><?php esc_html_e( 'All', 'ab-courses-table-tutor-lms' ); ?></button>
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
            <button class="abct-mode-btn abct-mode-active" data-mode="offline"><?php esc_html_e( 'In-person', 'ab-courses-table-tutor-lms' ); ?></button>
            <button class="abct-mode-btn" data-mode="online"><?php esc_html_e( 'Online', 'ab-courses-table-tutor-lms' ); ?></button>
        </div>
        <?php endif; ?>

        <?php if ( $atts['show_search'] === 'yes' ) : ?>
        <div class="abct-search-wrap">
            <span class="abct-search-label"><?php esc_html_e( 'Course name', 'ab-courses-table-tutor-lms' ); ?></span>
            <input type="text" class="abct-search" placeholder="<?php esc_attr_e( 'Type here...', 'ab-courses-table-tutor-lms' ); ?>" />
        </div>
        <?php endif; ?>
    </div>

    <div class="abct-table-wrap">
        <table class="abct-table">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Course', 'ab-courses-table-tutor-lms' ); ?></th>
                    <th><?php esc_html_e( 'Start date', 'ab-courses-table-tutor-lms' ); ?></th>
                    <th><?php esc_html_e( 'Start time', 'ab-courses-table-tutor-lms' ); ?></th>
                    <?php if ( $atts['show_price']    === 'yes' ) : ?><th><?php esc_html_e( 'Price', 'ab-courses-table-tutor-lms' ); ?></th><?php endif; ?>
                    <?php if ( $atts['show_students'] === 'yes' ) : ?><th><?php esc_html_e( 'Students', 'ab-courses-table-tutor-lms' ); ?></th><?php endif; ?>
                    <?php if ( $atts['show_seats']    === 'yes' ) : ?><th><?php esc_html_e( 'Seats', 'ab-courses-table-tutor-lms' ); ?></th><?php endif; ?>
                    <?php if ( $atts['show_level']    === 'yes' ) : ?><th><?php esc_html_e( 'Level', 'ab-courses-table-tutor-lms' ); ?></th><?php endif; ?>
                    <?php if ( $atts['show_duration'] === 'yes' ) : ?><th><?php esc_html_e( 'Duration', 'ab-courses-table-tutor-lms' ); ?></th><?php endif; ?>
                    <th><?php esc_html_e( 'Details', 'ab-courses-table-tutor-lms' ); ?></th>
                    <th><?php esc_html_e( 'Register', 'ab-courses-table-tutor-lms' ); ?></th>
                </tr>
            </thead>
            <tbody id="abct-tbody">
            <?php if ( empty( $courses ) ) : ?>
                <tr><td colspan="10" class="abct-empty"><?php esc_html_e( 'No courses available at the moment', 'ab-courses-table-tutor-lms' ); ?></td></tr>
            <?php else : ?>
            <?php foreach ( $courses as $course ) :
                $reg_url = ! empty( $atts['registration_page'] ) ? $atts['registration_page'] : $course['permalink'];
                $is_full = $course['seats_full'];
                $no_seats_label = ! empty( $atts['no_seats_label'] ) ? $atts['no_seats_label'] : __( 'Course is full', 'ab-courses-table-tutor-lms' );
            ?>
                <tr class="abct-row<?php echo $is_full ? ' abct-row-full' : ''; ?>"
                    data-title="<?php echo esc_attr( mb_strtolower( $course['title'] ) ); ?>"
                    data-delivery="<?php echo esc_attr( $course['delivery'] ); ?>"
                    data-cats="<?php echo esc_attr( $course['cat_slugs'] ); ?>"
                >
                    <td class="abct-course-name">
                        <?php echo esc_html( $course['title'] ); ?>
                        <?php if ( $is_full ) : ?>
                            <span class="abct-badge-full"><?php echo esc_html( $no_seats_label ); ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="abct-date-cell"><?php echo esc_html( $course['date'] ); ?></td>
                    <td class="abct-time-cell"><?php echo esc_html( $course['time'] ) ?: '—'; ?></td>

                    <?php if ( $atts['show_price'] === 'yes' ) : ?>
                    <td class="abct-price-cell <?php echo $course['is_free'] ? 'abct-free' : ''; ?>">
                        <?php echo $course['is_free'] ? esc_html__( 'Free', 'ab-courses-table-tutor-lms' ) : esc_html( number_format( (float) $course['price'] ) ) . ' ' . esc_html__( 'EGP', 'ab-courses-table-tutor-lms' ); ?>
                    </td>
                    <?php endif; ?>

                    <?php if ( $atts['show_students'] === 'yes' ) : ?>
                    <td class="abct-meta-cell">👤 <?php echo esc_html( number_format( $course['enrolled'] ) ); ?></td>
                    <?php endif; ?>

                    <?php if ( $atts['show_seats'] === 'yes' ) : ?>
                    <td class="abct-meta-cell <?php echo $is_full ? 'abct-seats-full' : ''; ?>">
                        <?php
                        if ( $course['seats_left'] < 0 ) {
                            echo '—';
                        } elseif ( $is_full ) {
                            echo '<span class="abct-badge-full">' . esc_html( $no_seats_label ) . '</span>';
                        } else {
                            /* translators: %d = number of seats left */
                            echo esc_html( sprintf( _n( '%d seat', '%d seats', $course['seats_left'], 'ab-courses-table-tutor-lms' ), $course['seats_left'] ) );
                        }
                        ?>
                    </td>
                    <?php endif; ?>

                    <?php if ( $atts['show_level'] === 'yes' ) : ?>
                    <td class="abct-meta-cell"><?php echo esc_html( $course['level'] ) ?: '—'; ?></td>
                    <?php endif; ?>

                    <?php if ( $atts['show_duration'] === 'yes' ) : ?>
                    <td class="abct-meta-cell"><?php echo esc_html( $course['duration'] ) ?: '—'; ?></td>
                    <?php endif; ?>

                    <td>
                        <a href="<?php echo esc_url( $course['permalink'] ); ?>" class="abct-link-detail" target="_blank"><?php esc_html_e( 'View details', 'ab-courses-table-tutor-lms' ); ?></a>
                    </td>
                    <td>
                        <?php if ( $is_full ) : ?>
                            <span class="abct-btn-disabled"><?php echo esc_html( $no_seats_label ); ?></span>
                        <?php else : ?>
                            <a href="<?php echo esc_url( $reg_url ); ?>" class="abct-btn-register">&#8635; <?php esc_html_e( 'Send registration request', 'ab-courses-table-tutor-lms' ); ?></a>
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
