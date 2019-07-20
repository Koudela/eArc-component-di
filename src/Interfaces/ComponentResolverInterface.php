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
use eArc\ComponentDI\Exceptions\NoComponentException;
use eArc\DI\Exceptions\InvalidArgumentException;
use eArc\DI\Exceptions\MakeClassException;
use eArc\DI\Exceptions\NotFoundException;

/**
 * Interface of a component resolver.
 */
interface ComponentResolverInterface
{
    /**
     * Returns the component resolver of class.
     *
     * @param string $fQCN The fully qualified class name of the class
     *
     * @return ComponentResolverInterface The component resolver of a class.
     *
     * @throws NoComponentException The class does not implement a component flag.
     */
    public static function getComponentResolver(string $fQCN): ComponentResolverInterface;

    /**
     * Checks whether a class is accessible to another class or not.
     *
     * @param string $fQCNCurrent The fully qualified class name of the accessor class.
     * @param string $fQCNCall    The fully qualified class name of the accessed class.
     *
     * @return bool
     */
    public static function hasAccess(string $fQCNCurrent, string $fQCNCall): bool;

    /**
     * Checks the class identifier against the current component. On success it passes
     * the returned object of the function `di_get($fQCN)` to the `$object` variable.
     * Otherwise it throws an AccessDeniedException.
     *
     * @param mixed  $object The variable to pass the classes instance.
     * @param string $fQCN The identifier of the class.
     *
     * @return ComponentResolverInterface The current component object.
     *
     * @throws AccessDeniedException   The current component has no access to the class.
     * @throws MakeClassException       Error while instantiating the class.
     * @throws InvalidArgumentException The decorator is no subclass of the identifier
     */
    public function get(&$object, string $fQCN): ComponentResolverInterface;

    /**
     * Checks the class identifier against the current component. On success it passes
     * the returned object of the function `di_make($fQCN)` to the `$object` variable.
     * Otherwise it throws an AccessDeniedException.
     *
     * @param mixed  $object The variable to pass the classes instance.
     * @param string $fQCN The identifier of the class.
     *
     * @return ComponentResolverInterface The current component object.
     *
     * @throws AccessDeniedException    The current component has no access to the class.
     * @throws MakeClassException       Error while instantiating the class.
     * @throws InvalidArgumentException The decorator is no subclass of the identifier
     */
    public function make(&$object, string $fQCN): ComponentResolverInterface;

    /**
     * Searches the component and all parent components for the parameter. If none
     * holds it looks up the global namespace. The result is passed to the `$param`
     * variable.
     *
     * @param mixed  $param
     * @param string $key
     *
     * @return ComponentResolverInterface The current component object.
     *
     * @throws NotFoundException The Parameter is not set.
     */
    public function param(&$param, string $key): ComponentResolverInterface;
}
