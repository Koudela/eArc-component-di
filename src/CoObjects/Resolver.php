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
use eArc\ComponentDI\Exceptions\OverwriteException;
use eArc\ComponentDI\Exceptions\NoSuchComponentException;
use eArc\ComponentDI\Exceptions\NotRegisteredException;
use eArc\ComponentDI\Interfaces\ComponentResolverInterface;
use eArc\ComponentDI\RootComponent;
use eArc\DI\Exceptions\NotFoundDIException;

class Resolver implements ComponentResolverInterface
{
    /** @var string[]  */
    protected static $component = [];
    /** @var int[] */
    protected static $type = [];
    /** @var string[] */
    protected static $shortName = [];
    /** @var ComponentResolverInterface[] */
    protected static $resolver = [];

    /** @var string */
    protected $fQCNComponent;
    /** @var string */
    protected $fQCN;
    /** @var int */
    protected $fQCNType;

    protected function __construct(string $fQCNComponent, string $fQCN, int $type)
    {
        $this->fQCNComponent = $fQCNComponent;
        $this->fQCN = $fQCN;
        $this->fQCNType = $type;
    }

    public static function register(string $fQCNComponent, string $fQCN, int $type = self::NO_SERVICE): ComponentResolverInterface
    {
        if (!is_subclass_of($fQCNComponent, RootComponent::class)) {
            throw new NoSuchComponentException(sprintf('No component named %s exists.', $fQCNComponent));
        }

        if (isset(self::$resolver[$fQCN])) {
            if (self::$component[$fQCN] !== $fQCNComponent) {
                throw new OverwriteException(sprintf('Class %s can not be registered to component %s as it is already registered to component %s.', $fQCN, $fQCNComponent, self::$component[$fQCN]));
            }

            if (self::$type[$fQCN] !== $type) {
                throw new OverwriteException(sprintf('Class %s can not be registered again with a different visibility type.', $fQCN));
            }
        } else {
            self::$component[$fQCN] = $fQCNComponent;
            self::$type[$fQCN] = $type;
            self::$resolver[$fQCN] = new static($fQCNComponent, $fQCN, $type);
        }

        return self::$resolver[$fQCN];
    }

    public static function getRegistered(string $fQCN): ComponentResolverInterface
    {
        if (!isset(self::$resolver[$fQCN])) {
            throw new NotRegisteredException(sprintf('Class %s is not registered to any component.', $fQCN));
        }

        return self::$resolver[$fQCN];
    }

    public static function getKey(string $fQCNComponent): string
    {
        if (!isset(self::$shortName[$fQCNComponent])) {
            $pos = strrpos($fQCNComponent, '\\');
            self::$shortName[$fQCNComponent] = strtolower(substr($fQCNComponent, false === $pos ? 0 : $pos + 1));
        }

        return self::$shortName[$fQCNComponent];
    }

    public function get(&$class, string $fQCN): ComponentResolverInterface
    {
        $class = di_get($fQCN);

        $this->checkVisibility($fQCN);

        return $this;
    }

    public function getUnregistered(&$class, string $fQCN): ComponentResolverInterface
    {
        $class = di_get($fQCN);

        if (isset(self::$component[$fQCN])) {
            $this->checkVisibility($fQCN);
        }

        return $this;
    }

    public function make(&$class, string $fQCN): ComponentResolverInterface
    {
        $class = di_make($fQCN);

        $this->checkVisibility($fQCN);

        return $this;
    }

    public function makeUnregistered(&$class, string $fQCN): ComponentResolverInterface
    {
        $class = di_make($fQCN);

        if (isset(self::$component[$fQCN])) {
            $this->checkVisibility($fQCN);
        }

        return $this;
    }

    public function param(&$parameter, string $key): ComponentResolverInterface
    {
        $extendedKey = self::getKey($this->fQCNComponent).'.'.$key;

        if (!di_has_param($extendedKey)) {
            $parameter = $this->getParameterByParent($this->fQCNComponent, $key);

            return $this;
        }

        $parameter = di_param($extendedKey);

        return $this;
    }

    /**
     * @param string $component
     * @param string $key
     *
     * @return mixed
     *
     * @throws NotFoundDIException
     */
    protected function getParameterByParent(string $component, string $key)
    {
        $parent = get_parent_class($component);

        if (RootComponent::class === $parent) {
            if (di_has_param($key)) {
                return di_param($key);
            }

            throw new NotFoundDIException(sprintf('Parameter %s was never added to the parameter bag of component %s.', $key, $this->fQCNComponent));
        }

        $extendedKey = $parent.'.'.$key;

        if (!di_has_param($extendedKey)) {
            return $this->getParameterByParent($this->fQCNComponent, $key);
        }

        return di_param($extendedKey);
    }

    /**
     * @param string $fQCN
     *
     * @throws AccessDeniedException
     */
    protected function checkVisibility(string $fQCN): void
    {
        if (!isset(self::$component[$fQCN])) {
            throw new AccessDeniedException(sprintf('Class %s was never added to a component.', $fQCN));
        }

        $type = self::$type[$fQCN];
        $component = self::$component[$fQCN];

        if (self::NO_SERVICE === $type) {
            throw new AccessDeniedException(sprintf('Class %s is not accessible, it is no service.', $fQCN));
        }

        if (self::PRIVATE_SERVICE === $type && $component !== $this->fQCNComponent) {
            throw new AccessDeniedException(sprintf('Class %s belongs to component %s and has private access, but was accessed from component %s.', $fQCN, $component, $this->fQCNComponent));
        }

        if (self::PROTECTED_SERVICE === $type && $component !== $this->fQCNComponent && !is_subclass_of($this->fQCNComponent, $component)) {
            throw new AccessDeniedException(sprintf('Class %s belongs to component %s and has protected access, but was accessed from component %s which does not inherit from.', $fQCN, $component, $this->fQCNComponent));
        }
    }
}
