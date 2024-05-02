<?php
add_action('admin_menu', 'crypto_tracker_add_admin_menu');
add_action('admin_init', 'crypto_tracker_settings_init');


function crypto_tracker_add_admin_menu()
{

    add_options_page('Crypto Tracker', 'Crypto Tracker', 'manage_options', 'crypto_tracker', 'crypto_tracker_options_page');
}


function crypto_tracker_settings_init()
{

    register_setting('pluginPage', 'crypto_tracker_settings');


    add_settings_section(
        'crypto_tracker_pluginPage_section',
        __('', 'crypto-tracker'),
        'crypto_tracker_settings_section_callback',
        'pluginPage'
    );

    add_settings_field(
        'crypto_tracker_api_key',
        __('CoinMarketCap API key', 'crypto-tracker'),
        'crypto_tracker_api_key_render',
        'pluginPage',
        'crypto_tracker_pluginPage_section'
    );
}


function crypto_tracker_api_key_render()
{

    $options = get_option('crypto_tracker_settings');
?>
    <input style="width: 300px;" type='text' name='crypto_tracker_settings[crypto_tracker_api_key]' value='<?php echo $options['crypto_tracker_api_key']; ?>'>
<?php

}


function crypto_tracker_settings_section_callback()
{

    if (get_option('crypto_tracker_settings') === false) // Nothing yet saved
        update_option('crypto_tracker_settings', ['crypto_tracker_api_key' => ' ']);
}


function crypto_tracker_options_page()
{

?>
    <form action='options.php' method='post'>

        <h2>Crypto Tracker Settings</h2>

        <?php
        settings_fields('pluginPage');
        do_settings_sections('pluginPage');
        submit_button();
        ?>

    </form>
<?php

}
