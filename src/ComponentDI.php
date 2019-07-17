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

namespace eArc\ComponentDI {

    abstract class ComponentDI
    {
        public static function init()
        {
        }
    }
}

namespace {

    use eArc\ComponentDI\Exceptions\NoSuchComponentException;
    use eArc\ComponentDI\Interfaces\ComponentInterface;
    use eArc\ComponentDI\CoObjects\Component;
    use eArc\DI\DI;

    if (!function_exists('di_param')
        || !function_exists('di_has_param')
        || !function_exists('di_get')
        || !function_exists('di_make')) {
        DI::init();
    }

    if (!function_exists('di_component')) {
        /**
         * Registers a class to a component and returns the corresponding ComponentInterface..
         *
         * @param string $fQCNComponent
         * @param string $fQCN
         * @param int $type
         *
         * @return ComponentInterface
         *
         * @throws NoSuchComponentException
         */
        function di_component(string $fQCNComponent, string $fQCN, int $type=Component::PROTECTED): ComponentInterface
        {
            return new Component($fQCNComponent, $fQCN, $type);
        }
    }
}
