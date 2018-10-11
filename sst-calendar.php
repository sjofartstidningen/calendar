<?php
/**
 * Plugin Name:  Calendar
 * Plugin URI:   https://www.sjofartstidningen.se
 * Description:  A calendar plugin
 * Version:      1.0.0
 * Author:       Adam Bergman
 * Author URI:   https://github.com/adambrgmn
 * License:      MIT
 * Text Domain:  sst-calendar
 * Domain Path: /languages
 *
 * @package sst-calendar
 * @since 1.0.0
 */

namespace SST\Calendar;

if (!defined('WPINC')) {
    die;
}

require_once __DIR__ . '/vendor/autoload.php';

define('SST_CALENDAR_VERSION', '1.0.0');

class Calendar
{
    protected static $postType = 'event';
    protected static $metaPrefix = '_event_';

    protected $version;
    protected $pluginName;
    protected $settings;
    protected $loader;

    public function __construct()
    {
        $this->version = SST_CALENDAR_VERSION;
        $this->pluginName = 'sst-calendar';
        $this->settings = [
            'pluginRoot' => \plugin_dir_path(__FILE__),
            'pluginUrl' => \plugins_url('/', __FILE__),
        ];

        $this->loadDependecies();
        $this->registerGlobalHooks();
        $this->registerAdminHooks();
        $this->registerPublicHooks();
    }

    private function loadDependecies()
    {
        $root = $this->settings['packageRoot'];

        require_once $root . 'includes/class-i18n.php';
        require_once $root . 'includes/class-loader.php';
        require_once $root . 'includes/class-post-type.php';
        require_once $root . 'includes/class-query.php';

        $this->loader = new Loader();
    }

    private function registerGlobalHooks()
    {
        $i18n = new I18N();
        $this->loader->addAction('plugins_loaded', $i18n, 'loadTextdomain');
    }

    private function registerAdminHooks()
    {
        $postType = new PostType($this->pluginName, self::$postType, self::$metaPrefix);
        $this->loader->addAction('init', $postType, 'registerPostType');
        $this->loader->addAction('cmb2_admin_init', $postType, 'registerMetaboxes');
        $this->loader->addFilter('manage_event_posts_columns', $postType, 'manageEventsColumns');
        $this->loader->addAction('manage_event_posts_custom_column', $postType, 'renderEventsColumns', 10, 2);
    }

    private function registerPublicHooks()
    {
        $query = new Query(self::$postType, self::$metaPrefix);
        $this->loader->addAction('pre_get_posts', $query, 'modifyMainQuery');
    }

    public function run()
    {
        $this->loader->run();
    }
}

function runCalendar()
{
    $instance = new Calendar();
    $instance->run();
}

runCalendar();
