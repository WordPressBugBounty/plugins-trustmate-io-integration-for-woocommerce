<?php

// Variable and grouped products are registered on the platform only through their children,
// so the parent id exists there as a group id, never as a product local id.
function trustmate_is_product_group($product)
{
    return in_array($product->get_type(), ['variable', 'grouped'], true);
}

function trustmate_widget_query($product = null)
{
    $query = array();

    if ($product) {
        $key = trustmate_is_product_group($product) ? 'group' : 'product';
        $query[$key] = $product->get_id();
    }

    if ($language = trustmate_ssr_current_language()) {
        $query['language'] = $language;
    }

    return $query;
}

// Emits the widget container and enqueues the platform loader. With server-side rendering enabled
// the container is filled here, and the loader is told so twice: html-embedded=1 keeps it from
// shipping a second copy of the markup, data-tm-ssr keeps it from overwriting the markup it finds.
function trustmate_render_widget_container($widget_key, $type, $container_id, $handle, $query = array())
{
    $html = null;

    if (trustmate_ssr_enabled() && trustmate_ssr_supported($widget_key)) {
        $html = trustmate_ssr_get($type, $query);
    }

    if ($html === null) {
        echo "<div id='" . esc_attr($container_id) . "'></div>";
        wp_enqueue_script($handle, trustmate_ssr_script_url($type, $query));

        return;
    }

    // Printed unescaped on purpose: the fragment carries its own <style> and JSON-LD,
    // which wp_kses would strip. It comes from trustmate_get_widget_base_url() - the very
    // same origin we already load widget JS from.
    echo "<div id='" . esc_attr($container_id) . "' data-tm-ssr='1'>" . $html . "</div>";
    $query['html-embedded'] = 1;
    wp_enqueue_script($handle, trustmate_ssr_script_url($type, $query));
}

function trustmate_render_widget_alpaca()
{
    if (get_option('trustmate_widget_alpaca')) {
        trustmate_render_widget_container(
            'alpaca',
            'alpaca',
            'tm-widget-alpaca',
            'trustmate-alpaca',
            trustmate_widget_query()
        );
    }
}

function trustmate_render_widget_badger2()
{
    global $product;

    if (is_product() && $product && get_option('trustmate_widget_badger2')) {
        trustmate_render_widget_container(
            'badger2',
            'badger2',
            'tm-widget-badger2',
            'trustmate-badger2',
            trustmate_widget_query($product)
        );
    }
}

function trustmate_render_widget_muskrat2()
{
    if (get_option('trustmate_widget_muskrat2')) {
        trustmate_render_widget_container(
            'muskrat2',
            'muskrat2',
            'tm-widget-muskrat2',
            'trustmate-muskrat2',
            trustmate_widget_query()
        );
    }
}

function trustmate_render_widget_bee()
{
    if (get_option('trustmate_widget_bee')) {
        trustmate_render_widget_container(
            'bee',
            'bee',
            'tm-widget-bee',
            'trustmate-bee',
            trustmate_widget_query()
        );
    }
}

function trustmate_render_widget_lemur()
{
    if (get_option('trustmate_widget_lemur')) {
        trustmate_render_widget_container(
            'lemur',
            'lemur',
            'tm-widget-lemur',
            'trustmate-lemur',
            trustmate_widget_query()
        );
    }
}

// Uses option for v1 but still renders v2
function trustmate_render_widget_chupacabra()
{
    if (get_option('trustmate_widget_chupacabra')) {
        trustmate_render_widget_container(
            'chupacabra',
            'chupacabra2',
            'tm-widget-chupacabra2',
            'trustmate-chupacabra',
            trustmate_widget_query()
        );
    }
}

function trustmate_render_widget_ferret2()
{
    if (get_option('trustmate_widget_ferret2')) {
        trustmate_render_widget_container(
            'ferret2',
            'ferret2',
            'tm-widget-ferret2',
            'trustmate-ferret2',
            trustmate_widget_query()
        );
    }
}

function trustmate_render_widget_product_ferret2()
{
    global $product;

    if (is_product() && $product && get_option('trustmate_widget_product_ferret2')) {
        trustmate_render_widget_container(
            'product_ferret2',
            'productFerret2',
            'tm-widget-productFerret2',
            'trustmate-product-ferret2',
            trustmate_widget_query($product)
        );
    }
}

function trustmate_render_widget_hydra()
{
    global $product;

    if (is_product() && $product && get_option('trustmate_widget_hydra')) {
        trustmate_render_widget_container(
            'hydra',
            'hydra',
            'tm-widget-hydra',
            'trustmate-hydra',
            trustmate_widget_query($product)
        );
    }
}

// Uses option for v1 but still renders v2
function trustmate_render_widget_owl()
{
    if (get_option('trustmate_widget_owl')) {
        trustmate_render_widget_container(
            'owl',
            'owl2',
            'tm-widget-owl2',
            'trustmate-owl',
            trustmate_widget_query()
        );
    }
}

function trustmate_insert_multihornet_wrappers() {
    global $product;

    if (!get_option('trustmate_widget_multihornet') || !$product) {
        return;
    }

    if (trustmate_is_product_group($product)) {
        echo sprintf(
            "<div class='tm-widget-hornet-wrapper' data-group-id='%s'></div>",
            $product->get_id()
        );
    } elseif ($product->get_type() === 'simple') {
        echo sprintf(
            "<div class='tm-widget-hornet-wrapper' data-product-id='%s'></div>",
            $product->get_id()
        );
    }
}

function trustmate_render_widget_multihornet()
{
    if ((is_shop() || is_product_category()) && get_option('trustmate_widget_multihornet')) {
        echo sprintf(
            "<script
                defer
                data-parent='.product'
                data-id='data-product-id'
                data-group-id='data-group-id'
                data-target='.price'
                src='%s/platforms/widget/multihornet/script/%s'>
            </script>",
            trustmate_get_widget_base_url(),
            trustmate_get_current_uuid(),
        );
    }
}

function trustmate_render_widget_hornet()
{
    global $product;

    if (is_product() && $product && get_option('trustmate_widget_hornet')) {
        trustmate_render_widget_container(
            'hornet',
            'hornet',
            'tm-widget-hornet',
            'trustmate-hornet',
            trustmate_widget_query($product)
        );
        echo "<script>
                (() => {
                    function styleHornet() {
                        const hornetRef = document.getElementById('tm-widget-hornet');
                        hornetRef.style.marginBottom = '16px';
                    }
                    window.setTimeout(styleHornet, 10, true);
                })();
            </script>";
    }

    if (get_option('trustmate_widget_gorilla') || get_option('trustmate_widget_product_ferret') || get_option('trustmate_widget_product_ferret2') || get_option('trustmate_widget_hydra')) {
        echo sprintf(
        "<script>
            (() => {
                function scrollToWidget() {
                    const hornetRef = document.getElementById('tm-widget-hornet');
                    let widgetRef = document.getElementById('tm-widget-productFerret');
                    if (!widgetRef) {
                        widgetRef = document.getElementById('tm-widget-gorilla');
                    }
                    if (!widgetRef) {
                        widgetRef = document.getElementById('tm-hydra');
                    }
                    if (!widgetRef) {
                        widgetRef = document.getElementById('tm-ferret2');
                    }
                    if (widgetRef) {
                        hornetRef.addEventListener('click', () => {
                            hornetRef.click();
                            setTimeout(() => {
                                let widgetPosition = widgetRef?.getBoundingClientRect();
                                if (widgetPosition) {
                                    window.scrollTo({top: widgetPosition.top + window.scrollY - 250, behavior: 'smooth'});
                                }
                            }, 100);
                        });
                    }
                }
                window.setTimeout(scrollToWidget, 10, true);
            })();
        </script>"
    );
    }
}
