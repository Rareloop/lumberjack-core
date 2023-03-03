<?php

namespace Rareloop\Lumberjack\Test;

use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\ViewModel;

class ViewModelTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /** @test */
    public function public_methods_are_serialised_by_to_array()
    {
        $viewModel = new TestViewModel();
        $data = $viewModel->toArray();

        $this->assertSame('bar', $data['foo']);
        $this->assertFalse(isset($data['toArray']));
        $this->assertFalse(isset($data['protectedFoo']));
        $this->assertFalse(isset($data['privateFoo']));
    }

    /** @test */
    public function public_methods_with_params_are_not_serialised_by_to_array()
    {
        $viewModel = new TestMethodWithParamsViewModel();
        $data = $viewModel->toArray();

        $this->assertFalse(isset($data['foo']));
    }

    /** @test */
    public function static_public_methods_are_not_serialised_by_to_array()
    {
        $viewModel = new TestStaticMethodViewModel();
        $data = $viewModel->toArray();

        $this->assertSame('bar', $data['foo']);
        $this->assertFalse(isset($data['staticFoo']));
    }

    /** @test */
    public function public_properties_are_serialised_by_to_array()
    {
        $viewModel = new TestPropertiesViewModel();
        $data = $viewModel->toArray();

        $this->assertSame('bar', $data['foo']);
        $this->assertFalse(isset($data['toArray']));
        $this->assertFalse(isset($data['protectedFoo']));
        $this->assertFalse(isset($data['privateFoo']));
    }

    /** @test */
    public function static_public_properties_are_not_serialised_by_to_array()
    {
        $viewModel = new TestStaticPropertiesViewModel();
        $data = $viewModel->toArray();

        $this->assertSame('bar', $data['foo']);
        $this->assertFalse(isset($data['staticFoo']));
    }
}

class TestViewModel extends ViewModel
{
    public function foo()
    {
        return 'bar';
    }

    protected function protectedFoo()
    {
        return 'protected-bar';
    }

    private function privateFoo()
    {
        return 'private-bar';
    }
}

class TestMethodWithParamsViewModel extends ViewModel
{
    public function foo($param)
    {
        return 'bar';
    }
}

class TestStaticMethodViewModel extends ViewModel
{
    public function foo()
    {
        return 'bar';
    }

    public static function staticFoo()
    {
        return 'static-bar';
    }
}

class TestPropertiesViewModel extends ViewModel
{
    public $foo = 'bar';
    private $privateFoo = 'private-foo';
    protected $protectedFoo = 'protected-foo';
}

class TestStaticPropertiesViewModel extends ViewModel
{
    public $foo = 'bar';
    public static $staticFoo = 'static-bar';
}
