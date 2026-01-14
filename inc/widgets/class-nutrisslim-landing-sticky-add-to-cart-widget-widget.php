<?php
use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class Sticky_Add_To_Cart_Widget extends Widget_Base {

    public function get_name() {
        return 'sticky_add_to_cart_widget';
    }

    public function get_title() {
        return __( 'Sticky Add To Cart', 'text-domain' );
    }

    public function get_icon() {
        return 'eicon-cart';
    }

    public function get_categories() {
        return [ 'nutrisslim-landing' ];
    }

    protected function render() {

    // Get ACF field
    $selected = get_field('selected_product');

    // Normalize to product ID
    $product_id = 0;

    if (empty($selected)) {
        // Nothing selected
        if ( class_exists('\Elementor\Plugin') && \Elementor\Plugin::$instance->editor && \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
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
        if ( class_exists('\Elementor\Plugin') && \Elementor\Plugin::$instance->editor && \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
            echo '<div class="ns-widget-notice">Selected product is invalid.</div>';
        }
        return;
    }

    // Load product safely
    $productObj = wc_get_product($product_id);

    if (!$productObj || !is_a($productObj, 'WC_Product')) {
        if ( class_exists('\Elementor\Plugin') && \Elementor\Plugin::$instance->editor && \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
            echo '<div class="ns-widget-notice">Selected product could not be loaded as a WooCommerce product.</div>';
        }
        return;
    }

    // Now it's safe to use it
    $type = $productObj->get_type();
    $real = ($type === 'nutrisslim') ? true : '';

    // Regular price including tax
    $regular_price = wc_get_price_including_tax($productObj, [
        'price' => $productObj->get_regular_price(),
    ]);

    // Custom price for one unit (net)
    $price_for_one = get_custom_product_price($product_id, 1, get_the_ID(), '', $real);

    // Add tax to the custom price
    $price_for_one_with_tax = wc_get_price_including_tax($productObj, [
        'price' => $price_for_one,
    ]);
    ?>
    <div class="sticky-add-to-cart">
        <span class="regular-price"><?php echo wc_price($regular_price); ?></span>
        <span class="sale-price"><?php echo wc_price($price_for_one_with_tax); ?></span>

        <a href="#order-form-anchor" class="org-btn"><?php _e('BESTEL NU', 'woocommerce'); ?></a>
    </div>
    <script>
    jQuery(function($) {
        var $this = $('div.sticky-add-to-cart');
        var $stickyContainer = $this.closest('.e-con').addClass('sticky-container');

        $(window).on('scroll', function() {
            var orderFormAnchor = $('#order-form-anchor');
            if (orderFormAnchor.length) {
                var orderFormOffset = orderFormAnchor.offset().top;
                var scrollTop = $(window).scrollTop();
                var windowHeight = $(window).height();

                if (scrollTop + windowHeight >= orderFormOffset) {
                    $stickyContainer.addClass('slide-down');
                } else {
                    $stickyContainer.removeClass('slide-down');
                }
            }
        });
    });
    </script>
    <?php
}

}