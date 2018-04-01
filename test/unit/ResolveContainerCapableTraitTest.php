<?php

namespace Dhii\Di\UnitTest;

use Dhii\Data\Container\ContainerAwareInterface;
use Dhii\Data\Container\ResolveContainerCapableTrait as TestSubject;
use Psr\Container\ContainerInterface;
use Xpmock\TestCase;
use Exception as RootException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit_Framework_MockObject_MockBuilder as MockBuilder;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class ResolveContainerCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'Dhii\Data\Container\ResolveContainerCapableTrait';

    /**
     * Creates a new instance of the test subject.
     *
     * @since [*next-version*]
     *
     * @param array $methods The methods to mock.
     *
     * @return MockObject The new instance.
     */
    public function createInstance($methods = [])
    {
        is_array($methods) && $methods = $this->mergeValues($methods, [
            '__',
        ]);

        $mock = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
            ->setMethods($methods)
            ->getMockForTrait();

        $mock->method('__')
                ->will($this->returnArgument(0));

        return $mock;
    }

    /**
     * Merges the values of two arrays.
     *
     * The resulting product will be a numeric array where the values of both inputs are present, without duplicates.
     *
     * @since [*next-version*]
     *
     * @param array $destination The base array.
     * @param array $source      The array with more keys.
     *
     * @return array The array which contains unique values
     */
    public function mergeValues($destination, $source)
    {
        return array_keys(array_merge(array_flip($destination), array_flip($source)));
    }

    /**
     * Creates a mock that both extends a class and implements interfaces.
     *
     * This is particularly useful for cases where the mock is based on an
     * internal class, such as in the case with exceptions. Helps to avoid
     * writing hard-coded stubs.
     *
     * @since [*next-version*]
     *
     * @param string   $className      Name of the class for the mock to extend.
     * @param string[] $interfaceNames Names of the interfaces for the mock to implement.
     *
     * @return MockBuilder The builder for a mock of an object that extends and implements
     *                     the specified class and interfaces.
     */
    public function mockClassAndInterfaces($className, $interfaceNames = [])
    {
        $paddingClassName = uniqid($className);
        $definition = vsprintf('abstract class %1$s extends %2$s implements %3$s {}', [
            $paddingClassName,
            $className,
            implode(', ', $interfaceNames),
        ]);
        eval($definition);

        return $this->getMockBuilder($paddingClassName);
    }

    /**
     * Creates a mock that uses traits.
     *
     * This is particularly useful for testing integration between multiple traits.
     *
     * @since [*next-version*]
     *
     * @param string[] $traitNames Names of the traits for the mock to use.
     *
     * @return MockBuilder The builder for a mock of an object that uses the traits.
     */
    public function mockTraits($traitNames = [])
    {
        $paddingClassName = uniqid('Traits');
        $definition = vsprintf('abstract class %1$s {%2$s}', [
            $paddingClassName,
            implode(
                ' ',
                array_map(
                    function ($v) {
                        return vsprintf('use %1$s;', [$v]);
                    },
                    $traitNames)),
        ]);
        var_dump($definition);
        eval($definition);

        return $this->getMockBuilder($paddingClassName);
    }

    /**
     * Creates a new exception.
     *
     * @since [*next-version*]
     *
     * @param string $message The exception message.
     *
     * @return RootException|MockObject The new exception.
     */
    public function createException($message = '')
    {
        $mock = $this->getMockBuilder('Exception')
            ->setConstructorArgs([$message])
            ->getMock();

        return $mock;
    }

    /**
     * Creates a mew container.
     *
     * @since [*next-version*]
     *
     * @param array|null $methods The methods to mock.
     *
     * @return MockObject|ContainerInterface The new container.
     */
    public function createContainer($methods = [])
    {
        is_array($methods) && $methods = $this->mergeValues($methods, [
            '__',
        ]);

        $mock = $this->getMockBuilder('Psr\Container\ContainerInterface')
            ->setMethods($methods)
            ->getMock();

        return $mock;
    }

    /**
     * Creates a new container-aware container.
     *
     * @since [*next-version*]
     *
     * @param array|null $methods The methods to mock.
     *
     * @return MockObject|ContainerInterface|ContainerAwareInterface The new container-aware container.
     */
    public function createContainerAwareContainer($methods = [])
    {
        is_array($methods) && $methods = $this->mergeValues($methods, [
            '__',
        ]);

        $mock = $this->mockClassAndInterfaces('ArrayObject', ['Dhii\Data\Container\ContainerAwareInterface', 'Psr\Container\ContainerInterface'])
            ->setMethods($methods)
            ->getMock();

        return $mock;
    }

    /**
     * Tests whether a valid instance of the test subject can be created.
     *
     * @since [*next-version*]
     */
    public function testCanBeCreated()
    {
        $subject = $this->createInstance();

        $this->assertInternalType(
            'object',
            $subject,
            'A valid instance of the test subject could not be created.'
        );
    }

    /**
     * Tests that `_resolveContainer()` works as expected when reaching a container without an inner container.
     *
     * @since [*next-version*]
     */
    public function testAssignDefinitionContainerWholeChain()
    {
        $container1 = $this->createContainerAwareContainer(['getContainer', 'get', 'has']);
        $container2 = $this->createContainerAwareContainer(['getContainer', 'get', 'has']);
        $container3 = $this->createContainerAwareContainer(['getContainer', 'get', 'has']);
        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);

        $container1->expects($this->exactly(1))
            ->method('getContainer')
            ->will($this->returnValue($container2));
        $container2->expects($this->exactly(1))
            ->method('getContainer')
            ->will($this->returnValue($container3));
        $container3->expects($this->exactly(1))
            ->method('getContainer')
            ->will($this->returnValue(null));

        $result = $_subject->_resolveContainer($container1);
        $this->assertSame($container3, $result, 'Wrong resolution result');
    }

    /**
     * Tests that `_resolveContainer()` works as expected when reaching a container that is not container-aware.
     *
     * @since [*next-version*]
     */
    public function testAssignDefinitionContainerNonContainerAware()
    {
        $container1 = $this->createContainerAwareContainer(['getContainer', 'get', 'has']);
        $container2 = $this->createContainer(['get', 'has']);
        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);

        $container1->expects($this->exactly(1))
            ->method('getContainer')
            ->will($this->returnValue($container2));

        $result = $_subject->_resolveContainer($container1);
        $this->assertSame($container2, $result, 'Wrong resolution result');
    }
}
