<?php

namespace Dhii\Data\Container\FuncTest;

use Xpmock\TestCase;
use InvalidArgumentException;
use Dhii\Data\Container\ContainerAwareTrait as TestSubject;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Dhii\Data\Container\ContainerInterface;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class ContainerAwareTraitTest extends TestCase
{
    /**
     * The name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'Dhii\Data\Container\ContainerAwareTrait';

    /**
     * Creates a new instance of the test subject.
     *
     * @since [*next-version*]
     *
     * @return MockObject The new instance of the test subject.
     */
    public function createInstance()
    {
        $mock = $this->getMockForTrait(static::TEST_SUBJECT_CLASSNAME);
        $mock->method('__')
                ->will($this->returnCallback(function ($string, $args) {
                    return vsprintf($string, $args);
                }));
        $mock->method('_createInvalidArgumentException')
                ->will($this->returnCallback(function ($message = '', $code = 0, $previous = null, $argument = null) {
                    return new InvalidArgumentException($message, $code, $previous);
                }));

        return $mock;
    }

    /**
     * Creates a new container.
     *
     * @since [*next-version*]
     *
     * @return ContainerInterface The new instance.
     */
    public function createContainer()
    {
        $container = $this->mock('Dhii\Data\Container\ContainerInterface')
                ->has()
                ->get()
                ->new();

        return $container;
    }

    /**
     * Tests whether a valid instance of the test subject can be created.
     *
     * @since [*next-version*]
     */
    public function testCanBeCreated()
    {
        $subject = $this->createInstance();

        $this->assertInternalType('object', $subject, 'A valid instance of the test subject could not be created');
    }

    /**
     * Tests whether a valid container can be successfully set and retrieved.
     *
     * @since [*next-version*]
     */
    public function testSetGetContainer()
    {
        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);
        $data = $this->createContainer();

        $_subject->_setContainer($data);

        $result = $_subject->_getContainer();

        $this->assertSame($data, $result, 'Container set is not the same as container retrieved');
    }

    /**
     * Tests whether an invalid container set attempt causes an appropriate exception to be thrown.
     *
     * @since [*next-version*]
     */
    public function testSetContainerFailure()
    {
        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);
        $data = new \stdClass();

        $this->setExpectedException('InvalidArgumentException');
        $_subject->_setContainer($data);
    }
}
