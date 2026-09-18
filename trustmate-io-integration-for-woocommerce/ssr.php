<?php

define('TRUSTMATE_SSR_TIMEOUT', 1.5);
define('TRUSTMATE_SSR_NEGATIVE_TTL', 5 * MINUTE_IN_SECONDS);
define('TRUSTMATE_SSR_KEY_PREFIX', 'trustmate_ssr_fail_');

// Widget keys (trustmate_widget_* option suffixes) eligible for server-side rendering.
// The platform type is passed separately by the caller - do not duplicate it here.
$trustmate_ssr_supported_widgets = array(
    'hydra',
);

function trustmate_ssr_enabled()
{
    if (!get_option('trustmate_widget_ssr')) {
        return false;
    }

    if (is_admin() || wp_doing_ajax() || is_feed()) {
        return false;
    }

    // A cart or checkout POST re-renders the product page; it is not worth an outbound request.
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] !== 'GET') {
        return false;
    }

    if (defined('REST_REQUEST') && REST_REQUEST) {
        return false;
    }

    if (defined('DOING_CRON') && DOING_CRON) {
        return false;
    }

    return (bool) trustmate_get_current_uuid();
}

function trustmate_ssr_supported($widget_key)
{
    global $trustmate_ssr_supported_widgets;

    return in_array($widget_key, $trustmate_ssr_supported_widgets, true);
}

function trustmate_ssr_current_language()
{
    if (class_exists('SitePress') && defined('ICL_LANGUAGE_CODE')) {
        return ICL_LANGUAGE_CODE;
    }

    if (function_exists('pll_current_language')) {
        $language = pll_current_language();
        if ($language) {
            return $language;
        }
    }

    return null;
}

// An unreachable platform is the same for every product, so it is remembered once per account -
// otherwise a catalogue of 20k products would pay the full timeout, and leave a transient behind,
// 20k times over.
function trustmate_ssr_outage_key($uuid)
{
    return TRUSTMATE_SSR_KEY_PREFIX . 'outage_' . md5($uuid);
}

// A platform that answers but refuses this particular widget or product is remembered per query,
// so one unknown product does not turn server-side rendering off for the whole shop.
function trustmate_ssr_failure_key($type, $query, $uuid)
{
    return TRUSTMATE_SSR_KEY_PREFIX . md5(
        wp_json_encode(
            array(
                'uuid' => $uuid,
                'type' => $type,
                'query' => $query,
            )
        )
    );
}

function trustmate_ssr_html_url($type, $query, $uuid)
{
    $url = sprintf(
        '%s/platforms/widget/%s/html/%s',
        trustmate_get_widget_base_url(),
        rawurlencode($type),
        rawurlencode($uuid)
    );

    return empty($query) ? $url : add_query_arg($query, $url);
}

function trustmate_ssr_script_url($type, $query)
{
    $url = sprintf(
        '%s/platforms/widget/%s/script/%s',
        trustmate_get_widget_base_url(),
        rawurlencode($type),
        rawurlencode(trustmate_get_current_uuid())
    );

    return empty($query) ? $url : add_query_arg($query, $url);
}

function trustmate_ssr_request($type, $query, $uuid)
{
    // Exactly one hop: /platforms/widget/{type}/html/{uuid} redirects to /widget/api/{token}/html.
    return wp_remote_get(
        trustmate_ssr_html_url($type, $query, $uuid),
        array(
            'timeout' => TRUSTMATE_SSR_TIMEOUT,
            'redirection' => 1,
        )
    );
}

function trustmate_ssr_is_outage($response)
{
    return is_wp_error($response) || wp_remote_retrieve_response_code($response) >= 500;
}

function trustmate_ssr_usable_html($response)
{
    if (wp_remote_retrieve_response_code($response) !== 200) {
        return null;
    }

    if (wp_remote_retrieve_header($response, 'x-trustmate-error')) {
        return null;
    }

    $body = trim(wp_remote_retrieve_body($response));

    return $body === '' ? null : $body;
}

// The rendered html is deliberately not stored locally: it runs to hundreds of kilobytes per
// product and language, which would bloat wp_options past any reasonable size. The platform
// serves it with Cache-Control: max-age=3600, public, so the CDN in front of it does the caching.
function trustmate_ssr_get($type, $query)
{
    $uuid = trustmate_get_current_uuid();
    $outage_key = trustmate_ssr_outage_key($uuid);
    $failure_key = trustmate_ssr_failure_key($type, $query, $uuid);

    if (get_transient($outage_key) || get_transient($failure_key)) {
        return null;
    }

    $response = trustmate_ssr_request($type, $query, $uuid);

    if (trustmate_ssr_is_outage($response)) {
        set_transient($outage_key, 1, TRUSTMATE_SSR_NEGATIVE_TTL);

        return null;
    }

    $html = trustmate_ssr_usable_html($response);

    if ($html === null) {
        set_transient($failure_key, 1, TRUSTMATE_SSR_NEGATIVE_TTL);

        return null;
    }

    return $html;
}
