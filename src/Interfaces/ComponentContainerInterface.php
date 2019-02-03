<?php
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


use eArc\ComponentDI\Exceptions\CircularDependencyException;
use eArc\ComponentDI\Exceptions\NoSuchComponentException;
use eArc\DI\DependencyContainer;

interface ComponentContainerInterface
{
    /**
     * @param string $component
     *
     * @return DependencyContainer
     *
     * @throws NoSuchComponentException
     * @throws CircularDependencyException
     */
    public function get(string $component): DependencyContainer;
}
