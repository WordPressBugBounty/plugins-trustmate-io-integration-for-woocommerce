<?php

function trustmate_render_widget_alpaca()
{
    $language_param = class_exists('SitePress') ? '?language='.ICL_LANGUAGE_CODE : '';

    if (get_option('trustmate_widget_alpaca')) {
        echo "<div id='tm-widget-alpaca'></div>";
        $script_src = sprintf(
            "%s/platforms/widget/alpaca/script/%s{$language_param}",
            trustmate_get_api_base_url(),
            trustmate_get_current_uuid()
        );
        wp_enqueue_script('trustmate-alpaca', $script_src);
    }
}

function trustmate_render_widget_badger2()
{
    global $product;

    $language_param = class_exists('SitePress') ? '&language='.ICL_LANGUAGE_CODE : '';

    if (is_product() && get_option('trustmate_widget_badger2')) {
        echo "<div id='tm-widget-badger2'></div>";
        $script_src = sprintf(
            "%s/platforms/widget/badger2/script/%s?%s=%s{$language_param}",
            trustmate_get_api_base_url(),
            trustmate_get_current_uuid(),
            $product->get_type() === 'variable' ? 'group' : 'product',
            $product->get_id()
        );
        wp_enqueue_script('trustmate-badger2', $script_src);
    }
}

function trustmate_render_widget_muskrat2()
{
    $language_param = class_exists('SitePress') ? '?language='.ICL_LANGUAGE_CODE : '';

    if (get_option('trustmate_widget_muskrat2')) {
        echo "<div id='tm-widget-muskrat2'></div>";
        $script_src = sprintf(
            "%s/platforms/widget/muskrat2/script/%s{$language_param}",
            trustmate_get_api_base_url(),
            trustmate_get_current_uuid()
        );
        wp_enqueue_script('trustmate-muskrat2', $script_src);
    }
}

function trustmate_render_widget_bee()
{
    $language_param = class_exists('SitePress') ? '?language='.ICL_LANGUAGE_CODE : '';

    if (get_option('trustmate_widget_bee')) {
        echo "<div id='tm-widget-bee'></div>";
        $script_src = sprintf(
            "%s/platforms/widget/bee/script/%s{$language_param}",
            trustmate_get_api_base_url(),
            trustmate_get_current_uuid()
        );
        wp_enqueue_script('trustmate-bee', $script_src);
    }
}

function trustmate_render_widget_lemur()
{
    $language_param = class_exists('SitePress') ? '?language='.ICL_LANGUAGE_CODE : '';

    if (get_option('trustmate_widget_lemur')) {
        echo "<div id='tm-widget-lemur'></div>";
        $script_src = sprintf(
            "%s/platforms/widget/lemur/script/%s{$language_param}",
            trustmate_get_api_base_url(),
            trustmate_get_current_uuid()
        );
        wp_enqueue_script('trustmate-lemur', $script_src);
    }
}

// Uses option for v1 but still renders v2
function trustmate_render_widget_chupacabra()
{
    $language_param = class_exists('SitePress') ? '?language='.ICL_LANGUAGE_CODE : '';

    if (get_option('trustmate_widget_chupacabra')) {
        echo "<div id='tm-widget-chupacabra2'></div>";
        $script_src = sprintf(
            "%s/platforms/widget/chupacabra2/script/%s{$language_param}",
            trustmate_get_api_base_url(),
            trustmate_get_current_uuid()
        );
        wp_enqueue_script('trustmate-chupacabra', $script_src);
    }
}

function trustmate_render_widget_ferret2()
{
    $language_param = class_exists('SitePress') ? '?language='.ICL_LANGUAGE_CODE : '';

    if (get_option('trustmate_widget_ferret2')) {
        echo "<div id='tm-widget-ferret2'></div>";
        $script_src = sprintf(
            "%s/platforms/widget/ferret2/script/%s{$language_param}",
            trustmate_get_api_base_url(),
            trustmate_get_current_uuid()
        );
        wp_enqueue_script('trustmate-ferret2', $script_src);
    }
}

function trustmate_render_widget_product_ferret2()
{
    global $product;

    $language_param = class_exists('SitePress') ? '&language='.ICL_LANGUAGE_CODE : '';

    if (is_product() && get_option('trustmate_widget_product_ferret2')) {
        echo "<div id='tm-widget-productFerret2'></div>";
        $script_src = sprintf(
            "%s/platforms/widget/productFerret2/script/%s?%s=%s{$language_param}",
            trustmate_get_api_base_url(),
            trustmate_get_current_uuid(),
            $product->get_type() === 'variable' ? 'group' : 'product',
            $product->get_id()
        );
        wp_enqueue_script('trustmate-product-ferret2', $script_src);
    }
}

function trustmate_render_widget_hydra()
{
    global $product;

    $language_param = class_exists('SitePress') ? '&language='.ICL_LANGUAGE_CODE : '';

    if (is_product() && get_option('trustmate_widget_hydra')) {
        echo "<div id='tm-widget-hydra'></div>";
        $script_src = sprintf(
            "%s/platforms/widget/hydra/script/%s?%s=%s{$language_param}",
            trustmate_get_api_base_url(),
            trustmate_get_current_uuid(),
            $product->get_type() === 'variable' ? 'group' : 'product',
            $product->get_id()
        );
        wp_enqueue_script('trustmate-hydra', $script_src);
    }
}

// Uses option for v1 but still renders v2
function trustmate_render_widget_owl()
{
    $language_param = class_exists('SitePress') ? '?language='.ICL_LANGUAGE_CODE : '';

    if (get_option('trustmate_widget_owl')) {
        echo "<div id='tm-widget-owl2'></div>";
        $script_src = sprintf(
            "%s/platforms/widget/owl2/script/%s{$language_param}",
            trustmate_get_api_base_url(),
            trustmate_get_current_uuid()
        );
        wp_enqueue_script('trustmate-owl', $script_src);
    }
}

function trustmate_insert_multihornet_wrappers() {
    global $product;

    if (get_option('trustmate_widget_multihornet') && $product->get_type() == 'simple') {
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
                data-target='.price'
                src='%s/platforms/widget/multihornet/script/%s'>
            </script>",
            trustmate_get_api_base_url(),
            trustmate_get_current_uuid(),
        );
    }
}

function trustmate_render_widget_hornet()
{
    global $product;

    $language_param = class_exists('SitePress') ? '&language='.ICL_LANGUAGE_CODE : '';

    if (is_product() && get_option('trustmate_widget_hornet')) {
        echo "<div id='tm-widget-hornet'></div>";
        $script_src = sprintf("%s/platforms/%s/widget/hornet/script?%s=%s{$language_param}",
            trustmate_get_api_base_url(),
            trustmate_get_current_uuid(),
            $product->get_type() === 'variable' ? 'group' : 'product',
            $product->get_id()
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
        wp_enqueue_script('trustmate-hornet', $script_src);
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