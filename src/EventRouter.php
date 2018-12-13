<?php
/**
 * e-Arc Framework - the explicit Architecture Framework
 *
 * @package earc/component-di
 * @link https://github.com/Koudela/eArc-component-di/
 * @copyright Copyright (c) 2018 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\ComponentDI;

use Behat\Testwork\Output\Node\EventListener\EventListener;
use eArc\ComponentDI\Exceptions\CircularDependencyException;
use eArc\ComponentDI\Exceptions\NoSuchComponentException;
use eArc\DI\DependencyContainer;
use eArc\DI\Exceptions\NotFoundException;
use eArc\eventTree\Event;
use eArc\Tree\Exceptions\NotFoundException as ObserverNotFoundException;
use eArc\EventTree\Handler;
use eArc\EventTree\Propagation\EventRouter as BaseEventRouter;
use eArc\ObserverTree\Observer;

class EventRouter extends BaseEventRouter
{
    /** @var string[] */
    protected $dependencies = [];

    /** @var DependencyContainer */
    protected $container;

    public function __construct(Event $event)
    {
        parent::__construct($event);

        $eventRouter = $this;

        $this->container = new DependencyContainer(null, function($name) use ($eventRouter) {
            foreach ($eventRouter->dependencies as $dependency) {
                /** @var DependencyContainer $depContainer */
                $depContainer = $eventRouter->event
                    ->get('eArcDIContainer:'.$dependency);

                if ($depContainer->has($name)) {

                    return $depContainer->get($name);
                }
            }

            throw new NotFoundException();
        });
    }

    /**
     * Defines how the observer calls the listener.
     *
     * @param Observer $observer
     *
     * @throws NoSuchComponentException
     */
    protected function visitObserver(Observer $observer): void
    {
        $eventRouter = $this;

        if (1 === $this->depth) {
            $buildComponentItems = $this->event
                ->get(ComponentContainer::CIRCLE_DETECTION);

            if ($buildComponentItems->has($observer->getName())) {
                throw new CircularDependencyException();
            }

            $buildComponentItems->set($observer->getName(), true);
        }

        $observer->callListeners(
            $this->event,
            $this->eventPhase,
            function() use ($eventRouter) {
                $state = $eventRouter->event->getHandler()->transferState($eventRouter);
                return 0 !== $state & Handler::EVENT_IS_SILENCED
                    ? Observer::CALL_LISTENER_BREAK : null;
            },
            null,
            function($result, EventListener $listener) use ($eventRouter) {
                $dependencies = $listener::EARC_LISTENER_COMPONENT_DEPENDENCIES ?? [];

                $eventRouter->buildDependencies($dependencies);

                if (is_array($result)) {
                    $eventRouter->container->load($result);
                }
            },
            $this->getContainer()
        );

        if (1 === $this->depth) {
            $this->event->getPayload()->get(ComponentContainer::CONTAINER_BAG)
                ->set($observer->getName(), $this->container);
        }

        $this->event->getHandler()->transferState($this);
    }

    /**
     * @param array $dependencies
     *
     * @throws NoSuchComponentException
     */
    protected function buildDependencies(array $dependencies)
    {
        foreach ($dependencies as $dependency)
        {
            if (isset($this->dependencies[$dependency])) {
                continue;
            }

            if (!$this->event->get(ComponentContainer::CONTAINER_BAG)->has($dependency)) {
                try {
                    $this->event->getEventFactoryFromRoot()
                        ->destination([$dependency])
                        ->build()
                        ->dispatch();
                } catch (ObserverNotFoundException $notFoundException) {
                    throw new NoSuchComponentException($dependency);
                }
            }

            $this->dependencies[$dependency] = $this->event
                ->get(ComponentContainer::CONTAINER_BAG)->get($dependency);
        }
    }
}
