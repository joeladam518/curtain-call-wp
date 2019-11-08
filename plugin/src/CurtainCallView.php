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
    
    public static function dirPath(string $path = ''): string
    {
        return ccwp_plugin_path('views/') . $path;
    }
    
    /**
     * Compile the php template/partial into an html string
     * @return string
     */
    protected function compile(): string
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
    
        include $this->getTemplatePath();
    
        return ob_get_clean();
    }
    
    /**
     * Either return the compiled html string or echo it out
     *
     * @param bool $return
     * @return string|void
     */
    public function render(bool $return = false)
    {
        if ($return) {
            return $this->compile();
        }
        
        echo $this->compile();
    }
    
    public function getTemplatePath(): string
    {
        return $this->views_path . $this->file_path;
    }
    
    protected function validateDataArray(): bool
    {
        // Checks to see if $this->data is an associative array;
        return array_keys($this->data) !== range(0, count($this->data) - 1);
    }
}