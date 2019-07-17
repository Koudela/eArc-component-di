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
use eArc\ComponentDI\Exceptions\NoSuchComponentException;
use eArc\ComponentDI\Interfaces\ComponentInterface;
use eArc\ComponentDI\RootComponent;
use eArc\DI\Exceptions\DIException;
use eArc\DI\Exceptions\NotFoundDIException;

class Component implements ComponentInterface
{
    protected static $component = [];
    protected static $type = [];

    protected $fQCNComponent;
    protected $fQCN;
    protected $fQCNType;

    /**
     * @param string $fQCNComponent
     * @param string $fQCN
     * @param int $type
     *
     * @throws NoSuchComponentException
     */
    public function __construct(string $fQCNComponent, string $fQCN, int $type)
    {
        if (!is_subclass_of($fQCNComponent, RootComponent::class)) {
            throw new NoSuchComponentException(sprintf("No component named %s exists.", $fQCNComponent));
        }

        self::$component[$fQCN] = $fQCNComponent;
        self::$type[$fQCN] = $type;

        $this->fQCNComponent = $fQCNComponent;
        $this->fQCN = $fQCN;
        $this->fQCNType = $type;
    }

    public function get(&$class, string $fQCN): ComponentInterface
    {
        $class = di_get($fQCN);

        $this->checkVisibility($fQCN);

        return $this;
    }

    public function getUnregistered(&$class, string $fQCN): ComponentInterface
    {
        $class = di_get($fQCN);

        if (isset(self::$component[$fQCN])) {
            $this->checkVisibility($fQCN);
        }

        return $this;
    }

    public function make(&$class, string $fQCN): ComponentInterface
    {
        $class = di_make($fQCN);

        $this->checkVisibility($fQCN);

        return $this;
    }

    public function makeUnregistered(&$class, string $fQCN): ComponentInterface
    {
        $class = di_make($fQCN);

        if (isset(self::$component[$fQCN])) {
            $this->checkVisibility($fQCN);
        }

        return $this;
    }

    public function param(&$parameter, string $key): ComponentInterface
    {
        $extendedKey = $this->fQCNComponent.'.'.$key;

        if (!di_has_param($extendedKey)) {
            return $this->getParameterByParent($this->fQCNComponent, $key);
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
     * @throws DIException
     */
    protected function getParameterByParent(string $component, string $key)
    {
        $parent = get_parent_class($component);

        if (RootComponent::class === $parent) {
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

        if (self::PRIVATE === $type && $component !== $this->fQCNComponent) {
            throw new AccessDeniedException(sprintf('Class %s belongs to component %s and has private access, but was accessed from component %s.', $fQCN, $component, $this->fQCNComponent));
        }

        if (self::PROTECTED === $type && $component !== $this->fQCNComponent && !is_subclass_of($this->fQCNComponent, $component)) {
            throw new AccessDeniedException(sprintf('Class %s belongs to component %s and has protected access, but was accessed from component %s which does not inherit from.', $fQCN, $component, $this->fQCNComponent));
        }
    }
}
