<?php

namespace DI\Test\IntegrationTest;

use DI\Container;
use DI\Definitions\ClosureDefinition;
use DI\Definitions\EntryReference;
use DI\Definitions\ObjectDefinition;
use DI\Exception\NotFoundException;
use DI\Test\IntegrationTest\Fixtures\Foo;
use DI\Test\IntegrationTest\Fixtures\Person;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ContainerTest extends TestCase
{
    public function test_get_404()
    {
        $this->expectException(NotFoundException::class);

        $container = new Container();
        $container->get('not_found_key');
    }

    public function test_get()
    {
        $container = new Container();
        $container->set('key1', 'val1');

        $this->assertSame('val1', $container->get('key1'));
    }

    public function test_make()
    {
        $container = new Container();
        $container->set('name', 'George');
        $container->set('age', 18);
        $container->set('sex', 'male');
        $container->set(
            (new ObjectDefinition(Person::class, false))
                ->setConstructParameters(['name' => 'Bob', 'age' => new EntryReference('age')])
                ->setProperty('sex', new EntryReference('sex'))
                ->setMethodParameters('desc', ['level' => 'warning'])
                ->setMethodParameters('say', ['word' => 'Hello world!'])
        );
        $container->set(new ObjectDefinition(Foo::class));
        /**
         * @var Person $object
         */
        $object = $container->make(
            Person::class,
            [
                'name' => new EntryReference('name'),
            ]
        );

        $this->assertSame(
            'name: George, age: 18, bar: bar1, sex: male, level: warning',
            $container->call([$object, 'desc'])
        );

        $this->assertEquals('the person say: Hello world!', $container->call([$object, 'say'], []));

        $this->assertEquals(
            'the person say: hey Bro~',
            $container->call([Person::class, 'say'], ['word' => 'hey Bro~'])
        );

        $this->assertSame(
            'name: Bob, age: 18, bar: bar1, sex: male, level: notice',
            $container->call([Person::class, 'desc'], ['level' => 'notice'])
        );
    }

    public function test_getObject()
    {
        $container = new Container();
        $container->set('name', 'George');
        $container->set('age', 18);
        $container->set('sex', 'male');
        $container->set(
            (new ObjectDefinition(Person::class, true))
                ->setConstructParameters(['name' => 'Bob', 'age' => new EntryReference('age')])
                ->setProperty('sex', new EntryReference('sex'))
                ->setMethodParameters('desc', ['level' => 'warning'])
                ->setMethodParameters('say', ['word' => 'Hello world!'])
        );
        $container->set(new ObjectDefinition(Foo::class));
        /**
         * @var Person $object
         */
        $object = $container->get(Person::class);
        $this->assertSame(
            'name: Bob, age: 18, bar: bar1, sex: male, level: warning',
            $container->call([$object, 'desc'])
        );

        $this->assertSame(
            'name: Bob, age: 18, bar: bar1, sex: male, level: notice',
            $container->call([Person::class, 'desc'], ['level' => 'notice'])
        );
    }

    public function test_callClosure()
    {
        $container = new Container();
        $container->set('sex', 'male');
        $container->set(
            (new ClosureDefinition(
                'test_print',
                function (ContainerInterface $container, $name, $age = 18) {
                    return "name: $name, age: $age, sex: ".$container->get('sex');
                },
                false
            ))
                ->setParameter('age', 28)
                ->setParameter('name', 'Bob')
        );

        $this->assertSame(
            'name: Bob, age: 28, sex: male',
            $container->call('test_print')
        );

        $this->assertSame(
            'name: Bob, age: 38, sex: male',
            $container->call('test_print', ['age' => 38])
        );

        $this->assertSame(
            'name: Bob, age: 38, sex: male',
            $container->call($container->get('test_print'), ['name' => 'Bob', 'container' => $container, 'age' => 38])
        );
    }
}