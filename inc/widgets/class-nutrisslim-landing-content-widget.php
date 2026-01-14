<?php
use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class Nutrisslim_Landing_Content_Widget extends Widget_Base {

    public function get_name() {
        return 'nutrisslim_landing_content_widget';
    }

    public function get_title() {
        return __( 'Nutrisslim Landing Content Widget', 'text-domain' );
    }

    public function get_icon() {
        return 'eicon-comments';
    }

    public function get_categories() {
        return [ 'nutrisslim-landing' ];
    }

    protected function _register_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __( 'Content', 'text-domain' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'width_option',
            [
                'label' => __( 'Width', 'text-domain' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'custom',
                'options' => [
                    'default' => __( 'Default', 'text-domain' ),
                    'full-width' => __( 'Full Width', 'text-domain' ),
                    'custom' => __( 'Custom', 'text-domain' ),
                ],
            ]
        );
    
        $this->add_control(
            'custom_width',
            [
                'label' => __( 'Custom Width', 'text-domain' ),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'range' => [
                    'px' => [
                        'min' => 320,
                        'max' => 1920,
                    ],
                    '%' => [
                        'min' => 10,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 1715,
                ],
                'condition' => [
                    'width_option' => 'custom',
                ],
            ]
        );        
    
        $this->end_controls_section();
    }

    protected function render() {

    // Get ACF field
    $selected = get_field('selected_product');

    // Normalize to product ID
    $product_id = 0;

    if (empty($selected)) {
        // Nothing selected
        if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
            echo '<div class="ns-widget-notice">Please select a product in the "selected_product" ACF field.</div>';
        }
        return;
    }

    // ACF relationship / post object can be array, single ID, WP_Post, or WC_Product
    if (is_array($selected)) {
        // e.g. [123] or [0 => 123] or [ WP_Post, ... ]
        $first = reset($selected);

        if ($first instanceof WP_Post) {
            $product_id = (int) $first->ID;
        } elseif ($first instanceof WC_Product) {
            $product_id = (int) $first->get_id();
        } else {
            $product_id = (int) $first;
        }
    } elseif ($selected instanceof WP_Post) {
        $product_id = (int) $selected->ID;
    } elseif ($selected instanceof WC_Product) {
        $product_id = (int) $selected->get_id();
    } else {
        $product_id = (int) $selected;
    }

    if (!$product_id) {
        if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
            echo '<div class="ns-widget-notice">Selected product is invalid.</div>';
        }
        return;
    }

    // Load product safely
    $productObj = wc_get_product($product_id);

    if (!$productObj || !is_a($productObj, 'WC_Product')) {
        if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
            echo '<div class="ns-widget-notice">Selected product could not be loaded as a WooCommerce product.</div>';
        }
        return;
    }

    // Now it's safe to use it
    $type = $productObj->get_type();
    $real = ($type === 'nutrisslim') ? true : '';

    ?>
    <div class="grid-2_sm-1 woocommerce">
        <div class="col imgHolder">
            <?php 
                $prices_include_tax = get_option('woocommerce_prices_include_tax') === 'yes';

                $regular_price = $prices_include_tax
                    ? $productObj->get_regular_price()
                    : wc_get_price_including_tax($productObj, ['price' => $productObj->get_regular_price()]);               

                $quantities = get_field('quantity_prices', $product_id);

                // Get custom price for one with tax
                $price_for_one = get_custom_product_price($product_id, 1, get_the_ID(), '', $real);
                $price_for_one = wc_get_price_including_tax($productObj, ['price' => $price_for_one]);      

                if ($regular_price > 0 && $price_for_one) {
                    $discount_percentage = (($regular_price - $price_for_one) / $regular_price) * 100;    
                    echo '<span class="onsale">-' . round($discount_percentage) . '%</span>';
                }

                get_product_image($product_id);

                echo '<div class="tag-list-section"><p>';
                $terms = get_the_terms($product_id, 'product_tag');

                if ($terms && !is_wp_error($terms)) {
                    $tag_names = array_map(function($term) {
                        return $term->name;
                    }, $terms);
                    $tags_string = implode(' | ', $tag_names);
                    echo esc_html($tags_string);
                }                
                echo '</p></div>';

                $main_review = get_field('main_review', $product_id);

                if (!empty($main_review) && !empty($main_review['review'])) {
                    $image_url = wp_get_attachment_image_url($main_review['image'], 'medium');
            ?>
            <div class="mainReview primary-transparent-bg-color">
                <div class="inner">
                    <?php echo '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($main_review['name']) . '">'; ?>
                    <div class="revContent">
                        <div class="name"><?php echo esc_html($main_review['name']); ?></div>
                        <div class="rateMeta">
                            <img class="star" src="/wp-content/uploads/2024/03/star.png" />
                            <img class="star" src="/wp-content/uploads/2024/03/star.png" />
                            <img class="star" src="/wp-content/uploads/2024/03/star.png" />
                            <img class="star" src="/wp-content/uploads/2024/03/star.png" />
                            <img class="star" src="/wp-content/uploads/2024/03/star.png" />
                            <div class="rate"><?php echo esc_html($main_review['rate']); ?> / 5</div>
                            <div class="checker">
                                <span class="check">
                                    <img src="/wp-content/uploads/2024/03/whiteCheck.png" />
                                </span>
                                <?php echo __('Geverifieerde gebruiker', 'nutrisslim-suite'); ?>
                            </div>
                        </div>
                        <div class="revComment">
                            <p><?php echo esc_html($main_review['review']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
        <div class="col contHolder product-short-description">
            <h2><?php echo esc_html($productObj->get_name()); ?></h2>
            <?php
                $subtitle = get_field('subtitle', $product_id);

                echo '<div class="product-short-description">';
                if ($subtitle) {
                    echo '<p class="subtitle">' . esc_html($subtitle) . '</p>';
                }
                echo do_shortcode('[product_rating id="' . $product_id . '"]');
                echo apply_filters('the_content', $productObj->get_short_description());
                echo '</div>';

                $weightOutput = '';
                $consumptionPeriodOutput = '';

                $weightValue = $productObj->get_weight();
                if (!empty($weightValue)) {
                    $weightOutput = "Netto " . $weightValue . " " . get_option('woocommerce_weight_unit');
                }

                $consumption_period = get_field('consumption_period', $product_id);
                if (!empty($consumption_period)) {
                    $consumptionPeriodOutput = sprintf(__('voor %s dagen', 'nutrisslim-suite'), $consumption_period);
                }

                $combinedOutput = $weightOutput;
                if (!empty($weightOutput) && !empty($consumptionPeriodOutput)) {
                    $combinedOutput .= " | " . $consumptionPeriodOutput;
                } elseif (empty($weightOutput) && !empty($consumptionPeriodOutput)) {
                    $combinedOutput = $consumptionPeriodOutput;
                }

                if (!empty($combinedOutput)) {
                    echo '<div class="product-details"><strong>' . esc_html($combinedOutput) . '</strong></div>';
                }
                
                echo '<p class="redno">' . __('Reguliere prijs', 'woocommerce') . ':<br /><span>' . wc_price($regular_price) . '</span></p>';
                echo '<div class="price-main price-large">' . wc_price($price_for_one) . '</div>';
                echo '<p><a href="#order-form-anchor" class="org-btn">' . __('Bestel nu', 'woocommerce') . '</a></p>';
                echo '<p class="prihrani">' . __('en bespaar', 'nutrisslim-suite') . ' ' . wc_price($regular_price - $price_for_one) . '</p>';
                echo '<p>' . __('100% veilige aankoop met een retourbeleid zonder vragen.', 'nutrisslim-suite') . '</p>';
            ?>
        </div>
    </div>
    <?php
}

}