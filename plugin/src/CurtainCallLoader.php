<?php

namespace CurtainCallWP;

/**
 * Class CurtainCallLoader
 * @package CurtainCallWP
 */
class CurtainCallLoader
{
    /**
     * The actions registered with WordPress to fire when the plugin loads.
     * @var array
     */
    protected $actions;
    
    /**
     * The filters registered with WordPress to fire when the plugin loads.
     * @var array
     */
    protected $filters;
    
    /**
     * The shortcodes registered with WordPress to fire when the plugin loads.
     * @var array
     */
    protected $shortcodes;
    
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
     * @param string $hook          The name of the WordPress action that is being registered.
     * @param object $component     A reference to the instance of the object on which the action is defined.
     * @param string $callback      The name of the function definition on the $component.
     * @param int    $priority      Optional. The priority at which the function should be fired.
     * @param int    $accepted_args Optional. The number of arguments that should be passed to the $callback.
     *
     * @return void
     */
    public function add_action($hook, $component, $callback, $priority = 10, $accepted_args = 1)
    {
        $this->actions = $this->add($this->actions, $hook, $component, $callback, $priority, $accepted_args);
    }
    
    /**
     * Add a new filter to the collection to be registered with WordPress.
     *
     * @param string $hook          The name of the WordPress filter that is being registered.
     * @param object $component     A reference to the instance of the object on which the filter is defined.
     * @param string $callback      The name of the function definition on the $component.
     * @param int    $priority      Optional. The priority at which the function should be fired.
     * @param int    $accepted_args Optional. The number of arguments that should be passed to the $callback.
     *
     * @return void
     */
    public function add_filter($hook, $component, $callback, $priority = 10, $accepted_args = 1)
    {
        $this->filters = $this->add($this->filters, $hook, $component, $callback, $priority, $accepted_args);
    }
    
    /**
     * Add a new shortcode to the collection to be registered with WordPress
     *
     * @param     $tag
     * @param     $component
     * @param     $callback
     * @param int $priority
     * @param int $accepted_args
     *
     * @return void
     */
    public function add_shortcode($tag, $component, $callback, $priority = 10, $accepted_args = 1)
    {
        $this->shortcodes = $this->add($this->shortcodes, $tag, $component, $callback, $priority, $accepted_args);
    }
    
    /**
     *  A utility function that is used to register the actions and hooks into a single
     *  collection.
     *
     * @param array  $hooks         The collection of hooks that is being registered.
     * @param string $hook          The name of the WordPress filter that is being registered.
     * @param object $component     A reference to the instance of the object on which the filter is defined.
     * @param string $callback      The name of the function definition on the $component.
     * @param int    $priority      The priority at which the function should be fired.
     * @param int    $accepted_args The number of arguments that should be passed to the $callback.
     *
     * @return   array
     */
    private function add($hooks, $hook, $component, $callback, $priority, $accepted_args)
    {
        $hooks[] = [
            'hook'          => $hook,
            'component'     => $component,
            'callback'      => $callback,
            'priority'      => $priority,
            'accepted_args' => $accepted_args,
        ];
        
        return $hooks;
    }
    
    /**
     * Add the filters, actions and shortcodes that will run te
     * @return void
     */
    public function run()
    {
        foreach ($this->filters as $hook) {
            add_filter($hook['hook'], [$hook['component'], $hook['callback']], $hook['priority'],
                $hook['accepted_args']);
        }
        
        foreach ($this->actions as $hook) {
            add_action($hook['hook'], [$hook['component'], $hook['callback']], $hook['priority'],
                $hook['accepted_args']);
        }
        
        foreach ($this->shortcodes as $hook) {
            add_shortcode($hook['hook'], [$hook['component'], $hook['callback']]);
        }
    }
}
