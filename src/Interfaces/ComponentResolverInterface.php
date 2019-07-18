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

namespace eArc\ComponentDI\Interfaces;

use eArc\ComponentDI\Exceptions\AccessDeniedException;
use eArc\ComponentDI\Exceptions\OverwriteException;
use eArc\ComponentDI\Exceptions\NoSuchComponentException;
use eArc\ComponentDI\Exceptions\NotRegisteredException;
use eArc\DI\Exceptions\DIException;
use eArc\DI\Exceptions\NotFoundDIException;

/**
 * Interface of a component resolver.
 */
interface ComponentResolverInterface
{
    /** Class can be injected to any component */
    const PUBLIC_SERVICE = 0;
    /** Class is injectable to any component that inherits from the classes component */
    const PROTECTED_SERVICE = 1;
    /** Class can only be injected to the same component */
    const PRIVATE_SERVICE = 2;
    /** Class can not be injected */
    const NO_SERVICE = 3;

    /**
     * Registers a class to a component and returns the corresponding resolver.
     *
     * @param string $fQCNComponent The fully qualified class name of the component.
     * @param string $fQCN          The fully qualified class name of the current class.
     * @param int    $type          The access type of the current class.
     *
     * @return ComponentResolverInterface The resolver for the current class.
     *
     * @throws NoSuchComponentException   The root component is no ancestor of `$fQCNComponent`.
     * @throws OverwriteException         The class is already registered to a different
     * component or with a different visibility type..
     */

    public static function register(string $fQCNComponent, string $fQCN, int $type=self::NO_SERVICE): ComponentResolverInterface;

    /**
     * Returns the resolver of an already registered class.
     *
     * @param string $fQCN The fully qualified class name of the class
     *
     * @return ComponentResolverInterface The resolver registered to the class.
     *
     * @throws NotRegisteredException The class has not been registered.
     */
    public static function getRegistered(string $fQCN): ComponentResolverInterface;

    /**
     * Returns the short name used in the parameter context of a component.
     *
     * @param string $fQCNComponent The fully qualified class name of the component.
     *
     * @return string The short name/key of the component.
     */
    public static function getKey(string $fQCNComponent): string;

    /**
     * Checks the class identifier against the current component. On success it passes
     * the returned object of the function `di_get($fQCN)` to the `$class` variable.
     * Otherwise it throws an AccessDeniedException.
     *
     * @param mixed  $class The variable to pass the classes instance.
     * @param string $fQCN The identifier of the class.
     *
     * @return ComponentResolverInterface The current component object.
     *
     * @throws AccessDeniedException The current component has no access to the class.
     * @throws DIException `di_get($fQCN)` has thrown an error.
     */
    public function get(&$class, string $fQCN): ComponentResolverInterface;

    /**
     * As `get`, but it does not throw an AccessDeniedException if the class is
     * not registered to any component.
     *
     * @param mixed  $class The variable to pass the classes instance.
     * @param string $fQCN The identifier of the class.
     *
     * @return ComponentResolverInterface The current component object.
     *
     * @throws AccessDeniedException The current component has no access to the class.
     * @throws DIException `di_get($fQCN)` has thrown an error.
     */
    public function getUnregistered(&$class, string $fQCN): ComponentResolverInterface;

    /**
     * Checks the class identifier against the current component. On success it passes
     * the returned object of the function `di_make($fQCN)` to the `$class` variable.
     * Otherwise it throws an AccessDeniedException.
     *
     * @param mixed  $class The variable to pass the classes instance.
     * @param string $fQCN The identifier of the class.
     *
     * @return ComponentResolverInterface The current component object.
     *
     * @throws AccessDeniedException The current component has no access to the class.
     * @throws DIException `di_make($fQCN)` has thrown an error.
     */
    public function make(&$class, string $fQCN): ComponentResolverInterface;

    /**
     * As `make`, but it does not throw an AccessDeniedException if the class is
     * not registered to any component.
     *
     * @param mixed  $class The variable to pass the classes instance.
     * @param string $fQCN The identifier of the class.
     *
     * @return ComponentResolverInterface The current component object.
     *
     * @throws AccessDeniedException The current component has no access to the class.
     * @throws DIException `di_make($fQCN)` has thrown an error.
     */
    public function makeUnregistered(&$class, string $fQCN): ComponentResolverInterface;

    /**
     * Searches the component and all parent components for the parameter. If none
     * holds it looks up the global namespace. If a parameter is found it is passed
     * to the `$param` variable otherwise a NotFoundDIException is thrown.
     *
     * @param mixed  $param
     * @param string $key
     *
     * @return ComponentResolverInterface The current component object.
     *
     * @throws NotFoundDIException The parameter is not set.
     */
    public function param(&$param, string $key): ComponentResolverInterface;
}
