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

    use eArc\ComponentDI\CoObjects\ComponentResolver;
    use eArc\DI\CoObjects\Resolver;
    use eArc\DI\CoObjects\ParameterBag;
    use BootstrapEArcDIComponent;
    use eArc\DI\DI;
    use eArc\DI\Exceptions\InvalidArgumentException;

    abstract class ComponentDI
    {
        /**
         * @param string $component
         * @param string $resolver
         * @param string $parameterBag
         *
         * @throws InvalidArgumentException
         */
        public static function init(string $component=ComponentResolver::class, string $resolver=Resolver::class, string $parameterBag=ParameterBag::class)
        {
            DI::init($resolver, $parameterBag);

            BootstrapEArcDIComponent::init($component);

        }
    }
}

namespace {

    use eArc\ComponentDI\Interfaces\ComponentResolverInterface;
    use eArc\ComponentDI\CoObjects\ComponentResolver;
    use eArc\ComponentDI\RootComponent;
    use eArc\DI\Exceptions\InvalidArgumentException;

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
         * @throws InvalidArgumentException
         */
        public static function init(string $component=ComponentResolver::class)
        {
            if (!is_subclass_of($component, ComponentResolverInterface::class)) {
                throw new InvalidArgumentException(sprintf('ComponentResolver has to implement %s.', ComponentResolverInterface::class));
            }

            self::$component = $component;

            if (!function_exists('di_comp')) {
                function di_comp(string $fQCN): ComponentResolverInterface
                {
                    return BootstrapEArcDIComponent::getComponent()::getComponentResolver($fQCN);
                }
            }

            if (!function_exists('di_comp_key')) {
                function di_comp_key(string $fQCNComponent): ?string
                {
                    /** @var RootComponent $fQCNComponent */
                    return is_subclass_of($fQCNComponent, RootComponent::class) ? $fQCNComponent::getShortName() : null;
                }
            }
        }
    }
}
