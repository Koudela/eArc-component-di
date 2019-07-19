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

namespace eArc\ComponentDI;

/**
 * All components inherit direct or indirect from the root component.
 */
class RootComponent
{
    public static function getShortName(): string
    {
        static $shortName;

        if (!isset($shortName)) {
            $pos = strrpos(static::class, '\\');
            $shortName = strtolower(substr(static::class, false === $pos ? 0 : $pos + 1));
        }

        return $shortName;
    }
}
