<?php

namespace RebelCode\State\UnitTest;

use PHPUnit_Framework_MockObject_MockObject;
use stdClass;
use Xpmock\TestCase;
use RebelCode\State\TransitionEvent as TestSubject;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class TransitionEventTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\State\TransitionEvent';

    /**
     * Creates a new instance of the test subject.
     *
     * @since [*next-version*]
     *
     * @param array $cArgs   The constructor arguments.
     * @param array $methods The methods to mock.
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    public function createInstance(array $cArgs = [], array $methods = [])
    {
        $mock = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
                     ->disableOriginalConstructor();

        if (!empty($methods)) {
            $mock->setMethods($methods);
        }
        if (!empty($cArgs)) {
            $mock->enableOriginalConstructor()
                 ->setConstructorArgs($cArgs);
        }

        return $mock->getMockForAbstractClass();
    }

    /**
     * Tests whether a valid instance of the test subject can be created.
     *
     * @since [*next-version*]
     */
    public function testCanBeCreated()
    {
        $subject = $this->createInstance();

        $this->assertInstanceOf(
            static::TEST_SUBJECT_CLASSNAME,
            $subject,
            'A valid instance of the test subject could not be created.'
        );
    }

    /**
     * Tests the constructor to ensure that the arguments are correctly handled and that the instance is correctly
     * initialized.
     *
     * @since [*next-version*]
     */
    public function testConstructor()
    {
        $name = uniqid('name-');
        $transition = uniqid('transition-');
        $target = new stdClass();
        $params = [
            uniqid('key-') => uniqid('value-'),
        ];

        $subject = $this->createInstance([$name, $transition, $target, $params]);

        $this->assertSame($name, $subject->getName(), 'Set and retrieved names are not the same.');
        $this->assertSame($transition, $subject->getTransition(), 'Set and retrieved transitions are not the same.');
        $this->assertSame($target, $subject->getTarget(), 'Set and retrieved targets are not the same.');
        $this->assertSame($params, $subject->getParams(), 'Set and retrieved params are not the same.');
        $this->assertFalse($subject->isPropagationStopped(), 'Propagation should initially be false.');
    }

    /**
     * Tests the name getter and setter methods to ensure correct value assignment and retrieval.
     *
     * @since [*next-version*]
     */
    public function testGetSetName()
    {
        $subject = $this->createInstance();

        $subject->setName($name = uniqid('name-'));

        $this->assertSame($name, $subject->getName(), 'Set and retrieved names are not the same.');
    }

    /**
     * Tests the target getter and setter methods to ensure correct value assignment and retrieval.
     *
     * @since [*next-version*]
     */
    public function testGetSetTarget()
    {
        $subject = $this->createInstance();

        $subject->setTarget($target = new stdClass());

        $this->assertSame($target, $subject->getTarget(), 'Set and retrieved targets are not the same.');
    }

    /**
     * Tests the transition getter and setter methods to ensure correct value assignment and retrieval.
     *
     * @since [*next-version*]
     */
    public function testGetSetTransition()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $reflect->_setTransition($transition = uniqid('transition-'));

        $this->assertSame($transition, $subject->getTransition(), 'Set and retrieved transitions are not the same.');
    }

    /**
     * Tests the propagation getter and setter methods to ensure correct value assignment and retrieval.
     *
     * @since [*next-version*]
     */
    public function testSetIsPropagationStopped()
    {
        $subject = $this->createInstance();

        $subject->stopPropagation(true);
        $this->assertTrue($subject->isPropagationStopped(), 'Propagation should be stopped.');

        $subject->stopPropagation(false);
        $this->assertFalse($subject->isPropagationStopped(), 'Propagation should not be stopped.');
    }

    /**
     * Tests the params getter and setter methods to ensure correct value assignment and retrieval.
     *
     * @since [*next-version*]
     */
    public function testGetSetParams()
    {
        $subject = $this->createInstance();

        $subject->setParams(
            $params = [
                $key1 = uniqid('key1-') => $value1 = uniqid('value-1'),
                $key2 = uniqid('key2-') => $value2 = uniqid('value-2'),
                $key3 = uniqid('key3-') => $value3 = uniqid('value-3'),
            ]
        );
        $key4 = uniqid('key4-');

        $this->assertEquals(
            $params,
            $subject->getParams(),
            'Set and retrieved params are not the same.',
            0.0, // delta
            10,  // depth
            true // canonical flag
        );

        $this->assertEquals($value1, $subject->getParam($key1), 'Param value is incorrect.');
        $this->assertEquals($value2, $subject->getParam($key2), 'Param value is incorrect.');
        $this->assertEquals($value3, $subject->getParam($key3), 'Param value is incorrect.');
        $this->assertNull($subject->getParam($key4), 'Expected null value for non-existing param.');
    }

    /**
     * Tests the transition checker and setter methods to ensure correct value assignment and retrieval.
     *
     * @since [*next-version*]
     */
    public function testSetIsTransitionAborted()
    {
        $subject = $this->createInstance();

        $subject->abortTransition(true);
        $this->assertTrue($subject->isTransitionAborted(), 'Transition should be aborted.');

        $subject->abortTransition(false);
        $this->assertFalse($subject->isTransitionAborted(), 'Transition should not be aborted.');
    }
}
