<?php

namespace RebelCode\State\UnitTest;

use Dhii\Events\TransitionEventInterface;
use Dhii\Util\String\StringableInterface;
use Exception;
use PHPUnit_Framework_MockObject_MockObject;
use Xpmock\TestCase;

/**
 * Tests {@see RebelCode\State\AbstractEventStateMachine}.
 *
 * @since [*next-version*]
 */
class AbstractEventStateMachineTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\State\AbstractEventStateMachine';

    /**
     * The FQN of the event manager interface.
     *
     * @since [*next-version*]
     */
    const EVENT_MANAGER_INTERFACE = 'Psr\EventManager\EventManagerInterface';

    /**
     * The FQN of the transition event interface.
     *
     * @since [*next-version*]
     */
    const TRANSITION_EVENT_INTERFACE = 'Dhii\Events\TransitionEventInterface';

    /**
     * The FQN of the readable state machine interface.
     *
     * @since [*next-version*]
     */
    const READABLE_STATE_MACHINE_INTERFACE = 'Dhii\State\ReadableStateMachineInterface';

    /**
     * The FQN of the stringable interface.
     *
     * @since [*next-version*]
     */
    const STRINGABLE_INTERFACE = 'Dhii\Util\String\StringableInterface';

    /**
     * Creates a new instance of the test subject.
     *
     * @since [*next-version*]
     *
     * @param array $methods The methods to mock.
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    public function createInstance(array $methods = [])
    {
        $mock = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
                     ->disableOriginalConstructor();

        if (!empty($methods)) {
            $mock->setMethods($methods);
        }

        return $mock->getMockForAbstractClass();
    }

    /**
     * Creates an event manager mock instance for testing purposes.
     *
     * @since [*next-version*]
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    public function createEventManager()
    {
        $mock = $this->getMockBuilder(static::EVENT_MANAGER_INTERFACE)
                     ->setMethods(
                         [
                             'attach',
                             'detach',
                             'trigger',
                             'clearListeners',
                         ]
                     );

        return $mock->getMock();
    }

    /**
     * Creates a transition event instance for testing purposes.
     *
     * @since [*next-version*]
     *
     * @param string     $name    The event name.
     * @param array      $params  The event params.
     * @param mixed|null $target  The event target, for context.
     * @param bool       $aborted The aborted flag.
     *
     * @return TransitionEventInterface The created transition event instance.
     */
    public function createTransitionEvent($name = '', $params = [], $target = null, $aborted = false)
    {
        return $this->mock(static::TRANSITION_EVENT_INTERFACE)
                    ->getName($name)
                    ->setName()
                    ->getParams($params)
                    ->setParams()
                    ->getParam(
                        function($key) use ($params) {
                            return array_key_exists($key, $params)
                                ? $params[$key]
                                : null;
                        }
                    )
                    ->getTarget($target)
                    ->setTarget()
                    ->getTransition()
                    ->stopPropagation()
                    ->isPropagationStopped()
                    ->abortTransition()
                    ->isTransitionAborted($aborted)
                    ->new();
    }

    /**
     * Creates a mock stringable instance for testing purposes.
     *
     * @since [*next-version*]
     *
     * @param string $string The string to return when the object is casted to string.
     *
     * @return StringableInterface
     */
    public function createStringable($string)
    {
        return $this->mock(static::STRINGABLE_INTERFACE)
                    ->__toString($string)
                    ->new();
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
     * Tests the transition method to ensure that the abstracted algorithm works as expected.
     *
     * @since [*next-version*]
     */
    public function testTransition()
    {
        $subject    = $this->createInstance();
        $reflect    = $this->reflect($subject);
        $transition = uniqid('transition-');

        // Expect subject will determine event via _getTransitionEvent(), mock result
        $event = $this->createTransitionEvent($transition, [], null, false);
        $subject->expects($this->once())
                ->method('_getTransitionEvent')
                ->with($transition)
                ->willReturn($event);

        // Expect subject to retrieve the event manager, mock result
        $evtManager = $this->createEventManager();
        $subject->expects($this->once())
                ->method('_getEventManager')
                ->willReturn($evtManager);

        // Expect subject to determine new state via _getNewState(), mock result
        $newState = uniqid('new-state-');
        $subject->expects($this->once())
                ->method('_getNewState')
                ->with($event)
                ->willReturn($newState);

        // Expect subject to set the state using the mocked result for _getNewState()
        $subject->expects($this->once())
                ->method('_setState')
                ->with($newState);

        // Expect subject to invoke event manager's trigger method.
        $evtManager->expects($this->once())
                   ->method('trigger')
                   ->with($event);

        $reflect->_transition($transition);
    }

    /**
     * Tests the transition method with an aborted transition to ensure that the new state is not set.
     *
     * @since [*next-version*]
     */
    public function testTransitionAborted()
    {
        $subject    = $this->createInstance();
        $reflect    = $this->reflect($subject);
        $transition = uniqid('transition-');

        // Expect subject will determine event via _getTransitionEvent(), mock result
        $event = $this->createTransitionEvent($transition, [], null, true);
        $subject->expects($this->once())
                ->method('_getTransitionEvent')
                ->with($transition)
                ->willReturn($event);

        // Expect subject to retrieve the event manager, mock result
        $evtManager = $this->createEventManager();
        $subject->expects($this->once())
                ->method('_getEventManager')
                ->willReturn($evtManager);

        // Expect subject to NOT determine new state via _getNewState(), mock result
        $newState = uniqid('new-state-');
        $subject->expects($this->never())
                ->method('_getNewState')
                ->with($this->anything())
                ->willReturn($newState);

        // Expect subject to NOT set the state using the mocked result for _getNewState()
        $subject->expects($this->never())
                ->method('_setState')
                ->with($newState);

        // Expect subject to invoke event manager's trigger method.
        $evtManager->expects($this->once())
                   ->method('trigger')
                   ->with($event);

        $subject->expects($this->once())
            ->method('_createCouldNotTransitionException')
            ->with($this->anything(), $this->anything(), $this->anything(), $transition)
            ->willReturn(new Exception());

        $this->setExpectedException('Exception');

        $reflect->_transition($transition);
    }

    /**
     * Tests the transition method when an exception is thrown by an event handler to ensure that the state
     * is still updated.
     *
     * @since [*next-version*]
     */
    public function testTransitionException()
    {
        $subject    = $this->createInstance();
        $reflect    = $this->reflect($subject);
        $transition = uniqid('transition-');

        // Expect subject will determine event via _getTransitionEvent(), mock result
        $event = $this->createTransitionEvent($transition, [], null, false);
        $subject->expects($this->once())
                ->method('_getTransitionEvent')
                ->with($transition)
                ->willReturn($event);

        // Expect subject to retrieve the event manager, mock result
        $evtManager = $this->createEventManager();
        $subject->expects($this->once())
                ->method('_getEventManager')
                ->willReturn($evtManager);

        // Expect subject to determine new state via _getNewState(), mock result
        $newState = uniqid('new-state-');
        $subject->expects($this->once())
                ->method('_getNewState')
                ->with($this->anything())
                ->willReturn($newState);

        // Expect subject to set the state using the mocked result for _getNewState()
        $subject->expects($this->once())
                ->method('_setState')
                ->with($newState);

        // Expect subject to call the exception factory, mock result
        $subject->expects($this->once())
                ->method('_createStateMachineException')
                ->willReturn(new Exception());

        // Expect subject to invoke event manager's trigger method, which will throw an exception
        $evtManager->expects($this->once())
                   ->method('trigger')
                   ->willThrowException(new Exception());

        $this->setExpectedException('Exception');

        $reflect->_transition($transition);
    }

    /**
     * Tests the transition method with an aborted transition and a thrown exception to ensure that the
     * new state is not set.
     *
     * @since [*next-version*]
     */
    public function testTransitionExceptionAborted()
    {
        $subject    = $this->createInstance();
        $reflect    = $this->reflect($subject);
        $transition = uniqid('transition-');

        // Expect subject will determine event via _getTransitionEvent(), mock result
        $event = $this->createTransitionEvent($transition, [], null, true);
        $subject->expects($this->once())
                ->method('_getTransitionEvent')
                ->with($transition)
                ->willReturn($event);

        // Expect subject to retrieve the event manager, mock result
        $evtManager = $this->createEventManager();
        $subject->expects($this->once())
                ->method('_getEventManager')
                ->willReturn($evtManager);

        // Expect subject to NOT determine new state via _getNewState(), mock result
        $newState = uniqid('new-state-');
        $subject->expects($this->never())
                ->method('_getNewState')
                ->with($this->anything())
                ->willReturn($newState);

        // Expect subject to NOT set the state using the mocked result for _getNewState()
        $subject->expects($this->never())
                ->method('_setState')
                ->with($newState);

        // Expect subject to call the exception factory, mock result
        $subject->expects($this->once())
                ->method('_createCouldNotTransitionException')
                ->willReturn(new Exception());

        // Expect subject to invoke event manager's trigger method, which will throw an exception
        $evtManager->expects($this->once())
                   ->method('trigger')
                   ->willThrowException(new Exception());

        $this->setExpectedException('Exception');

        $reflect->_transition($transition);
    }
}
