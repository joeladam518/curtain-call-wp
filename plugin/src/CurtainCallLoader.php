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
     * @param string $tag           The name of the WordPress action that is being registered.
     * @param object $component     A reference to the instance of the object on which the action is defined.
     * @param string $callback      The name of the function definition on the $component.
     * @param int    $priority      Optional. The priority at which the function should be fired.
     * @param int    $accepted_args Optional. The number of arguments that should be passed to the $callback.
     * @return void
     */
    public function add_action($tag, $component, $callback, $priority = 10, $accepted_args = 1)
    {
        $this->actions[] = $this->add($tag, $component, $callback, $priority, $accepted_args);
    }
    
    /**
     * Add a new filter to the collection to be registered with WordPress.
     *
     * @param string $tag           The name of the WordPress filter that is being registered.
     * @param object $component     A reference to the instance of the object on which the filter is defined.
     * @param string $callback      The name of the function definition on the $component.
     * @param int    $priority      Optional. The priority at which the function should be fired.
     * @param int    $accepted_args Optional. The number of arguments that should be passed to the $callback.
     * @return void
     */
    public function add_filter($tag, $component, $callback, $priority = 10, $accepted_args = 1)
    {
        $this->filters[] = $this->add($tag, $component, $callback, $priority, $accepted_args);
    }
    
    /**
     * Add a new shortcode to the collection to be registered with WordPress
     *
     * @param string $tag
     * @param object $component
     * @param string $callback
     * @return void
     */
    public function add_shortcode($tag, $component, $callback)
    {
        $this->shortcodes[] = [
            'tag'       => $tag,
            'component' => $component,
            'callback'  => $callback,
        ];
    }
    
    /**
     * A utility function that is used to register the actions and hooks into a single
     * collection.
     *
     * @param string $tag          The name of the WordPress filter that is being registered.
     * @param object $component     A reference to the instance of the object on which the filter is defined.
     * @param string $callback      The name of the function definition on the $component.
     * @param int    $priority      The priority at which the function should be fired.
     * @param int    $accepted_args The number of arguments that should be passed to the $callback.
     *
     * @return array
     */
    private function add($tag, $component, $callback, $priority, $accepted_args)
    {
        return [
            'tag'           => $tag,
            'component'     => $component,
            'callback'      => $callback,
            'priority'      => $priority,
            'accepted_args' => $accepted_args,
        ];
    }
    
    /**
     * Add the filters, actions and shortcodes that will run te
     * @return void
     */
    public function run()
    {
        foreach($this->filters as $filter) {
            add_filter(
                $filter['tag'],
                array($filter['component'], $filter['callback']),
                $filter['priority'],
                $filter['accepted_args']
            );
        }
        
        foreach($this->actions as $action) {
            add_action(
                $action['tag'],
                array($action['component'], $action['callback']),
                $action['priority'],
                $action['accepted_args']
            );
        }
        
        foreach($this->shortcodes as $shortcode) {
            add_shortcode(
                $shortcode['tag'],
                array($shortcode['component'], $shortcode['callback'])
            );
        }
    }
}
