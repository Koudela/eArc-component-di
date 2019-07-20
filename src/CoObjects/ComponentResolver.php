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

namespace eArc\ComponentDI\CoObjects;

use eArc\ComponentDI\Exceptions\AccessDeniedException;
use eArc\ComponentDI\Exceptions\NoComponentException;
use eArc\ComponentDI\Interfaces\ComponentInterface;
use eArc\ComponentDI\Interfaces\ComponentResolverInterface;
use eArc\ComponentDI\Interfaces\Flags\NoServiceInterface;
use eArc\ComponentDI\Interfaces\Flags\PrivateServiceInterface;
use eArc\ComponentDI\Interfaces\Flags\ProtectedServiceInterface;
use eArc\ComponentDI\Interfaces\Flags\PublicServiceInterface;
use eArc\ComponentDI\RootComponent;
use eArc\DI\Exceptions\NotFoundException;

class ComponentResolver implements ComponentResolverInterface
{
    /** @var ComponentResolverInterface[] */
    protected static $resolver = [];

    /** @var ComponentInterface|string */
    protected $fQCN;

    /** @var RootComponent|string */
    protected $fQCNComponent;

    /**
     * @param string $fQCN
     *
     * @throws NoComponentException
     */
    protected function __construct(string $fQCN)
    {
        if (!is_subclass_of($fQCN, PublicServiceInterface::class)) {
            throw new NoComponentException(sprintf('Class %s has no flag component interface.', $fQCN));
        }

        $this->fQCN = $fQCN;
        $this->fQCNComponent = $this->fQCN::getComponent();

        if (!is_subclass_of($this->fQCNComponent, RootComponent::class)) {
            throw new NoComponentException(sprintf('No component named %s exists.', $this->fQCNComponent));
        }
    }

    public static function getComponentResolver(string $fQCN): ComponentResolverInterface
    {
        if (!isset(self::$resolver[$fQCN])) {
            self::$resolver[$fQCN] = new static($fQCN);
        }

        return self::$resolver[$fQCN];
    }


    public function get(&$object, string $fQCN): ComponentResolverInterface
    {
        $this->checkVisibility($this->getDecorator($fQCN));

        $object = di_get($fQCN);

        return $this;
    }

    public function make(&$object, string $fQCN): ComponentResolverInterface
    {
        $this->checkVisibility($this->getDecorator($fQCN));

        $object = di_make($fQCN);

        return $this;
    }

    protected function getDecorator(string $fQCN): string
    {
        return di_is_decorated($fQCN) ? $this->getDecorator(di_get_decorator($fQCN)) : $fQCN;
    }

    public function param(&$parameter, string $key): ComponentResolverInterface
    {
        $parameter = $this->getParameterRecursive($this->fQCNComponent, $key);

        return $this;
    }

    /**
     * @param string $component
     * @param string $key
     *
     * @return mixed
     *
     * @throws NotFoundException
     */
    protected function getParameterRecursive(string $component, string $key)
    {
        /** @var RootComponent $component */
        $shortName = RootComponent::class === $component ? null : $component::getShortName();

        $extendedKey = (null !== $shortName ? $shortName.'.' : '').$key;

        if (di_has_param($extendedKey)) {
            return di_param($extendedKey);
        }

        if (null === $shortName) {
            throw new NotFoundException(sprintf('Parameter %s was never added to the parameter bag of component %s.', $key, $this->fQCNComponent));
        }

        return $this->getParameterRecursive(get_parent_class($component), $key);
    }

    public static function hasAccess(string $fQCNCurrent, string $fQCNCall): bool
    {
        if (is_subclass_of($fQCNCall, PublicServiceInterface::class)
            && !is_subclass_of($fQCNCall, NoServiceInterface::class)
        ) {
            /** @var ComponentInterface $fQCNCurrent */
            $componentCurrent = $fQCNCurrent::getComponent();
            /** @var ComponentInterface $fQCNCall */
            $componentCall = $fQCNCall::getComponent();

            if (is_subclass_of($fQCNCall, PrivateServiceInterface::class) && $componentCall !== $componentCurrent) {
                return false;
            }

            return !is_subclass_of($fQCNCall, ProtectedServiceInterface::class)
                || $componentCall === $componentCurrent
                || is_subclass_of($componentCurrent, $componentCall);

        }

        return false;
    }

    /**
     * @param string $fQCN
     *
     * @throws AccessDeniedException
     */
    protected function checkVisibility(string $fQCN): void
    {
        if (!is_subclass_of($fQCN, PublicServiceInterface::class)) {
            throw new AccessDeniedException(sprintf('Class %s has no flag component interface.', $fQCN));
        }

        /** @var ComponentInterface $fQCN */
        $component = $fQCN::getComponent();

        if (is_subclass_of($fQCN, NoServiceInterface::class)) {
            throw new AccessDeniedException(sprintf('Class %s is not accessible, it is no service.', $fQCN));
        }

        if (is_subclass_of($fQCN, PrivateServiceInterface::class) && $component !== $this->fQCNComponent) {
            throw new AccessDeniedException(sprintf('Class %s belongs to component %s and has private access, but was accessed from component %s.', $fQCN, $component, $this->fQCNComponent));
        }

        if (is_subclass_of($fQCN, ProtectedServiceInterface::class) && $component !== $this->fQCNComponent && !is_subclass_of($this->fQCNComponent, $component)) {
            throw new AccessDeniedException(sprintf('Class %s belongs to component %s and has protected access, but was accessed from component %s which does not inherit from.', $fQCN, $component, $this->fQCNComponent));
        }
    }
}
