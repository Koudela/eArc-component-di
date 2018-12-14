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

use eArc\ComponentDI\Exceptions\NoSuchComponentException;
use eArc\PayloadContainer\Items;
use eArc\Tree\Exceptions\NotFoundException as ObserverNotFoundException;
use eArc\DI\DependencyContainer;
use eArc\EventTree\Event;
use eArc\EventTree\Type;
use eArc\ObserverTree\Observer;

/**
 * Dependency injection container composite facade.
 */
class ComponentContainer
{
    const CIRCLE_DETECTION = ComponentContainer::class . '/CircleDetection';

    const CONTAINER_BAG = ComponentContainer::class . '/ContainerBag';

    /** @var Event */
    protected $rootEvent;

    /** @var Items */
    protected $components;

    public function __construct(Observer $eventTree)
    {
        $this->rootEvent = new Event(
            null,
            new Type($eventTree, [], [], null),
            true,
            EventRouter::class,
            null);

        $this->components = new Items;
        $this->rootEvent->getPayload()->set(self::CONTAINER_BAG, $this->components);
        $this->rootEvent->getPayload()->set(self::CIRCLE_DETECTION, new Items());
    }

    /**
     * @param string $component
     *
     * @return DependencyContainer
     *
     * @throws NoSuchComponentException
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
     */
    protected function buildComponent(string $component): void
    {
        try {
            $buildEvent = $this->rootEvent->getEventFactory()
                ->destination([$component])
                ->build();

            $buildEvent->dispatch();
        } catch (ObserverNotFoundException $notFoundException) {
            throw new NoSuchComponentException($component);
        }
    }
}
