<?php

namespace eArc\ComponentDI;

use eArc\ComponentDI\Exceptions\NoSuchComponentException;
use eArc\DI\Exceptions\NotFoundException;
use eArc\PayloadContainer\Exceptions\ItemNotFoundException;
use eArc\Tree\Exceptions\NotFoundException as ObserverNotFoundException;
use eArc\DI\DependencyContainer;
use eArc\EventTree\Event;
use eArc\EventTree\Type;
use eArc\ObserverTree\Observer;

class ComponentContainer
{
    /** @var Event */
    protected $rootEvent;

    /** @var array */
    protected $components = [];

    public function __construct(Observer $eventTree)
    {
        //$eventTreeFactory = new ObserverTreeFactory($eventTreeRootAbsoluteDir, $eventTreeRootNamespace);
        //$eventTreeFactory->get($componentsRootKey)
        //string $componentsRootKey = 'components';

        $this->rootEvent = new Event(
            null,
            new Type($eventTree, [], [], null),
            true,
            EventRouter::class,
            null);
    }

    /**
     * @param string $component
     * @param string $name
     *
     * @return bool
     *
     * @throws NoSuchComponentException
     */
    public function has(string $component, string $name)
    {
        return $this->getComponent($component)->has($name);
    }

    /**
     * @param string $component
     * @param string $name

     * @return object
     *
     * @throws NotFoundException
     * @throws NoSuchComponentException
     */
    public function get(string $component, string $name)
    {
        return $this->getComponent($component)->get($name);
    }

    /**
     * @param string $component
     * @param string $name
     *
     * @return object
     *
     * @throws NotFoundException
     * @throws NoSuchComponentException
     */
    public function make(string $component, string $name)
    {
        return $this->getComponent($component)->make($name);
    }

    /**
     * @param string $component
     *
     * @return DependencyContainer
     *
     * @throws NoSuchComponentException
     */
    public function getComponent(string $component): DependencyContainer
    {
        if (!isset($this->components[$component])) {
            $this->components[$component] = $this->buildComponent($component);
        }

        return $this->components[$component];
    }

    /**
     * @param string $component
     *
     * @return DependencyContainer
     *
     * @throws NoSuchComponentException
     */
    protected function buildComponent(string $component): DependencyContainer
    {
        try {
            return $this->rootEvent->getPayload()->get('eArcDIContainer:'.$component);
        } catch (ItemNotFoundException $notFoundException) {}

        try {
            $buildEvent = $this->rootEvent->getEventFactory()
                ->destination([$component])
                ->build();

            $buildEvent->dispatch();

            return $buildEvent->getPayload()->get('eArcDIContainer:' . $component);
        } catch (ObserverNotFoundException $notFoundException) {
            throw new NoSuchComponentException($component);
        }
    }
}
