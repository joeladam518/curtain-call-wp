<?php

namespace CurtainCall;

class Loader
{
    /**
     * The actions registered with WordPress to fire when the plugin loads.
     * @var array
     */
    protected array $actions;

    /**
     * The filters registered with WordPress to fire when the plugin loads.
     * @var array
     */
    protected array $filters;

    /**
     * The shortcodes registered with WordPress to fire when the plugin loads.
     * @var array
     */
    protected array $shortcodes;

    /**
     * CurtainCallLoader constructor.
     */
    public function __construct()
    {
        $this->actions    = [];
        $this->filters    = [];
        $this->shortcodes = [];
    }

    /**
     * Add a new action to the collection to be registered with WordPress.
     *
     * @param string   $name          The name of the WordPress action that is being registered.
     * @param callable $callback      The callback to be run when the action is called.
     * @param int      $accepted_args Optional. The number of arguments that should be passed to the $callback.
     * @param int      $priority      Optional. The priority at which the function should be fired.
     *
     * @return void
     */
    public function addAction(string $name, callable $callback, int $accepted_args = 1, int $priority = 10): void
    {
        $this->actions[] = [
            'name'          => $name,
            'callback'      => $callback,
            'priority'      => $priority,
            'accepted_args' => $accepted_args,
        ];
    }

    /**
     * Add a new filter to the collection to be registered with WordPress.
     *
     * @param string   $name          The name of the WordPress filter that is being registered.
     * @param callable $callback      The callback to be run when the filter is applied.
     * @param int      $priority      Optional. The priority at which the function should be fired.
     * @param int      $accepted_args Optional. The number of arguments that should be passed to the $callback.
     *
     * @return void
     */
    public function addFilter(string $name, callable $callback, int $accepted_args = 1, int $priority = 10): void
    {
        $this->filters[] = [
            'name'          => $name,
            'callback'      => $callback,
            'priority'      => $priority,
            'accepted_args' => $accepted_args,
        ];
    }

    /**
     * Add a new shortcode to the collection to be registered with WordPress
     *
     * @param string $name
     * @param callable $callback
     *
     * @return void
     */
    public function addShortcode(string $name, callable $callback): void
    {
        $this->shortcodes[] = [
            'name'     => $name,
            'callback' => $callback,
        ];
    }

    /**
     * Add the filters, actions and shortcodes that will run be executed in WordPress
     * @return void
     */
    public function run(): void
    {
        foreach($this->filters as $filter) {
            add_filter(
                $filter['name'],
                $filter['callback'],
                $filter['priority'],
                $filter['accepted_args']
            );
        }

        foreach($this->actions as $action) {
            add_action(
                $action['name'],
                $action['callback'],
                $action['priority'],
                $action['accepted_args']
            );
        }

        foreach($this->shortcodes as $shortcode) {
            add_shortcode(
                $shortcode['name'],
                $shortcode['callback']
            );
        }
    }
}
