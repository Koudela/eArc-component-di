<?php declare(strict_types=1);

namespace bla\bla\blub;

use eArc\ComponentDI\Exceptions\AccessDeniedException;
use eArc\ComponentDI\Interfaces\Flags\PrivateServiceInterface;
use eArc\ComponentDI\Interfaces\Flags\ProtectedServiceInterface;
use eArc\ComponentDI\RootComponent;
use eArc\ComponentDI\ComponentDI;
use eArc\DI\Exceptions\InvalidArgumentException;
use eArc\DI\Exceptions\MakeClassException;
use eArc\DI\Exceptions\NotFoundException;

require __DIR__ . '/../vendor/autoload.php';

class X extends RootComponent {}
class Y extends X {}
class z extends RootComponent {}

class A implements PrivateServiceInterface
{
    protected $p;

    /**
     * @throws NotFoundException
     */
    public function __construct()
    {
        di_comp(static::class)
            ->param($this->p, 'p1');
    }

    public function sayHello()
    {
        echo "hello, I am A\n";
    }

    public function myParameter()
    {
        echo "my Parameter is {$this->p}!\n";
    }

    public static function getComponent(): string
    {
        return X::class;
    }
}

class B extends A implements PrivateServiceInterface
{
    protected $a;

    /**
     * @throws NotFoundException
     * @throws AccessDeniedException
     * @throws InvalidArgumentException
     * @throws MakeClassException
     */
    public function __construct()
    {
        parent::__construct();

        di_comp(static::class)
            ->get($this->a, A::class)
            ->param($this->p, 'p2');
    }

    public function getA()
    {
        return $this->a;
    }

    public function sayHello()
    {
        echo "hello, I am B\n";
    }

    public static function getComponent(): string
    {
        return Y::class;
    }
}

class C implements ProtectedServiceInterface
{
    protected $a;
    protected $b;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        di_comp(static::class)
            ->get($this->a, A::class)
            ->get($this->b, B::class);
    }

    public function getA()
    {
        return $this->a;
    }

    public function getB()
    {
        return $this->b;
    }

    public static function getComponent(): string
    {
        return Y::class;
    }

}

class D extends A
{
    public function sayHello() {
        echo "I decorate A\n";
    }
}

ComponentDI::init();

di_import_param([di_comp_key(X::class) => ['p1' => 'Hase'], 'p2' => 'Igel']);
$c = di_get(C::class);
$c->getB()->getA()->sayHello();
$c->getA()->sayHello();
$c->getB()->sayHello();
$c->getA()->myParameter();
di_decorate(A::class, D::class);
$c = di_make(C::class);
$c->getB()->getA()->sayHello();
$c->getA()->sayHello();
$c->getB()->sayHello();
$c->getB()->myParameter();
di_clear_cache(B::class);
$c = di_make(C::class);
$c->getB()->getA()->sayHello();
$c->getA()->sayHello();
$c->getB()->sayHello();
