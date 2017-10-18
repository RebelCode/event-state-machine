<?php

namespace RebelCode\State\Exception\UnitTest;

use Dhii\State\StateMachineInterface;
use Exception;
use PHPUnit_Framework_MockObject_MockObject;
use Xpmock\TestCase;
use RebelCode\State\Exception\CouldNotTransitionException as TestSubject;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class CouldNotTransitionExceptionTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\State\Exception\CouldNotTransitionException';

    /**
     * Creates a new instance of the test subject.
     *
     * @since [*next-version*]
     *
     * @param array $cArgs   The constructor args.
     * @param array $methods The methods to mock.
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    public function createInstance(array $cArgs = [], array $methods = [])
    {
        $mock = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
                     ->disableOriginalConstructor();

        if (!empty($cArgs)) {
            $mock->enableOriginalConstructor()
                 ->setConstructorArgs($cArgs);
        }
        if (!empty($methods)) {
            $mock->setMethods($methods);
        }

        return $mock->getMockForAbstractClass();
    }

    /**
     * Creates a mocked state machine for testing purposes.
     *
     * @since [*next-version*]
     *
     * @return StateMachineInterface The state machine mock instance.
     */
    public function createStateMachine()
    {
        $mock = $this->mock('Dhii\State\StateMachineInterface')
                     ->transition()
                     ->canTransition();

        return $mock->new();
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
        $message = uniqid('message-');
        $code = rand(0, 100);
        $previous = new Exception();
        $stateMachine = $this->createStateMachine();
        $transition = uniqid('transition-');
        $subject = $this->createInstance([$message, $code, $previous, $stateMachine, $transition]);

        $this->assertSame(
            $message,
            $subject->getMessage(),
            'Set and retrieved messages are not the same.'
        );
        $this->assertSame(
            $code,
            $subject->getCode(),
            'Set and retrieved code are not the same.'
        );
        $this->assertSame(
            $previous,
            $subject->getPrevious(),
            'Set and retrieved previous exception are not the same.'
        );
        $this->assertSame(
            $stateMachine,
            $subject->getStateMachine(),
            'Set and retrieved state machines are not the same.'
        );
        $this->assertSame(
            $transition,
            $subject->getTransition(),
            'Set and retrieved transitions are not the same.'
        );
    }
}
