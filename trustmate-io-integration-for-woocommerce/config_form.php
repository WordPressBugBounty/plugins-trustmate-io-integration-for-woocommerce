<?php

function trustmate_render_config_form()
{
    wp_enqueue_style( 'config_form_style' );
    $regulations_consent_file = 'assets/Regulations-Consent-en.pdf';

    $locale = get_locale();
    if ($locale === 'pl' || $locale === 'pl_PL') {
        $regulations_consent_file = 'assets/Regulations-Consent.pdf';
    }

    ?>
    <div class="tm-page">
    <img src="<?php echo plugins_url('logo.png', __FILE__) ?>" style="width: 30px; float: left; margin-right: 5px;" alt="TrustMate logo"/>
    <h2 class="mt-3" style="line-height: 30px">
      TrustMate.io &dash; <?php echo trustmate_tr('settings') ?>
    </h2>

    <form method="post" action="options.php">
    <?php settings_fields('trustmate_basic_settings') ?>
    <div class="form-section tm-card">
      <label for="trustmate_account_uuid"><?php echo trustmate_tr('Your TrustMate user code (UUID from TrustMate panel, Integration section)') ?></label>
      <br><input type="text" id="trustmate_account_uuid" name="trustmate_account_uuid" value="<?php echo get_option('trustmate_account_uuid') ?>" required/>
    </div>

    <div class="form-section tm-card">
    <label for="trustmate_invitations_enabled">
        <?php echo trustmate_tr('Automatic review invitations') ?>
    </label>
    <select id="trustmate_invitations_enabled" name="trustmate_invitations_enabled">
        <option value="0" <?php selected('0', get_option('trustmate_invitations_enabled')) ?>>
            <?php echo trustmate_tr('Disabled') ?>
        </option>
        <option value="1" <?php selected('1', get_option('trustmate_invitations_enabled')) ?>>
            <?php echo trustmate_tr('After successful order') ?>
        </option>
        <option value="2" <?php selected('2', get_option('trustmate_invitations_enabled')) ?>>
            <?php echo trustmate_tr('After successful payment') ?>
        </option>
        <option value="3" <?php selected('3', get_option('trustmate_invitations_enabled')) ?>>
            <?php echo trustmate_tr('After marked as completed') ?>
        </option>
    </select>

    <br><br>
    <label for="trustmate_instant_review">
        <input id="trustmate_instant_review" name="trustmate_instant_review" value="1" type="checkbox" <?php if (get_option('trustmate_instant_review')): ?>checked<?php endif ?>>
        <?php echo trustmate_tr('Enable instant company review form on thank you page - do not use with After successful order status') ?>
    </label>
    <br><br>
    <p>
        <?php echo trustmate_tr('To quickly collect client reviews turn on automatic invitations') ?>.
        <?php echo trustmate_tr('While active, script sends invitations to your customers asking for review') ?>.
    </p>
    <p>
        <h5><?php echo trustmate_tr('In TrustMate panel you can') ?>:</h5>
        <ul>
            <li><?php echo trustmate_tr('change invitation sending delay') ?></li>
            <li><?php echo trustmate_tr('set how many times customer is reminded to write a review') ?></li>
            <li><?php echo trustmate_tr('adjust invitation colors to your brand') ?></li>
            <li><?php echo trustmate_tr('turn on and off product review invitations') ?></li>
            <li><?php echo trustmate_tr('and more!') ?></li>
        </ul>
    </p>
    <?php if (get_option('trustmate_base_url')): ?>
        <input type="hidden" name="trustmate_base_url" value="<?php echo get_option('trustmate_base_url') ?>"/>
    <?php endif ?>
    <?php submit_button(trustmate_tr('Save changes')) ?>
    <div class="notice notice-info">
        <p>
            <?php echo trustmate_tr('If you do not directly ask customer for consent to send invitation, we suggest to extend your regulations with content from') ?>

            <a href="<?php echo plugins_url($regulations_consent_file, __FILE__) ?>" target="_blank">
                <strong><?php echo trustmate_tr('this file') ?></strong>
            </a>.
        </p>
    </div>
    </div>
    </form>

    <?php include 'faq.php' ?>
    <a href="?page=trustmate&action=reset_plugin" title="Reset plugin" style="text-decoration: none"> </a>
    </div>
    <?php
}
