<?php

/*
* Plugin Name: Simple Contact Form
* Description: a simple contact Form from Mr Digital.
* Version: 1.10.11
* Text Domain: Optional Domain Plugin
* Author: Muhammad Arsalan
*
*/


if(!defined('ABSPATH'))
{
die ('You cannot be here');
}

if(!class_exists('ContactPlugin'))
{
class ContactPlugin {
public function __construct()
{
define('MY_PLUGIN_PATH', plugin_dir_path( __FILE__));
require_once(MY_PLUGIN_PATH.'/vendor/autoload.php');
}
public function initialize()
{

    include_once MY_PLUGIN_PATH .'includes/options-page.php';
    include_once MY_PLUGIN_PATH .'includes/contact-form.php';
}
}

$contactPlugin = new ContactPlugin;
$contactPlugin->initialize();
}