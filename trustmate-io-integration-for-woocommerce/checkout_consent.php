<?php

function trustmate_render_consent_checkbox()
{
    if (!get_option('trustmate_require_review_consent')) {
        return;
    }

    woocommerce_form_field('trustmate_review_consent', array(
        'type'     => 'checkbox',
        'class'    => array('trustmate-review-consent'),
        'label'    => trustmate_tr('I agree to receive a review request'),
        'required' => false,
    ), '');
}
add_action('woocommerce_review_order_before_submit', 'trustmate_render_consent_checkbox');

function trustmate_save_consent($order, $data)
{
    if (!get_option('trustmate_require_review_consent')) {
        return;
    }

    $consent = (isset($_POST['trustmate_review_consent']) && $_POST['trustmate_review_consent']) ? 'yes' : 'no';
    $order->update_meta_data('_trustmate_review_consent', $consent);
}
add_action('woocommerce_checkout_create_order', 'trustmate_save_consent', 10, 2);

function trustmate_register_block_consent_field()
{
    if (!function_exists('woocommerce_register_additional_checkout_field')) {
        return;
    }
    if (!get_option('trustmate_require_review_consent')) {
        return;
    }

    if (!is_textdomain_loaded('trustmate')) {
        plugin_load_textdomain();
    }

    woocommerce_register_additional_checkout_field(array(
        'id'       => 'trustmate/review-consent',
        'label'    => trustmate_tr('I agree to receive a review request'),
        'location' => 'order',
        'type'     => 'checkbox',
        'required' => false,
    ));
}
add_action('woocommerce_init', 'trustmate_register_block_consent_field');
