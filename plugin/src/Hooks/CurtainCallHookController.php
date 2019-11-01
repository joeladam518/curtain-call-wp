<?php

namespace CurtainCallWP\Controllers;

/**
 * Class CurtainCallController
 * @package CurtainCallWP\Controllers
 */
class CurtainCallHookController
{
    protected $plugin_name;
    protected $plugin_version;
    
    protected $assets_url;
    protected $assets_path;
    
    public function __construct()
    {
        $this->plugin_name    = CCWP_PLUGIN_NAME;
        $this->plugin_version = CCWP_PLUGIN_VERSION;
    }
}

