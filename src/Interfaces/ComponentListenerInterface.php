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

use eArc\Observer\Interfaces\ListenerInterface;

/**
 * Listener interface for the listeners holding the components dependency
 * definitions.
 */
interface ComponentListenerInterface extends ListenerInterface
{
    /**
     * Get an array of the observer tree names related to the component
     * dependencies.
     *
     * @return string[]
     */
    public static function getComponentDependencies(): array;
}
