<?php declare(strict_types=1);

namespace bla\bla\blub;

use eArc\ComponentDI\RootComponent;
use eArc\ComponentDI\CoObjects\Component;
use eArc\ComponentDI\ComponentDI;
use eArc\DI\Exceptions\DIException;

require __DIR__ . '/../vendor/autoload.php';

class X extends RootComponent {}
class Y extends X {}
class z extends RootComponent {}

class A
{
    protected $p;

    /**
     * @throws DIException
     */
    public function __construct()
    {
        di_component(X::class, A::class, Component::PUBLIC)
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
}

class B extends A
{
    protected $a;

    public function __construct()
    {
        parent::__construct();

        di_component(z::class, B::class, Component::PUBLIC)
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
}

class C
{
    protected $a;
    protected $b;

    /**
     * @throws DIException
     */
    public function __construct()
    {
        di_component(X::class, C::class)
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
}

class D extends A
{
    public function sayHello() {
        echo "I decorate A\n";
    }
}
ComponentDI::init();
di_import_param([Component::getShortName(X::class) => ['p1' => 'Hase'], 'p2' => 'Igel']);
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
