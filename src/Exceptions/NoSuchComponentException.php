<?php

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