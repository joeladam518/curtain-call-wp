<?php
declare(strict_types=1);

namespace CurtainCallWP;

use CurtainCallWP\Exceptions\ViewDataNotValidException;
use CurtainCallWP\Exceptions\ViewNotFoundException;
use Throwable;

/**
 * Class CurtainCallView
 * @package CurtainCallWP
 */
class CurtainCallView
{
    /**
     * View data
     * @var array
     */
    protected $data;
    
    /**
     * View file to include
     * @var string
     */
    protected $file_path;
    
    /**
     * View constructor.
     * @param string $file_path
     * @param array  $data
     */
    public function __construct(string $file_path, array $data = [])
    {
        $this->file_path = trim($file_path);
        $this->data = $data;
    }
    
    /**
     * @return string
     */
    public static function dirPath(): string
    {
        return trailingslashit(
            ccwpPluginPath('views')
        );
    }
    
    /**
     * Either return the compiled html string or echo it out
     * @param bool $return
     * @return string|void
     * @throws Throwable
     */
    public function render(bool $return = false)
    {
        if ($return) {
            return $this->compile();
        }
        
        echo $this->compile();
    }
    
    /**
     * @return string
     */
    public function getTemplatePath(): string
    {
        return static::dirPath() . $this->file_path;
    }
    
    /**
     * Compile the php template/partial into an html string
     * @return string
     * @throws Throwable
     */
    protected function compile(): string
    {
        if (count($this->data) > 0 && ! $this->validateDataArray()) {
            throw new ViewDataNotValidException('$data is not valid.');
        }
        
        if (!file_exists($this->getTemplatePath())) {
            throw new ViewNotFoundException($this->getTemplatePath() . ' does not exist.');
        }
        
        try {
            ob_start();
            $this->includeTemplate();
            $output = ob_get_clean();
        } catch (Throwable $e) {
            ob_end_clean();
            throw $e;
        }
        
        return $output;
    }
    
    /**
     * @return void
     */
    protected function includeTemplate(): void
    {
        extract($this->data);
        include $this->getTemplatePath();
    }
    
    /**
     * Checks to see if $this->data is an associative array;
     * @return bool
     */
    protected function validateDataArray(): bool
    {
        return array_keys($this->data) !== range(0, count($this->data) - 1);
    }
}
