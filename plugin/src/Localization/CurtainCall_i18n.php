<?php

namespace CurtainCallWP\Localization;

/**
 * Class CurtainCall_i18n
 * @package CurtainCallWP\Localization
 */
class CurtainCall_i18n 
{
    public function load_plugin_textdomain(): void
    {
        load_plugin_textdomain(
            CCWP_PLUGIN_NAME,
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }
}
