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
use eArc\DI\Exceptions\DIException;
use eArc\DI\Exceptions\NotFoundDIException;

/**
 * Interface of the component builder.
 */
interface ComponentInterface
{
    const PUBLIC = 0;
    const PROTECTED = 1;
    const PRIVATE = 2;

    /**
     * Returns the short name used in the parameter context of a component.
     *
     * @param string $fQCNComponent The fully qualified class name of the component.
     *
     * @return string
     */
    public static function getShortName(string $fQCNComponent): string;

    /**
     * Checks the class identifier against the current component. On success it passes
     * the returned object of the function `di_get($fQCN)` to the `$class` variable.
     * Otherwise it throws an AccessDeniedException.
     *
     * @param mixed  $class The variable to pass the classes instance.
     * @param string $fQCN The identifier of the class.
     *
     * @return ComponentInterface The current component object.
     *
     * @throws AccessDeniedException The current component has no access to the class.
     * @throws DIException `di_get($fQCN)` has thrown an error.
     */
    public function get(&$class, string $fQCN): ComponentInterface;

    /**
     * As `get`, but it does not throw an AccessDeniedException if the class is
     * not registered to any component.
     *
     * @param mixed  $class The variable to pass the classes instance.
     * @param string $fQCN The identifier of the class.
     *
     * @return ComponentInterface The current component object.
     *
     * @throws AccessDeniedException The current component has no access to the class.
     * @throws DIException `di_get($fQCN)` has thrown an error.
     */
    public function getUnregistered(&$class, string $fQCN): ComponentInterface;

    /**
     * Checks the class identifier against the current component. On success it passes
     * the returned object of the function `di_make($fQCN)` to the `$class` variable.
     * Otherwise it throws an AccessDeniedException.
     *
     * @param mixed  $class The variable to pass the classes instance.
     * @param string $fQCN The identifier of the class.
     *
     * @return ComponentInterface The current component object.
     *
     * @throws AccessDeniedException The current component has no access to the class.
     * @throws DIException `di_make($fQCN)` has thrown an error.
     */
    public function make(&$class, string $fQCN): ComponentInterface;

    /**
     * As `make`, but it does not throw an AccessDeniedException if the class is
     * not registered to any component.
     *
     * @param mixed  $class The variable to pass the classes instance.
     * @param string $fQCN The identifier of the class.
     *
     * @return ComponentInterface The current component object.
     *
     * @throws AccessDeniedException The current component has no access to the class.
     * @throws DIException `di_make($fQCN)` has thrown an error.
     */
    public function makeUnregistered(&$class, string $fQCN): ComponentInterface;

    /**
     * Searches the component and all parent components for the parameter. If none
     * holds it looks up the global namespace. If a parameter is found it is passed
     * to the `$param` variable otherwise a NotFoundDIException is thrown.
     *
     * @param mixed  $param
     * @param string $key
     *
     * @return ComponentInterface The current component object.
     *
     * @throws NotFoundDIException The parameter is not set.
     */
    public function param(&$param, string $key): ComponentInterface;
}
