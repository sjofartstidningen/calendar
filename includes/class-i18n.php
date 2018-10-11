<?php
/**
 * Register all actions and filters for the plugin
 *
 * @since 1.0.0
 * @package sst-calendar
 * @subpackage sst-calendar/includes
 */

namespace SST\Calendar;

class I18n
{
    public function loadTextdomain()
    {
        $langPath = basename(dirname(dirname(__FILE__))) . '/languages';
        $loaded = load_plugin_textdomain('sst-calendar', false, $langPath);
        return $loaded;
    }
}
