<?php

function add_elementor_widget_categories($elements_manager)
{

    $elements_manager->add_category(
        'first-category',
        [
            'title' => esc_html__('Crypto Tracker', 'crypto-tracker'),
            'icon' => 'fa fa-plug',
        ]
    );
}
add_action('elementor/elements/categories_registered', 'add_elementor_widget_categories');
