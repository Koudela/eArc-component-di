<?php
/**
 * e-Arc Framework - the explicit Architecture Framework
 *
 * @package earc/component-di
 * @link https://github.com/Koudela/eArc-component-di/
 * @copyright Copyright (c) 2018 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\ComponentDI\Exceptions;

use Psr\Container\NotFoundExceptionInterface;
use Throwable;

class NoSuchComponentException extends \RuntimeException implements NotFoundExceptionInterface
{
    public function __construct(string $componentName = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct("No component named `$componentName` exists.", $code, $previous);
    }
}