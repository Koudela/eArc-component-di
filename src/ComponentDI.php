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

    use eArc\ComponentDI\CoObjects\Component;
    use eArc\DI\CoObjects\DependencyResolver;
    use eArc\DI\CoObjects\ParameterBag;
    use BootstrapEArcDIComponent;
    use eArc\DI\DI;
    use eArc\DI\Exceptions\DIException;

    abstract class ComponentDI
    {
        /**
         * @param string $component
         * @param string $resolver
         * @param string $parameterBag
         *
         * @throws DIException
         */
        public static function init(string $component=Component::class, string $resolver=DependencyResolver::class, string $parameterBag=ParameterBag::class)
        {
            DI::init($resolver, $parameterBag);

            BootstrapEArcDIComponent::init($component);

        }
    }
}

namespace {

    use eArc\ComponentDI\Exceptions\NoSuchComponentException;
    use eArc\ComponentDI\Interfaces\ComponentInterface;
    use eArc\ComponentDI\CoObjects\Component;
    use eArc\DI\Exceptions\DIException;

    abstract class BootstrapEArcDIComponent
    {
        /** @var ComponentInterface */
        protected static $component;

        public static function getComponent()
        {
            return self::$component;
        }

        /**
         * @param string $component
         *
         * @throws DIException
         */
        public static function init(string $component=Component::class)
        {
            if (!is_subclass_of($component, ComponentInterface::class)) {
                throw new DIException(sprintf('Component has to implement %s.', ComponentInterface::class));
            }

            self::$component = $component;

            if (!function_exists('di_component')) {
                /**
                 * Registers a class to a component and returns the corresponding ComponentInterface.
                 *
                 * @param string $fQCNComponent The fully qualified class name of the component.
                 * @param string $fQCN The fully qualified class name of the current class.
                 * @param int $type The access type of the current class.
                 *
                 * @return ComponentInterface
                 *
                 * @throws NoSuchComponentException
                 */
                function di_component(string $fQCNComponent, string $fQCN, int $type = ComponentInterface::PROTECTED): ComponentInterface
                {
                    $component = BootstrapEArcDIComponent::getComponent();

                    return new $component($fQCNComponent, $fQCN, $type);
                }
            }
        }
    }
}
