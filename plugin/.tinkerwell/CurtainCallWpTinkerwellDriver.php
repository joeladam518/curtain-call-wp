<?php

use WordpressTinkerwellDriver;


class CurtainCallWpTinkerwellDriver extends WordpressTinkerwellDriver
{
    /**
     * Determine if the driver can be used with the selected project path.
     * You most likely want to check the existence of project / framework specific files.
     *
     * @param string $projectPath
     * @return  bool
     */
    public function canBootstrap($projectPath)
    {
        return (
            file_exists($projectPath . '/../../../wp-load.php') &&
            file_exists($projectPath . '/vendor/autoload.php')
        );
    }

    /**
     * Bootstrap the application so that any executed can access the application in your desired state.
     *
     * @param string $projectPath
     */
    public function bootstrap($projectPath)
    {
        require $projectPath . '/../../../wp-load.php';
        require $projectPath . '/vendor/autoload.php';
    }
}
