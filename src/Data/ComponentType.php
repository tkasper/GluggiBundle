<?php

namespace Becklyn\GluggiBundle\Data;

use Becklyn\GluggiBundle\Exception\ComponentNotFoundException;

/**
 * Describes a component type
 */
class ComponentType
{
    /**
     * @var string
     */
    private $key;


    /**
     * @var Component[]
     */
    private $components = [];


    /**
     * @param string $key
     */
    public function __construct (string $key)
    {
        $this->key = $key;
    }


    /**
     * @return string
     */
    public function getKey () : string
    {
        return $this->key;
    }


    /**
     * @return string
     */
    public function getName () : string
    {
        return \ucwords($this->key);
    }


    /**
     * Returns all components in this type
     *
     * @return Component[]
     */
    public function getComponents () : array
    {
        return $this->components;
    }


    /**
     * Returns a single component by key
     *
     * @param string $key
     *
     * @return Component
     */
    public function getComponent (string $key) : Component
    {
        if (!isset($this->components[$key]))
        {
            throw new ComponentNotFoundException($key, $this->key);
        }

        return $this->components[$key];
    }


    /**
     * @param Component $component
     */
    public function addComponent (Component $component) : void
    {
        $this->components[$component->getKey()] = $component;
    }
}
