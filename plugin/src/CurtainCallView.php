<?php

namespace CurtainCallWP;

class View
{
    /**
     * The file path for the views dir
     * @var string
     */
    protected $views_dir_path;
    
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
        $this->views_dir_path = self::dirPath();
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
            echo $this->getTemplatePath() . '<br>' . PHP_EOL;
            throw new \Exception('View template is not found.');
            //return '';
        }
        
        if (count($this->data) > 0) {
            if (!$this->validateDataArray()) {
                return '';
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
        return $this->views_dir_path . $this->file_path;
    }
    
    private function validateDataArray(): bool
    {
        if(array_keys($this->data) !== range(0, count($this->data) - 1)) {
            return true;
        } else {
            return false;
        }
    }
}