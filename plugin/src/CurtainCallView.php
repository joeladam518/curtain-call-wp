<?php

namespace CurtainCallWP;

/**
 * Class CurtainCallView
 * @package CurtainCallWP
 */
class CurtainCallView
{
    /**
     * The file path for the views dir
     * @var string
     */
    protected $views_path;
    
    /**
     * View file to include
     * @var string
     */
    protected $file_path;
    
    /**
     * View data
     * @var array
     */
    protected $data;
    
    /**
     * View constructor.
     * @param string $file_path
     * @param array  $data
     */
    public function __construct(string $file_path, array $data = [])
    {
        $this->views_path = self::dirPath();
        $this->file_path = $file_path;
        $this->data = $data;
    }
    
    public static function dirPath(): string
    {
        return ccwp_plugin_path('resources/views/');
    }
    
    public function render(): string
    {
        if (!file_exists($this->getTemplatePath())) {
            //TODO: Change this back to an empty string
            return '<h3 style="color:red;">Couldn\'t find view template path: "'. $this->getTemplatePath() .'"</h3>';
        }
        
        if (count($this->data) > 0) {
            if (!$this->validateDataArray()) {
                //TODO: Change this back to an empty string
                return '<h3 style="color:red;">View template didn\'t pass validation.</h3>';
            }
            extract($this->data);
        }
        
        ob_start();
        
        include($this->getTemplatePath());
        
        return ob_get_clean();
    }

    public function display(): void
    {
        echo $this->render();
    }
    
    public function getTemplatePath(): string
    {
        return $this->views_path . $this->file_path;
    }
    
    private function validateDataArray(): bool
    {
        // Checks to see if $this->data is an associative array;
        if(array_keys($this->data) !== range(0, count($this->data) - 1)) {
            return true;
        } else {
            return false;
        }
    }
}