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

    use eArc\ComponentDI\CoObjects\Resolver;
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
        public static function init(string $component=Resolver::class, string $resolver=DependencyResolver::class, string $parameterBag=ParameterBag::class)
        {
            DI::init($resolver, $parameterBag);

            BootstrapEArcDIComponent::init($component);

        }
    }
}

namespace {

    use eArc\ComponentDI\Interfaces\ComponentResolverInterface;
    use eArc\ComponentDI\CoObjects\Resolver;
    use eArc\DI\Exceptions\DIException;

    abstract class BootstrapEArcDIComponent
    {
        /** @var ComponentResolverInterface */
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
        public static function init(string $component=Resolver::class)
        {
            if (!is_subclass_of($component, ComponentResolverInterface::class)) {
                throw new DIException(sprintf('Resolver has to implement %s.', ComponentResolverInterface::class));
            }

            self::$component = $component;

            if (!function_exists('di_comp_reg')) {
                function di_comp_reg(string $fQCNComponent, string $fQCN, int $type = ComponentResolverInterface::NO_SERVICE): ComponentResolverInterface
                {
                    return BootstrapEArcDIComponent::getComponent()::register($fQCNComponent, $fQCN, $type);
                }
            }

            if (!function_exists('di_comp_get')) {
                function di_comp_get(string $fQCN): ComponentResolverInterface
                {
                    return BootstrapEArcDIComponent::getComponent()::getRegistered($fQCN);
                }
            }

            if (!function_exists('di_comp_key')) {
                function di_comp_key(string $fQCNComponent): string
                {
                    return BootstrapEArcDIComponent::getComponent()::getKey($fQCNComponent);
                }
            }
        }
    }
}
