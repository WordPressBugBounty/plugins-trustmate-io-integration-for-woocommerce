<?php

define('TRUSTMATE_INFO_TRANSIENT', 'trustmate_account_info');
define('TRUSTMATE_INFO_TTL', 12 * HOUR_IN_SECONDS);
define('TRUSTMATE_INFO_NEGATIVE_TTL', HOUR_IN_SECONDS);

define('TRUSTMATE_VERSION_TRANSIENT', 'trustmate_latest_version');
define('TRUSTMATE_VERSION_TTL', 24 * HOUR_IN_SECONDS);
define('TRUSTMATE_VERSION_NEGATIVE_TTL', 6 * HOUR_IN_SECONDS);

define('TRUSTMATE_INFO_NEGATIVE_MARKER', '__trustmate_negative__');

$trustmate_widget_required_service = array(
    'lemur' => 'WIDGET_LEMUR',
    'alpaca' => 'WIDGET',
    'badger2' => 'WIDGET',
    'muskrat2' => 'WIDGET',
    'bee' => 'WIDGET',
    'chupacabra' => 'WIDGET',
    'ferret2' => 'WIDGET',
    'product_ferret2' => 'WIDGET',
    'hydra' => 'WIDGET',
    'owl' => 'WIDGET',
    'multihornet' => 'WIDGET',
    'hornet' => 'WIDGET',
);

function trustmate_account_info_get()
{
    $cached = get_transient(TRUSTMATE_INFO_TRANSIENT);
    if ($cached !== false) {
        return $cached === TRUSTMATE_INFO_NEGATIVE_MARKER ? null : $cached;
    }

    $uuid = get_option('trustmate_account_uuid');
    $key = get_option('trustmate_installation_key');
    if (!$uuid || !$key) {
        set_transient(TRUSTMATE_INFO_TRANSIENT, TRUSTMATE_INFO_NEGATIVE_MARKER, TRUSTMATE_INFO_NEGATIVE_TTL);
        return null;
    }

    $url = trustmate_get_api_base_url() . '/platforms/api/account/' . urlencode($uuid) . '/info';
    $response = wp_remote_get($url, array(
        'timeout' => 5,
        'headers' => array(
            'X-TRUSTMATE-INSTALLATION-KEY' => $key,
            'X-TRUSTMATE-ACCOUNT-UUID' => $uuid,
        ),
    ));

    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        set_transient(TRUSTMATE_INFO_TRANSIENT, TRUSTMATE_INFO_NEGATIVE_MARKER, TRUSTMATE_INFO_NEGATIVE_TTL);
        return null;
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);
    if (!is_array($data) || !isset($data['account_status'])) {
        set_transient(TRUSTMATE_INFO_TRANSIENT, TRUSTMATE_INFO_NEGATIVE_MARKER, TRUSTMATE_INFO_NEGATIVE_TTL);
        return null;
    }

    set_transient(TRUSTMATE_INFO_TRANSIENT, $data, TRUSTMATE_INFO_TTL);
    return $data;
}

function trustmate_account_info_clear_cache()
{
    delete_transient(TRUSTMATE_INFO_TRANSIENT);
}

add_action('update_option_trustmate_account_uuid', 'trustmate_account_info_clear_cache');
add_action('update_option_trustmate_installation_key', 'trustmate_account_info_clear_cache');
add_action('add_option_trustmate_account_uuid', 'trustmate_account_info_clear_cache');
add_action('add_option_trustmate_installation_key', 'trustmate_account_info_clear_cache');

add_action('admin_init', function () {
    if (isset($_GET['clear_tm_cache']) && $_GET['clear_tm_cache'] === '1' && current_user_can('manage_options')) {
        trustmate_account_info_clear_cache();
        delete_transient(TRUSTMATE_VERSION_TRANSIENT);
    }
});

function trustmate_widget_is_available($widget_name)
{
    global $trustmate_widget_required_service;

    $info = trustmate_account_info_get();
    if (!$info || !isset($info['services']) || !is_array($info['services'])) {
        return true;
    }

    if (!isset($trustmate_widget_required_service[$widget_name])) {
        return true;
    }

    return in_array($trustmate_widget_required_service[$widget_name], $info['services'], true);
}

function trustmate_subscription_has_any_unavailable_widget()
{
    global $trustmate_widget_required_service;

    $info = trustmate_account_info_get();
    if (!$info) {
        return false;
    }

    foreach (array_keys($trustmate_widget_required_service) as $widget) {
        if (!trustmate_widget_is_available($widget)) {
            return true;
        }
    }

    return false;
}

function trustmate_latest_plugin_version_get()
{
    $cached = get_transient(TRUSTMATE_VERSION_TRANSIENT);
    if ($cached !== false) {
        return $cached === TRUSTMATE_INFO_NEGATIVE_MARKER ? null : $cached;
    }

    $url = trustmate_get_api_base_url() . '/plugins/woo/version.json';
    $response = wp_remote_get($url, array('timeout' => 5));

    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        set_transient(TRUSTMATE_VERSION_TRANSIENT, TRUSTMATE_INFO_NEGATIVE_MARKER, TRUSTMATE_VERSION_NEGATIVE_TTL);
        return null;
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);
    if (!is_array($data) || empty($data['version']) || !is_string($data['version'])) {
        set_transient(TRUSTMATE_VERSION_TRANSIENT, TRUSTMATE_INFO_NEGATIVE_MARKER, TRUSTMATE_VERSION_NEGATIVE_TTL);
        return null;
    }

    set_transient(TRUSTMATE_VERSION_TRANSIENT, $data, TRUSTMATE_VERSION_TTL);
    return $data;
}

function trustmate_subscription_is_active()
{
    $info = trustmate_account_info_get();
    if (!$info || !isset($info['account_status'])) {
        return null;
    }

    $days = isset($info['days_to_expiration']) && $info['days_to_expiration'] !== '' ? (int) $info['days_to_expiration'] : null;
    $status = $info['account_status'];
    $is_active = ($status === 'paying');

    return $is_active && ($days === null || $days > 0);
}

function trustmate_render_subscription_info()
{
    $info = trustmate_account_info_get();
    if (!$info || !isset($info['account_status']) || empty($info['subscription_name'])) {
        return;
    }

    $days = isset($info['days_to_expiration']) && $info['days_to_expiration'] !== '' ? (int) $info['days_to_expiration'] : null;

    echo '<div class="tm-subscription-info">';
    echo '<strong>' . esc_html(sprintf(trustmate_tr('Subscription: %s'), $info['subscription_name'])) . '</strong>';
    if ($days !== null) {
        echo ' &mdash; ' . esc_html(sprintf(trustmate_tr('Days remaining: %d'), $days));
    }
    if ($days !== null && $days <= 14 && $days > 0) {
        echo ' <span class="tm-subscription-warning">(' . esc_html(trustmate_tr('Your subscription expires soon')) . ')</span>';
    }
    echo '</div>';
}

function trustmate_render_admin_notices()
{
    $info = trustmate_account_info_get();
    if ($info && isset($info['account_status'])) {
        $is_active = trustmate_subscription_is_active();

        if ($is_active === false) {
            $days = isset($info['days_to_expiration']) && $info['days_to_expiration'] !== '' ? (int) $info['days_to_expiration'] : null;

            $parts = array();
            if (!empty($info['subscription_name'])) {
                $parts[] = sprintf(trustmate_tr('Subscription: %s'), $info['subscription_name']);
            }
            if ($days !== null) {
                $parts[] = sprintf(trustmate_tr('Days remaining: %d'), $days);
            }
            $parts[] = trustmate_tr('Your subscription is inactive');

            echo '<div class="notice notice-error"><p>';
            echo esc_html(implode(' — ', $parts));
            echo '</p></div>';
        }
    }

    $latest = trustmate_latest_plugin_version_get();
    if ($latest && defined('TRUSTMATE_PLUGIN_VERSION') && version_compare($latest['version'], TRUSTMATE_PLUGIN_VERSION, '>')) {
        echo '<div class="notice notice-warning"><p>';
        echo esc_html(sprintf(trustmate_tr('New version of TrustMate plugin available: %s'), $latest['version']));
        if (!empty($latest['download_url'])) {
            echo ' <a href="' . esc_url($latest['download_url']) . '" target="_blank">' . esc_html(trustmate_tr('Download')) . '</a>';
        }
        if (!empty($latest['changelog'])) {
            echo '</p><p><em>' . esc_html($latest['changelog']) . '</em>';
        }
        echo '</p></div>';
    }
}
