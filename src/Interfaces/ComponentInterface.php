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

/**
 * Interface of the component builder.
 */
interface ComponentInterface
{
    const PUBLIC = 0;
    const PROTECTED = 1;
    const PRIVATE = 2;

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

    public function param(&$param, string $fQCN): ComponentInterface;
}
