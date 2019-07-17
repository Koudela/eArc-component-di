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
use eArc\ComponentDI\Interfaces\ComponentListenerInterface;
use eArc\Container\Exceptions\ItemNotFoundException;
use eArc\Container\Exceptions\ItemOverwriteException;
use eArc\DI\DependencyContainer;
use eArc\DI\DependencyResolver;
use eArc\DI\Exceptions\InvalidFactoryException;
use eArc\DI\Exceptions\InvalidObjectConfigurationException;
use eArc\DI\Support\ContainerCollection;
use eArc\EventTree\Exceptions\InvalidDestinationNodeException;
use eArc\EventTree\Exceptions\InvalidStartNodeException;
use eArc\EventTree\Exceptions\IsDispatchedException;
use eArc\EventTree\Exceptions\IsRootEventException;
use eArc\EventTree\RoutingType;
use eArc\EventTree\TreeEvent;
use eArc\Observer\Exception\NoValidListenerException;
use eArc\ObserverTree\Interfaces\ObserverTreeInterface;
use eArc\EventTree\Handler;
use eArc\EventTree\Propagation\TreeEventRouter;

/**
 * Dependency injection container composite factory.
 */
class ComponentEventRouter extends TreeEventRouter
{
    /** @var DependencyContainer */
    protected $container;

    /** @var ContainerCollection */
    protected $containerCollection;

    /**
     * @param TreeEvent $event
     * @param RoutingType $routingType
     *
     * @throws CircularDependencyException
     * @throws InvalidFactoryException
     * @throws InvalidObjectConfigurationException
     * @throws \eArc\DI\Exceptions\CircularDependencyException
     */
    public function __construct(TreeEvent $event, RoutingType $routingType)
    {
        parent::__construct($event, $routingType);

        $this->containerCollection = new ContainerCollection();
        $dependencyResolver = new DependencyResolver([], null, $this->containerCollection);
        $this->container = new DependencyContainer($dependencyResolver);
    }

    /**
     * Defines how the observer calls the listener. The heart of the dependency
     * container composite factory.
     *
     * @param ObserverTreeInterface $observer
     *
     * @throws NoSuchComponentException
     * @throws CircularDependencyException
     * @throws ItemNotFoundException
     * @throws InvalidObjectConfigurationException
     * @throws InvalidFactoryException
     * @throws InvalidFactoryException
     * @throws CircularDependencyException
     */
    protected function visitObserver(ObserverTreeInterface $observer): void
    {
        $eventRouter = $this;

        if (1 === $this->depth) {
            $buildComponentItems = $this->event
                ->get(ComponentContainer::CIRCLE_DETECTION);

            if ($buildComponentItems->has($observer->getName())) {
                throw new CircularDependencyException();
            }

            $buildComponentItems->set($observer->getName(), true);

            $this->event->get(ComponentContainer::CONTAINER_BAG)
                ->set($observer->getName(), $this->container);
        }


        $observer->callListeners(
            $this->event,
            $this->eventPhase,
            function() use ($eventRouter) {
                $state = $eventRouter->getState();
                return 0 !== $state & Handler::EVENT_IS_SILENCED
                    ? ObserverTreeInterface::CALL_LISTENER_BREAK : null;

            },
            null,
            function($result,  $listener) use ($eventRouter) {
                if (!$listener instanceof ComponentListenerInterface) {
                    throw new NoValidListenerException();
                }

                if (!is_array($result)) {
                    throw new InvalidObjectConfigurationException(sprintf(
                        'The `%s` listeners `process` method has to return an array.',
                        get_class($listener)
                    ));
                }

                $eventRouter->buildDependencies(
                    $listener::getComponentDependencies()
                );

                $eventRouter->container->load($result);
            }
        );
    }

    /**
     * @param string[] $dependencies
     *
     * @throws InvalidStartNodeException
     * @throws ItemNotFoundException
     * @throws NoSuchComponentException
     * @throws ItemOverwriteException
     * @throws IsDispatchedException
     * @throws IsRootEventException
     */
    protected function buildDependencies(array $dependencies)
    {
        $this->containerCollection->reset();

        foreach ($dependencies as $dependency)
        {
            if (!$this->event->get(ComponentContainer::CONTAINER_BAG)->has($dependency)) {
                try {
                    $this->event->getRoot()->fork()
                        ->destination([$dependency])
                        ->build()
                        ->dispatch();
                } catch (InvalidDestinationNodeException $notFoundException) {
                    throw new NoSuchComponentException($dependency);
                }
            }

            $container = $this->event
                ->get(ComponentContainer::CONTAINER_BAG)
                ->get($dependency);

            $this->containerCollection->merge($container);
        }
    }
}
