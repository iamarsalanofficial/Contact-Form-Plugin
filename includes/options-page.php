<?php
use Carbon_Fields\Container;
use Carbon_Fields\Field;
use Carbon_Fields\Carbon_Fields;

add_action('after_setup_theme', 'load_carbon_fields');
add_action('carbon_fields_register_fields', 'create_options_page');

function load_carbon_fields() 
{
    \Carbon_Fields\Carbon_Fields:: boot();
}
function create_options_page()
{
Container:: make('theme_options', __('Theme Options'))
->set_icon('dashicons-media-text')
->add_fields(array(
    Field::make('text', 'contact_plugin_recipients', __('Recipient Email'))->set_attribute('placeholder', 'eg. your@email.com'),
    Field::make('textarea', 'contact_plugin_message', __('Confirmation Message'))->set_attribute('placeholder', 'Enter confirmation message'),
));
}