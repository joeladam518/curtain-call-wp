<?php

namespace CurtainCallWP\Controllers;

class CurtainCallController
{
    protected $plugin_name;
    protected $plugin_version;
    protected $assets_dir;
    
    public function __construct(string $plugin_name, string $plugin_version)
    {
        $this->plugin_name = $plugin_name;
        $this->plugin_version = $plugin_version;
    }
}

