<?php
use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class Nutrisslim_Landing_Quantity_Selector extends Widget_Base {

    public function get_name() {
        return 'nutrisslim_landing_quantity_selector_widget';
    }

    public function get_title() {
        return __( 'Nutrisslim Landing Quantity Selector Widget', 'text-domain' );
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
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'width_option',
            [
                'label'   => __( 'Width', 'text-domain' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'custom',
                'options' => [
                    'default'     => __( 'Default', 'text-domain' ),
                    'full-width'  => __( 'Full Width', 'text-domain' ),
                    'custom'      => __( 'Custom', 'text-domain' ),
                ],
            ]
        );

        $this->add_control(
            'custom_width',
            [
                'label'     => __( 'Custom Width', 'text-domain' ),
                'type'      => \Elementor\Controls_Manager::SLIDER,
                'size_units'=> [ 'px', '%' ],
                'range'     => [
                    'px' => [
                        'min' => 320,
                        'max' => 1920,
                    ],
                    '%'  => [
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

        $this->add_control(
            'upsell_block_option',
            [
                'label'   => __( 'Upsell Block', 'text-domain' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'upsell-block-a',
                'options' => [
                    'upsell-block-a' => __( 'Upsell Block A', 'text-domain' ),
                    'upsell-block-b' => __( 'Upsell Block B', 'text-domain' ),
                    'upsell-block-c' => __( 'Upsell Block C', 'text-domain' ), // NEW
                ],
            ]
        );

        // Add controls for changing package text (only for upsell-block-b)
        $this->add_control(
            'package_1_text',
            [
                'label'     => __( 'Package 1 Text', 'text-domain' ),
                'type'      => \Elementor\Controls_Manager::TEXT,
                'default'   => '',
                'condition' => [
                    'upsell_block_option' => 'upsell-block-b',
                ],
            ]
        );

        $this->add_control(
            'package_2_text',
            [
                'label'     => __( 'Package 2 Text', 'text-domain' ),
                'type'      => \Elementor\Controls_Manager::TEXT,
                'default'   => '',
                'condition' => [
                    'upsell_block_option' => 'upsell-block-b',
                ],
            ]
        );

        $this->add_control(
            'package_3_text',
            [
                'label'     => __( 'Package 3 Text', 'text-domain' ),
                'type'      => \Elementor\Controls_Manager::TEXT,
                'default'   => '',
                'condition' => [
                    'upsell_block_option' => 'upsell-block-b',
                ],
            ]
        );

        // Upsell Block A Fields
        $this->add_control(
            'upsell_a_title_1',
            [
                'label'     => __( 'Upsell Block A - Title 1', 'text-domain' ),
                'type'      => \Elementor\Controls_Manager::TEXT,
                'default'   => '',
                'condition' => [
                    'upsell_block_option' => [ 'upsell-block-a', 'upsell-block-c' ], // Use same fields for C
                ],
            ]
        );

        $this->add_control(
            'upsell_a_text_1',
            [
                'label'     => __( 'Upsell Block A - Custom Text 1', 'text-domain' ),
                'type'      => \Elementor\Controls_Manager::TEXTAREA,
                'default'   => '',
                'condition' => [
                    'upsell_block_option' => [ 'upsell-block-a', 'upsell-block-c' ],
                ],
            ]
        );

        $this->add_control(
            'upsell_a_title_2',
            [
                'label'     => __( 'Upsell Block A - Title 2', 'text-domain' ),
                'type'      => \Elementor\Controls_Manager::TEXT,
                'default'   => '',
                'condition' => [
                    'upsell_block_option' => [ 'upsell-block-a', 'upsell-block-c' ],
                ],
            ]
        );

        $this->add_control(
            'upsell_a_text_2',
            [
                'label'     => __( 'Upsell Block A - Custom Text 2', 'text-domain' ),
                'type'      => \Elementor\Controls_Manager::TEXTAREA,
                'default'   => '',
                'condition' => [
                    'upsell_block_option' => [ 'upsell-block-a', 'upsell-block-c' ],
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        // If in admin edit screen for a "landing-page" post type, don't render
        if (is_admin()) {
            $current_screen = get_current_screen();
            if ($current_screen && $current_screen->base === 'post' && $current_screen->post_type === 'landing-page') {
                return ''; // Return empty to disable execution in admin preview
            }
        }

        $productref = get_field('selected_product');
        if (empty($productref)) {
            return ''; // Return early if no product is selected
        }

        $settings = $this->get_settings_for_display();
        $upsell_block_option = $settings['upsell_block_option'];

        // These are used only for upsell-block-b
        $package_1_text = !empty($settings['package_1_text']) ? $settings['package_1_text'] : '';
        $package_2_text = !empty($settings['package_2_text']) ? $settings['package_2_text'] : '';
        $package_3_text = !empty($settings['package_3_text']) ? $settings['package_3_text'] : '';

        $lid = get_the_ID();
        $subprices = get_post_meta($lid, '_subproducts_custom_prices', true);

        $productID = $productref[0];
        $product = wc_get_product($productID);

        if (!$product) {
            return ''; // Return early if product doesn't exist
        }

        $consumption_period = get_field('field_6615478b09bca', $productID);
        // If empty or zero, do not use it
        if (empty($consumption_period) || $consumption_period <= 0) {
            $consumption_period = false;
        }

        // Get prices
        $regular_price = wc_get_price_including_tax($product, ['price' => $product->get_regular_price()]);
        $regular_price_wo_tax = $product->get_regular_price();
        $sale_price = $product->get_price();
        $price_for_two = get_post_meta($product->get_id(), '_price_for_two', true);
        $price_for_three = get_post_meta($product->get_id(), '_price_for_three', true);
        $price_for_two_full = wc_get_price_including_tax($product, ['price' => $price_for_two]);
        $price_for_three_full = wc_get_price_including_tax($product, ['price' => $price_for_three]);

        // Ensure quantities are populated
        $quantities = get_field('quantity_prices');
        if (empty($quantities[0]['price'])) {
            $quantities[0]['price'] = $sale_price;
        }
        if (empty($quantities[1]['price'])) {
            $quantities[1]['price'] = $price_for_two;
        }
        if (empty($quantities[2]['price'])) {
            $quantities[2]['price'] = $price_for_three;
        }

        if (empty($quantities)) {
            return ''; // Return early if no quantities
        }

        // This function presumably sets an initial product in cart with custom price
        add_product_to_cart_with_custom_price($productID, $quantities[0]['price']);

        // Calculate discounts
        $discount_percentage  = $regular_price_wo_tax > 0 
            ? (($regular_price_wo_tax - $quantities[0]['price']) / $regular_price_wo_tax) * 100
            : 0;

        $discount_percentage2 = $regular_price_wo_tax > 0 && !empty($quantities[1]['price'])
            ? ((($regular_price_wo_tax * 2) - $quantities[1]['price']) / ($regular_price_wo_tax * 2)) * 100
            : 0;

        $discount_percentage3 = $regular_price_wo_tax > 0 && !empty($quantities[2]['price'])
            ? ((($regular_price_wo_tax * 3) - $quantities[2]['price']) / ($regular_price_wo_tax * 3)) * 100
            : 0;

        // Round them
        $quantities[0]['percent'] = round($discount_percentage);
        $quantities[1]['percent'] = round($discount_percentage2);
        $quantities[2]['percent'] = round($discount_percentage3);

        $attachment_id = $product->get_image_id(); // default product image

        // Shipping logic
        $free_shipping_threshold = 0;
        $shipping_zones = WC_Shipping_Zones::get_zones();
        foreach ($shipping_zones as $zone) {
            $shipping_methods = $zone['shipping_methods'];
            foreach ($shipping_methods as $method) {
                if (isset($method->instance_settings['cost']) && $method->instance_settings['cost'] && !$method->instance_settings['part_of_fee']) {
                    $shippingCost = floatval(str_replace(',', '.', $method->instance_settings['cost']));
                    $from         = $method->instance_settings['delivery_from'];
                    $to           = $method->instance_settings['delivery_to'];
                    $courier      = $method->instance_settings['title'];
                    $delivery_type= $method->instance_settings['delivery_type'];

                    $tax_class = get_option('woocommerce_shipping_tax_class');
                    if ($tax_class === 'inherit') {
                        $tax_class = '';
                    }
                    $tax_rates = WC_Tax::get_rates_for_tax_class($tax_class);
                    $tax_percentage = 0;

                    if (is_array($tax_rates) && !empty($tax_rates)) {
                        $first_rate = reset($tax_rates);
                        if (is_array($first_rate)) {
                            $tax_percentage = isset($first_rate['rate']) ? floatval($first_rate['rate']) : 0;
                        } elseif (is_object($first_rate)) {
                            $tax_percentage = isset($first_rate->tax_rate) ? floatval($first_rate->tax_rate) : 0;
                        }
                    }

                    $calculated_tax = ($shippingCost * $tax_percentage) / 100;
                    $shippingCost   = $shippingCost + $calculated_tax;
                    break;
                }
            }
            foreach ($shipping_methods as $method) {
                if ($method->id === 'free_shipping' && isset($method->min_amount)) {
                    $free_shipping_threshold = $method->min_amount;
                    break;
                }
            }
        }

		$singlePrice = $quantities[0]['price'];
        $singlePriceWithTax = wc_get_price_including_tax($product, ['price' => $singlePrice]);       
		if ($singlePriceWithTax >= $free_shipping_threshold) {
            $shippingCost = __('Gratis verzending', 'nutrisslim-suite');
        } else {
            $shippingCost = floatval(str_replace(',', '.', $shippingCost));
            $shippingCost = __('Verzendkosten', 'nutrisslim-suite') . ' ' . wc_price($shippingCost);
        }
        ?>
<div class="row mx-0">
    <!-- Content Slider -->
    <div class="slider-center col-12 px-0">
        <div class="content-slider container slick-initialized slick-slider" id="content-slider-1">
            <div class="slick-list">
                <div class="slick-track" style="opacity: 1; width: 450px;">
                    <div class="slide slick-slide slick-current slick-active" data-slick-index="0" aria-hidden="false"
                         style="width: 450px; position: relative; left: 0px; top: 0px; z-index: 999; opacity: 1;"
                         tabindex="0">
                        <div class="slider-flex">
                            <span class="slide-content"><?php _e('BESTELFORMULIER', 'nutrisslim-suite'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="countdown-progress-bar col-12 px-0 mx-auto mb-3"></div>
    <div class="col-12"></div>

    <!-- Product Display -->
    <div class="product-display col-sm-6 pt-sm-4 mb-auto">
        <div class="row mx-0 py-0">
            <div class="product-display-img col-12 px-0 pt-3">
                <?php
                if ($quantities[0]['percent'] > 0) {
                    echo '<span class="onsale">-' . $quantities[0]['percent'] . '%</span>';
                }
                $image_src_array = wp_get_attachment_image_src($attachment_id, 'large');
                $imgsrc = $image_src_array[0];
                echo '<img width="400" height="400" src="' . esc_url($imgsrc) . '" alt="' . esc_attr__('Product Image', 'nutrisslim-suite') . '">';
                ?>
            </div>
            <div class="col-12 pt-3 icon-check icon-primary-color text-center">
                <h2 class="product-title-offer">
                    <?php 
                    $product_title = get_the_title($productID);
                    echo esc_html(trim($product_title, '[]'));
                    ?>
                </h2>
                <div class="col-12 pt-3 shipping-details">
                    <?php
                    echo '<p class="center-text-mobile ddd"><strong>' . $shippingCost . '</strong><br>';
                    echo sprintf(__(' %s binnen 3-4 werkdagen', 'nutrisslim-suite'), $delivery_type, $courier, $from, $to ) . '</p>';
                    ?>
                </div>

                <?php if (!empty($quantities[1]['price']) || !empty($quantities[2]['price'])) { ?>
                    <div class="text-center cta-notice">
                        <h3><?php _e('Vásárolj többet, spórolj többet', 'nutrisslim-suite'); ?></h3>
                    </div>
                <?php } else {
                    // If only one quantity is available
                    $productObj = wc_get_product($product);
                    $regular_price_val = $productObj->get_regular_price();
                    $regular_price_val = wc_get_price_including_tax($product, ['qty' => 1, 'price' => $regular_price_val]);

                    $type = $productObj->get_type();
                    $real = ($type == 'nutrisslim') ? true : false;

                    $price_for_one = get_custom_product_price($product, 1, get_the_ID(), '', $real); 
                    $price_for_one_with_tax = wc_get_price_including_tax($product, ['qty' => 1, 'price' => $price_for_one]);
                    
                    // Display the regular price and discounted price if set
                    echo '<p class="redno">' . __('Normale prijs.', 'woocommerce') . ':<br /><span><s>' . wc_price($regular_price_val) . '</s></span></p>';
                    echo '<div style="font-size:40px;color:red;font-weight:700;">' . wc_price($price_for_one_with_tax) . '</div>';
                } ?>
            </div>
        </div>
    </div>
</div>

<?php
// If the 2x or 3x price is empty, hide the upsell area entirely
if (!empty($quantities[1]['price']) || !empty($quantities[2]['price'])) {
    $display_style = '';
} else {
    $display_style = 'style="display: none;"';
}
?>

<!-- ---------------------- UPSell Block A ---------------------- -->
<?php if ($upsell_block_option == 'upsell-block-a') : ?>
<div id="upsell-block" class="style-a<?php echo $upsell_block_option == 'upsell-block-a' ? '' : ' d-none'; ?>"
     <?php echo $display_style; ?>>
    <?php
    $custom_titles = array(
        1 => !empty($package_1_text) ? $package_1_text : __('Bestel 1 pakket', 'nutrisslim-suite'),
        2 => !empty($package_2_text) ? $package_2_text : __('Bestel 2 pakketten', 'nutrisslim-suite'),
        3 => !empty($package_3_text) ? $package_3_text : __('Bestel 3 pakketten', 'nutrisslim-suite'),
    );

    $custom_titles2 = array(
        1 => __('1x pakket', 'nutrisslim-suite'),
        2 => __('2x pakket', 'nutrisslim-suite'),
        3 => __('3x pakket', 'nutrisslim-suite'),
    );

    foreach ($quantities as $key => $item) {
        $num = $key + 1;

        if ($key == 1) {
            echo '<div class="text-center cta-notice mobile"><h3>' . __('Koop meer, bespaar meer', 'nutrisslim-suite') . '</h3></div>';
        }

        if ($quantities[$key]['price']) {
            $freeshipping = $quantities[$key]['price'] >= $free_shipping_threshold;
            // If consumption period is set
            if ($consumption_period) {
                $duration = $num * $consumption_period;
            }
            ?>
            <div class="offer custom_offer offer<?php echo $key . ' ' . ($key == 0 ? 'active' : ''); ?>"
                 data-key="<?php echo $key; ?>"
                 data-price="<?php echo $quantities[$key]['price']; ?>">
                <div class="offer-container">
                    <div class="text-center <?php echo ($key == 0 ? 'active' : ''); ?>">
                        <?php
                        $title  = isset($custom_titles[$num]) && !empty($custom_titles[$num]) ? $custom_titles[$num] : '';
                        $title2 = isset($custom_titles2[$num]) ? $custom_titles2[$num] : '';
                        if (!empty($title)) {
                            echo '<h3>' . $title . '</h3>';
                        }
                        ?>
                    </div>
                    <div class="bottom">
                        <div class="upsell-card">
                            <div class="offer-right">
                                <?php
                                $image_src_array = $item['image']
                                    ? wp_get_attachment_image_src($item['image'], 'medium')
                                    : wp_get_attachment_image_src($attachment_id, 'medium');
                                $imgsrc = $image_src_array[0];
                                ?>
                                <img width="400" height="500" src="<?php echo esc_url($imgsrc); ?>"
                                     alt="<?php echo esc_attr__('Product Image', 'nutrisslim-suite'); ?>"/>
                                <div class="circle-red">
                                    <div class="circle-text">
                                        <span class="circle-txt-large">-<?php echo $quantities[$key]['percent']; ?>%</span>
                                        <span class="circle-txt-small"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="offer-left">
                                <div class="cta">
                                    <div class="checkbox">
                                        <?php 
                                        echo '<input 
                                            data-id="' . $productID . '" 
                                            data-lid="' . get_the_ID() . '" 
                                            data-qty="' . $num . '" 
                                            data-price="' . round($quantities[$key]['price'], 2) . '" 
                                            id="checked0' . $num . '" 
                                            type="checkbox" ' . ($key == 0 ? 'checked' : '') .
                                            (isset($quantities[$key]['gift'][0]) ? ' data-gift="' . $quantities[$key]['gift'][0] . '"' : '') .
                                            (!empty($quantities[$key]['free_shipping']) ? ' data-free-shipping="1"' : '') .
                                            '>';
                                        ?>
                                        <label for="checked0<?php echo $num; ?>">
                                            <?php if (!empty($title2)) {
                                                echo '<h3>' . $title2 . '</h3>';
                                            } ?>
                                            <?php 
                                            if ($consumption_period && $duration > 0) {
                                                echo '<p style="font-size: smaller; padding-left: 20px; font-weight:300; padding-top: 10px"> voor ' . $duration . ' dagen</p>';
                                            }
                                            ?>
                                        </label>
                                    </div>
                                    <div class="price">
                                        <div class="price-old price-small">
                                            <span class="line-through precrtano">
                                                <?php
                                                echo sprintf(
                                                    __('Normale prijs. %s', 'nutrisslim-suite'),
                                                    wc_price($regular_price * $num)
                                                );
                                                ?>
                                            </span>
                                        </div>
                                        <div class="price-large">
                                            <span class="offer-price ee">
                                                <?php 
                                                $calculated_price = wc_get_price_including_tax($product, ['price' => $quantities[$key]['price']]);
                                                echo wc_price($calculated_price);
                                                ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <?php if (!$quantities[$key]['free_shipping'] && !$freeshipping) { ?>
                                    <div class="shipping-cost">
                                        <span><strong></strong></span>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <?php if ($quantities[$key]['free_shipping'] || $freeshipping) { ?>
                            <div class="offer_shipping">
                                <div class="plus">
                                    <img src="<?php echo esc_url(plugins_url('assets/checkPlus.svg', dirname(__FILE__, 2))); ?>"
                                         alt="<?php _e('plus', 'nutrisslim-suite'); ?>">
                                </div>
                                <div class="icon">
                                    <img src="<?php echo esc_url(plugins_url('assets/cart_free_delivery_ico.svg', dirname(__FILE__, 2))); ?>"
                                         alt="<?php _e('express-delivery', 'nutrisslim-suite'); ?>">
                                </div>
                                <?php _e('Gratis verzending', 'nutrisslim-suite'); ?>
                            </div>
                        <?php } ?>
                        <?php if (!empty($quantities[$key]['gift']) && isset($quantities[$key]['gift'][0])) { ?>
                            <div class="offer_shipping">
                                <div class="plus">
                                    <img src="<?php echo esc_url(plugins_url('assets/checkPlus.svg', dirname(__FILE__, 2))); ?>"
                                         alt="<?php _e('plus', 'nutrisslim-suite'); ?>">
                                </div>
                                <div class="icon">
                                    <img src="<?php echo esc_url(plugins_url('assets/darilo_ico.svg', dirname(__FILE__, 2))); ?>"
                                         alt="<?php _e('free_gift', 'nutrisslim-suite'); ?>">
                                </div>
                                <?php _e('Gratis cadeau', 'nutrisslim-suite'); ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <?php
        }
    }
    ?>
</div>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const offerBlocks = document.querySelectorAll("#upsell-block.style-a .offer");

    offerBlocks.forEach(block => {
        block.addEventListener("click", function() {
            // Uncheck all checkboxes and remove active class from all blocks
            offerBlocks.forEach(b => {
                b.classList.remove("active");
                b.querySelector(".text-center").classList.remove("active");
                b.querySelector("input[type='checkbox']").checked = false;
            });

            // Add active class to the clicked block and check its checkbox
            this.classList.add("active");
            this.querySelector(".text-center").classList.add("active");
            this.querySelector("input[type='checkbox']").checked = true;

            // Update the price (if you have a .order-review-price somewhere)
            // const selectedPrice = this.querySelector('.offer-price').textContent;
            // document.querySelector(".order-review-price").textContent = selectedPrice;
        });
    });
});
</script>
<?php endif; ?>

<!-- ---------------------- UPSell Block B ---------------------- -->
<?php if ($upsell_block_option == 'upsell-block-b') : ?>
<style>
    .ponudbab {
        border-style: dashed !important;
        border-width: 1px !important;
        padding-left: 10px !important;
        padding-right: 10px !important;
        padding-top: 5px !important;
        padding-bottom: 5px !important;
    }
    .ponudbaikona img {
        width:25px!important;
        top:1px!important;
        left:0px!important;
    }
    .offer_shipping.ponudbab { 
        font-size: 18px!important; 
        font-weight:300!important;
    }
</style>
<div id="upsell-block" class="style-b<?php echo $upsell_block_option == 'upsell-block-b' ? '' : 'disabled'; ?>">
    <?php
    foreach ($quantities as $key => $item) {
        $num = $key + 1;
        $package_text = ${'package_' . $num . '_text'};
        $freeshipping = $quantities[$key]['price'] >= $free_shipping_threshold;
        $has_gift = !empty($quantities[$key]['gift']) && isset($quantities[$key]['gift'][0]);
        ?>
        <div class="offer custom_offer offer<?php echo $key . ' ' . ($key == 0 ? 'active' : ''); ?>">
            <input data-id="<?php echo $productID; ?>"
                   data-lid="<?php echo get_the_ID(); ?>"
                   data-qty="<?php echo $num; ?>"
                   data-price="<?php echo round($quantities[$key]['price'], 2); ?>"
                   id="checked0<?php echo $num; ?>"
                   type="checkbox"
                   <?php echo ($key == 0 ? 'checked' : ''); ?>
                   <?php echo ($has_gift ? ' data-gift="' . $quantities[$key]['gift'][0] . '"' : ''); ?>
                   <?php echo ($freeshipping ? ' data-free-shipping="1"' : ''); ?>>
            <label for="offer<?php echo $key; ?>" class="offer-label">
                <div class="offer-container">
                    <div class="text-title">
                        <?php if (!empty($package_text)) { ?>
                            <h3><?php echo esc_html($package_text); ?></h3>
                        <?php } ?>
                    </div>
                    <div class="product-details">
                        <span class="product-quantity"><?php echo $num; ?>x</span>
                        <span class="product-name"><?php echo get_the_title($productID); ?></span>
                        <?php
                        // Display duration if consumption_period is available
                        if ($consumption_period) {
                            $duration = $num * $consumption_period;
                            $duration_text = $duration . '-day supply';
                            echo '<span class="product-duration">' . esc_html($duration_text) . '</span>';
                        }
                        ?>
                    </div>
                    <div class="image-block">
                        <?php
                        $imgsrc = $item['image']
                            ? wp_get_attachment_image_src($item['image'], 'medium')[0]
                            : wp_get_attachment_image_src($attachment_id, 'medium')[0];
                        ?>
                        <img width="400" height="500" src="<?php echo esc_url($imgsrc); ?>"
                             alt="<?php echo esc_attr__('Product Image', 'nutrisslim-suite'); ?>" />
                        <div class="circle-red circle-right">
                            <div class="circle-text">
                                <span class="circle-txt-large">-<?php echo $quantities[$key]['percent']; ?>%</span>
                            </div>
                        </div>
                    </div>
                    <div class="offer-block">
                    <div class="price-per-product">
                        <span class="price-per-product-text">
                            <?php _e('Price per product:', 'nutrisslim-suite'); ?>

                            <?php 
                            // ACF logic to fetch 'quantity' for each offer from custom fields
                            $bundle_data = get_field('field_663a89fffa70d', $productID); // Replace $productID with your actual variable
                            $acf_quantity = 0; // Initialize as 0 to start summing

                            if (is_array($bundle_data)) {
                                foreach ($bundle_data as $row) {
                                    // Check if 'quantity' is set and not empty for the current row
                                    if (!empty($row['quantity'])) {
                                        $acf_quantity += floatval($row['quantity']); // Add the quantity to the total
                                    }
                                }
                            }

                            // Multiply by how many items in the offer (if needed)
                            $acf_quantity *= $num;

                            // Price per product = total offer price / ACF quantity
                            $price_per_product_with_tax = 0;
                            if ($acf_quantity > 0) {
                                // Include tax in the total price
                                $price_with_tax = wc_get_price_including_tax($product, ['price' => $quantities[$key]['price']]);
                                $price_per_product_with_tax = $price_with_tax / $acf_quantity;
                            }

                            echo wc_price($price_per_product_with_tax);
                            ?>
                        </span>
                    </div>

                        <div class="price">
                        <div class="landing-offer-price">
                                <span class="offer-price">
                                    <?php 
                                    _e('Total:', 'nutrisslim-suite'); 
                                    $calculated_price = wc_get_price_including_tax($product, ['price' => $quantities[$key]['price']]);
                                    echo wc_price($calculated_price); 
                                    ?>
                                </span>
                            </div>
                            <div class="price-old">
                                <span class="line-through">
                                    <?php echo wc_price($regular_price_wo_tax * $num); ?>
                                </span>
                            </div>
                        </div>
                        <div class="additional-button">
                            <button class="btn btn-primary" type="button" disabled>
                                <?php _e('Choose Package', 'nutrisslim-suite'); ?>
                            </button>
                        </div>
                        <div class="savings">
                            <span class="savings-text">
                                <?php _e('You save', 'nutrisslim-suite'); ?>
                                <?php echo wc_price($regular_price_wo_tax * $num - $quantities[$key]['price']); ?>
                            </span>
                        </div>
                    </div>

                    <!-- For the first item, show shipping cost if not free -->
                    <?php if ($key == 0 && !$freeshipping) { ?>
                        <div class="shipping-cost">
                            <span><b><?php _e('Koszty wysyłki 4,90', 'nutrisslim-suite'); ?></b></span>
                        </div>
                    <?php } ?>

                    <!-- Handle Free Shipping for other items -->
                    <?php if ($key > 0 && $freeshipping) { ?>
                        <div class="offer_shipping ponudbab">
                            <div class="icon ponudbaikona">
                                <img src="<?php echo esc_url(plugins_url('assets/cart_free_delivery_ico.svg', dirname(__FILE__, 2))); ?>"
                                     alt="<?php _e('express-delivery', 'nutrisslim-suite'); ?>">
                            </div>
                            <?php _e('Ingyenes szállítás', 'nutrisslim-suite'); ?>
                        </div>
                    <?php } ?>

                    <!-- Handle Free Gift display -->
                    <?php if ($has_gift) { ?>
                        <div class="offer_shipping ponudbab">
                            <div class="icon ponudbaikona">
                                <img src="<?php echo esc_url(plugins_url('assets/darilo_ico.svg', dirname(__FILE__, 2))); ?>"
                                     alt="<?php _e('free_gift', 'nutrisslim-suite'); ?>">
                            </div>
                            <?php _e('Darmowy prezent', 'nutrisslim-suite'); ?>
                        </div>
                    <?php } ?>
                </div>
            </label>
        </div>
    <?php } ?>
</div>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const offerBlocksB = document.querySelectorAll("#upsell-block.style-b .offer");

    offerBlocksB.forEach(block => {
        block.addEventListener("click", function() {
            // Uncheck all checkboxes and remove active class from all blocks
            offerBlocksB.forEach(b => {
                b.querySelector("input[type='checkbox']").checked = false;
                b.classList.remove("active");
            });
            // Check the clicked checkbox and add active class to the clicked block
            const checkbox = this.querySelector("input[type='checkbox']");
            checkbox.checked = true;
            this.classList.add("active");
        });
    });
});
</script>
<?php endif; ?>

<!-- ---------------------- UPSell Block C (Same as A but with Price-per-product) ---------------------- -->
<?php if ($upsell_block_option == 'upsell-block-c') : ?>
    <style>

    .regularofferc {
        color:grey!important;
        font-size: 16px!important;;
    }
    .totalofferc {
        font-size: 20px!important;;
    }
    .cenanakos {
        margin-bottom:0px!important; 
    }
    .cenanakos bdi {
    font-size: 30px!important; 
    }

    .cenanakos bdi span.woocommerce-Price-currencySymbol {
    font-size: 30px!important;
    margin-left: 2px!important; /* Adds spacing between price and currency */
    }
    .price-per-product-text {
    font-size: 14px!important; /* Style everything within */
    }
    .price.cenaoffer {
    padding-top: 38px!important;
    }
    #upsell-block .offer div.slikadesnooffer img {
        height:100px!important;
        width:100px!important;
    }
    .karticaupsell {
        margin-top:17px!important; 
    }
    .offer-right.slikadesnooffer {
        margin-top:0px!important;
    }

</style>
<div id="upsell-block" class="style-c<?php echo $upsell_block_option == 'upsell-block-c' ? '' : ' d-none'; ?>"
     <?php echo $display_style; ?>>
    <?php
    // We can reuse the same logic from Block A, but add the "price per product" under the image
    $custom_titles = array(
        1 => !empty($package_1_text) ? $package_1_text : __('1+1 FREE', 'nutrisslim-suite'),
        2 => !empty($package_2_text) ? $package_2_text : __('2+2 FREE ', 'nutrisslim-suite') . '<span style="font-weight: 300;">' . __('+ save extra 5%', 'nutrisslim-suite') . '</span>',

        3 => !empty($package_3_text) ? $package_3_text : __('3+3 FREE ', 'nutrisslim-suite') . '<span style="font-weight: 300;">' . __('+ save extra 10%', 'nutrisslim-suite') . '</span>',
    );

    $custom_titles2 = array(
        1 => __('Get 2 products', 'nutrisslim-suite'),
        2 => __('Get 4 products', 'nutrisslim-suite'),
        3 => __('Get 6 products', 'nutrisslim-suite'),
    );

    foreach ($quantities as $key => $item) {
        $num = $key + 1;

        if ($key == 1) {
            echo '<div class="text-center cta-notice mobile"><h3>' . __('Vásárolj többet, spórolj többet', 'nutrisslim-suite') . '</h3></div>';
        }

        if ($quantities[$key]['price']) {
            $freeshipping = $quantities[$key]['price'] >= $free_shipping_threshold;
            // If consumption period is set
            if ($consumption_period) {
                $duration = $num * $consumption_period;
            }
            ?>
            <div class="offer custom_offer offer<?php echo $key . ' ' . ($key == 0 ? 'active' : ''); ?>"
                 data-key="<?php echo $key; ?>"
                 data-price="<?php echo $quantities[$key]['price']; ?>">
                <div class="offer-container">
                    <div class="text-center <?php echo ($key == 0 ? 'active' : ''); ?>">
                        <?php
                        $title  = isset($custom_titles[$num]) && !empty($custom_titles[$num]) ? $custom_titles[$num] : '';
                        $title2 = isset($custom_titles2[$num]) ? $custom_titles2[$num] : '';
                        if (!empty($title)) {
                            echo '<h3>' . wp_kses_post($title) . '</h3>';
                        }
                        ?>
                    </div>
                    <div class="bottom">
                        <div class="upsell-card karticaupsell">
                            <div class="offer-right slikadesnooffer">
                                <?php
                                // Product image
                                $image_src_array = $item['image']
                                    ? wp_get_attachment_image_src($item['image'], 'medium')
                                    : wp_get_attachment_image_src($attachment_id, 'medium');
                                $imgsrc = $image_src_array[0];
                                ?>
                                <img width="400" height="500" src="<?php echo esc_url($imgsrc); ?>"
                                     alt="<?php echo esc_attr__('Product Image', 'nutrisslim-suite'); ?>"/>
                                <div class="circle-red">
                                    <div class="circle-text">
                                        <span class="circle-txt-large">-<?php echo $quantities[$key]['percent']; ?>%</span>
                                    </div>
                                </div>

                                <!-- NEW: Price per product (taken from Upsell Block B logic) -->
                                <?php
                                // Fetch ACF data (adjust the field key if needed)
                                $bundle_data  = get_field('field_663a89fffa70d', $productID);
                                $acf_quantity = 0; // Initialize as 0 to start summing

                                if (is_array($bundle_data)) {
                                    foreach ($bundle_data as $row) {
                                        // Check if 'quantity' exists and is not empty, then sum it
                                        if (!empty($row['quantity'])) {
                                            $acf_quantity += floatval($row['quantity']);
                                        }
                                    }
                                }

                                // Multiply by how many items in the offer
                                $acf_quantity *= $num;

                                // Calculate the price per product
                                $price_per_product_with_tax = 0;
                                if ($acf_quantity > 0) {
                                    // Calculate the price per product with tax
                                    $price_with_tax = wc_get_price_including_tax($product, ['price' => $quantities[$key]['price']]);
                                    $price_per_product_with_tax = $price_with_tax / $acf_quantity;
                                }
                                ?>
                                <div class="price-per-product">
                                    <span class="price-per-product-text">
                                        <p class="offer-price ee cenanakos"><?php echo wc_price($price_per_product_with_tax); ?></p>
                                        <?php _e('/per product', 'nutrisslim-suite'); ?>
                                    </span>
                                </div>

                            </div> <!-- end .offer-right -->

                            <div class="offer-left">
                                <div class="cta">
                                    <div class="checkbox">
                                        <?php 
                                        echo '<input 
                                            data-id="' . esc_attr($productID) . '" 
                                            data-lid="' . esc_attr(get_the_ID()) . '" 
                                            data-qty="' . esc_attr($num) . '" 
                                            data-price="' . esc_attr(round($quantities[$key]['price'], 2)) . '" 
                                            id="checked0_c_' . esc_attr($num) . '" 
                                            type="checkbox" ' . ($key == 0 ? 'checked' : '') .
                                            (isset($quantities[$key]['gift'][0]) ? ' data-gift="' . esc_attr($quantities[$key]['gift'][0]) . '"' : '') .
                                            (!empty($quantities[$key]['free_shipping']) ? ' data-free-shipping="1"' : '') .
                                            '>';
                                        ?>
                                        <label for="checked0_c_<?php echo esc_attr($num); ?>">
                                            <?php if (!empty($title2)) {
                                                echo '<h3>' . esc_html($title2) . '</h3>';
                                            } ?>
                                            <?php 
                                            if ($consumption_period && !empty($duration)) {
                                                echo '<p style="font-size: smaller; padding-left: 20px; font-weight:300; padding-top: 10px"> na ' . esc_html($duration) . ' dni</p>';
                                            }
                                            ?>
                                        </label>
                                    </div>
                                    <div class="price cenaoffer">
                                        <div class="price-old price-small">
                                            <span class="line-through precrtano regularofferc">
                                                <?php
                                                echo sprintf(
                                                    __('Normál ár. %s', 'nutrisslim-suite'),
                                                    wc_price($regular_price * $num)
                                                );
                                                ?>
                                            </span>
                                        </div>
                                        <div class="price-large">
                                            <span class="offerc-price eec totalofferc">
                                                <?php 
                                                echo __('Total: ', 'nutrisslim-suite'); // Add "Total:" before the price
                                                $calculated_price = wc_get_price_including_tax($product, [
                                                    'price' => $quantities[$key]['price']
                                                ]);
                                                echo wc_price($calculated_price);
                                                ?>
                                            </span>
                                        </div>

                                    </div>
                                </div>
                                <?php if (!$quantities[$key]['free_shipping'] && !$freeshipping) { ?>
                                    <div class="shipping-cost">
                                        <span><strong></strong></span>
                                    </div>
                                <?php } ?>
                            </div> <!-- end .offer-left -->
                        </div> <!-- end .upsell-card -->

                        <!-- Free Shipping -->
                        <?php if ($quantities[$key]['free_shipping'] || $freeshipping) { ?>
                            <div class="offer_shipping">
                                <div class="plus">
                                    <img src="<?php echo esc_url(plugins_url('assets/checkPlus.svg', dirname(__FILE__, 2))); ?>"
                                         alt="<?php _e('plus', 'nutrisslim-suite'); ?>">
                                </div>
                                <div class="icon">
                                    <img src="<?php echo esc_url(plugins_url('assets/cart_free_delivery_ico.svg', dirname(__FILE__, 2))); ?>"
                                         alt="<?php _e('express-delivery', 'nutrisslim-suite'); ?>">
                                </div>
                                <?php _e('Ingyenes szállítás', 'nutrisslim-suite'); ?>
                            </div>
                        <?php } ?>

                        <!-- Free Gift -->
                        <?php if (!empty($quantities[$key]['gift']) && isset($quantities[$key]['gift'][0])) { ?>
                            <div class="offer_shipping">
                                <div class="plus">
                                    <img src="<?php echo esc_url(plugins_url('assets/checkPlus.svg', dirname(__FILE__, 2))); ?>"
                                         alt="<?php _e('plus', 'nutrisslim-suite'); ?>">
                                </div>
                                <div class="icon">
                                    <img src="<?php echo esc_url(plugins_url('assets/darilo_ico.svg', dirname(__FILE__, 2))); ?>"
                                         alt="<?php _e('free_gift', 'nutrisslim-suite'); ?>">
                                </div>
                                <?php _e('Ingyenes ajándék', 'nutrisslim-suite'); ?>
                            </div>
                        <?php } ?>


                        <?php if ($key == 1 || $key == 2) { // Only for offer 2 and 3 ?>
                    <div class="offer_shipping">
                        <div class="plus">
                            <img src="<?php echo esc_url(plugins_url('assets/checkPlus.svg', dirname(__FILE__, 2))); ?>"
                                alt="<?php _e('plus', 'nutrisslim-suite'); ?>">
                        </div>
                        <div class="icon">
                            <img src="<?php echo esc_url(plugins_url('assets/darilo_ico.svg', dirname(__FILE__, 2))); ?>"
                                alt="<?php _e('savings', 'nutrisslim-suite'); ?>">
                        </div>
                        <span>
                            <?php
                            // Calculate the savings
                            $regular_price_total = $regular_price_wo_tax * $num; // Total regular price
                            $savings = $regular_price_total - $quantities[$key]['price']; // Savings calculation

                            // Display the savings
                            echo __('You Save', 'nutrisslim-suite') . ': ' . wc_price($savings);
                            ?>
                        </span>
                    </div>
                <?php } ?>
                    </div> <!-- end .bottom -->
                </div> <!-- end .offer-container -->
            </div> <!-- end .offer -->
            <?php
        }
    }
    ?>
</div>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const offerBlocksC = document.querySelectorAll("#upsell-block.style-c .offer");

    offerBlocksC.forEach(block => {
        block.addEventListener("click", function() {
            // Uncheck all checkboxes and remove active class from all blocks
            offerBlocksC.forEach(b => {
                b.classList.remove("active");
                b.querySelector(".text-center").classList.remove("active");
                b.querySelector("input[type='checkbox']").checked = false;
            });

            // Add active class to the clicked block and check its checkbox
            this.classList.add("active");
            this.querySelector(".text-center").classList.add("active");
            this.querySelector("input[type='checkbox']").checked = true;

            // If you want to update some "total" in real time, do it here
            // const selectedPrice = this.querySelector('.offer-price').textContent;
            // document.querySelector(".order-review-price").textContent = selectedPrice;
        });
    });
});
</script>
<?php endif; ?>


<div id="offer-loader" class="">
    <div class="preloader-content">
        <h2>Bijwerking van het aanbod ...</h2>
        <img class="gray-gif"
             src="<?php echo esc_url(plugins_url('assets/preloader-greybg.gif', dirname(__FILE__, 2))); ?>"
             alt="Preloader" />
    </div>
</div>
<?php
    }
}
