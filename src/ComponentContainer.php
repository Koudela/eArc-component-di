<?php declare(strict_types=1);
/**
 * e-Arc Framework - the explicit Architecture Framework
 * component dependency injection component
 *
 * @package earc/component-di
 * @link https://github.com/Koudela/eArc-component-di/
 * @copyright Copyright (c) 2018-2019 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */


namespace eArc\ComponentDI;

use eArc\ComponentDI\Exceptions\CircularDependencyException;
use eArc\ComponentDI\Exceptions\NoSuchComponentException;
use eArc\Container\Exceptions\ItemNotFoundException;
use eArc\Container\Exceptions\ItemOverwriteException;
use eArc\Container\Items;
use eArc\EventTree\Exceptions\EventTreeException;
use eArc\EventTree\Exceptions\InvalidDestinationNodeException;
use eArc\EventTree\Exceptions\InvalidStartNodeException;
use eArc\EventTree\Exceptions\IsDispatchedException;
use eArc\EventTree\Exceptions\IsRootEventException;
use eArc\EventTree\RoutingType;
use eArc\EventTree\TreeEvent;
use eArc\ObserverTree\Interfaces\ObserverTreeInterface;
use eArc\DI\DependencyContainer;

/**
 * Dependency injection container composite facade.
 */
class ComponentContainer
{
    const CIRCLE_DETECTION = ComponentContainer::class . '/CircleDetection';

    const CONTAINER_BAG = ComponentContainer::class . '/ContainerBag';

    /** @var TreeEvent */
    protected $rootEvent;

    /** @var Items */
    protected $components;

    /**
     * @param ObserverTreeInterface $observerTree
     *
     * @throws ItemOverwriteException
     * @throws EventTreeException
     * @throws InvalidDestinationNodeException
     * @throws InvalidStartNodeException
     */
    public function __construct(ObserverTreeInterface $observerTree)
    {
        $this->rootEvent = new TreeEvent(
            null,
            new RoutingType($observerTree, [], [], null),
            null,
            ComponentEventRouter::class
        );

        $this->components = new Items();
        $this->rootEvent->set(self::CONTAINER_BAG, $this->components);
        $this->rootEvent->set(self::CIRCLE_DETECTION, new Items());
    }

    /**
     * @param string $component
     *
     * @return DependencyContainer
     *
     * @throws NoSuchComponentException
     * @throws CircularDependencyException
     * @throws ItemNotFoundException
     * @throws ItemOverwriteException
     * @throws InvalidStartNodeException
     * @throws IsDispatchedException
     * @throws IsRootEventException
     */
    public function get(string $component): DependencyContainer
    {
        if (!$this->components->has($component)) {
            $this->buildComponent($component);
        }

        return $this->components->get($component);
    }

    /**
     * @param string $component
     *
     * @throws NoSuchComponentException
     * @throws CircularDependencyException
     * @throws ItemOverwriteException
     * @throws InvalidStartNodeException
     * @throws IsDispatchedException
     * @throws IsRootEventException
     */
    protected function buildComponent(string $component): void
    {
        try {
            $buildEvent = $this->rootEvent->fork()
                ->destination([$component])
                ->inheritPayload(true)
                ->build();

            $buildEvent->dispatch();
        } catch (InvalidDestinationNodeException $notFoundException) {
            throw new NoSuchComponentException($component);
        }
    }
}
