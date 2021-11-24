<?php

namespace CurtainCallWP;

use CurtainCallWP\Exceptions\ViewDataNotValidException;
use CurtainCallWP\Exceptions\ViewNotFoundException;
use CurtainCallWP\Support\Arr;
use Throwable;

class View
{
    protected array $data;
    protected string $path;

    public function __construct(string $path, array $data = [])
    {
        $this->path = trim($path);
        $this->data = $data;
    }

    /**
     * @param string $path
     * @param array $data
     * @return self
     */
    public static function make(string $path, array $data = []): self
    {
        return new static($path, $data);
    }

    /**
     * @param string|null $path
     * @return string
     */
    public static function path(?string $path = null): string
    {
        $dirPath = trailingslashit(ccwpPluginPath('views'));

        if ($path) {
            return $dirPath . ltrim($path, '/');
        }

        return $dirPath;
    }

    /**
     * Render the template/partial into html
     *
     * @param bool $return
     * @return string|void
     * @throws ViewDataNotValidException|ViewNotFoundException|Throwable
     */
    public function render(bool $return = false)
    {
        if ($return) {
            return $this->compile();
        }

        echo $this->compile();
    }

    /**
     * The absolute path to the template/partial.
     *
     * @return string
     */
    public function templatePath(): string
    {
        return static::path($this->path);
    }

    /**
     * Compile the php template/partial into html
     *
     * @return string
     * @throws ViewDataNotValidException|ViewNotFoundException|Throwable
     */
    protected function compile(): string
    {
        if (count($this->data) > 0 && Arr::isList($this->data)) {
            throw new ViewDataNotValidException('$data is not valid.');
        }

        if (!file_exists($this->templatePath())) {
            throw new ViewNotFoundException( $this->templatePath().' does not exist.');
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
     * Include the template/partial
     *
     * @return void
     */
    protected function includeTemplate(): void
    {
        extract($this->data);
        include $this->templatePath();
    }
}
