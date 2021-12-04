<?php

namespace CurtainCall;

use CurtainCall\Exceptions\ViewDataNotValidException;
use CurtainCall\Exceptions\ViewNotFoundException;
use CurtainCall\Support\Arr;
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

        if (!$path) {
            return $dirPath;
        }

        return $dirPath . ltrim($path, '/');
    }

    /**
     * Compile the php template/partial into a html string
     *
     * @return string
     * @throws ViewDataNotValidException|ViewNotFoundException|Throwable
     */
    public function compile(): string
    {
        if (!file_exists($this->templatePath())) {
            throw new ViewNotFoundException( $this->templatePath() . ' does not exist.');
        }

        if (!empty($this->data) && Arr::isList($this->data)) {
            throw new ViewDataNotValidException('$data is not valid.');
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
     * Render the template/partial into html
     *
     * @return void
     * @throws ViewDataNotValidException|ViewNotFoundException|Throwable
     */
    public function render(): void
    {
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
     * Include the template/partial
     *
     * @return void
     */
    protected function includeTemplate(): void
    {
        if (!empty($this->data)) {
            extract($this->data, EXTR_OVERWRITE);
        }

        include $this->templatePath();
    }
}
