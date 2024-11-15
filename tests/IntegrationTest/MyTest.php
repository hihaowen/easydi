<?php

namespace DI\Test\IntegrationTest;

use DI\Test\IntegrationTest\Fixtures\Foo;
use DI\Test\IntegrationTest\Fixtures\Person;
use PHPUnit\Framework\TestCase;

class MyTest extends TestCase
{
    public function test_function()
    {
        $function1 = function (Person $person) {
            return $person->desc();
        };

        $reflection = new \ReflectionFunction($function1);

        $reflectionParameters = $reflection->getParameters();

        var_dump($reflectionParameters);
    }

    public function test_object()
    {
        $className       = Person::class;
        $classReflection = new \ReflectionClass($className);

        // __construct injections
        $constructMethod = $classReflection->getConstructor();

        // TODO
        $constructParameters = [
            'foo'  => new Foo('bar'),
            'age'  => 18,
            'name' => 'George',
        ];

        $constructMethodArgs = $this->resolveParameters($constructMethod, $constructParameters);

        if (count($constructMethodArgs) > 0) {
            $object = $classReflection->newInstanceArgs($constructMethodArgs);
        } else {
            $object = new $className;
        }

        // Property injections
        $property1 = new \ReflectionProperty($className, 'name');
        if (!$property1->isPublic()) {
            $property1->setAccessible(true);
        }
        $property1->setValue($object, 'George2');

        // Method injections
        $methodName       = 'updateAge';
        $methodReflection = new \ReflectionMethod($object, $methodName);

        $methodReflection->invokeArgs($object, $this->resolveParameters($methodReflection, ['age' => 35]));

        $method = 'desc';

        if (!method_exists($object, $method)) {
            throw new \InvalidArgumentException('没该方法: '.$method);
        }

        $callable = [$object, $method];

        $this->assertSame('name: George2, age: 35, bar: bar, sex: , level: info', call_user_func_array($callable, []));
    }

    private function resolveParameters(
        \ReflectionMethod $method = null,
        array $parameters = []
    ) {
        $args = [];

        if (!$method) {
            return $args;
        }

        foreach ($method->getParameters() as $index => $parameter) {
            if (array_key_exists($parameter->getName(), $parameters)) {
                $args[] = &$parameters[$parameter->getName()];
            } elseif ($parameter->isOptional()) {
                try {
                    $defaultValue = $parameter->getDefaultValue();
                } catch (\ReflectionException $e) {
                    throw new \RuntimeException(
                        sprintf(
                            'The parameter "%s" of %s has no type defined or guessable. It has a default value, '
                            .'but the default value can\'t be read through Reflection because it is a PHP internal class.',
                            $parameter->getName(),
                            $method->getName().'()'
                        )
                    );
                }

                $args[] = $defaultValue;
            } else {
                throw new \RuntimeException(
                    sprintf(
                        'Parameter $%s of %s has no value defined or guessable',
                        $parameter->getName(),
                        $method->getName().'()'
                    )
                );
            }
        }

        return $args;
    }
}