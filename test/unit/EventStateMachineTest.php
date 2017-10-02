<?php

namespace RebelCode\State\UnitTest;

use Dhii\Util\String\StringableInterface;
use Exception;
use PHPUnit_Framework_MockObject_MockObject;
use RebelCode\State\EventStateMachine as TestSubject;
use stdClass;
use Xpmock\TestCase;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class EventStateMachineTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\State\EventStateMachine';

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
     * @param array $cArgs   The constructor args.
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

    public function createTransitionEvent($name = '', $params = [], $target = null, $aborted = false)
    {
        return $this->mock(static::TRANSITION_EVENT_INTERFACE)
                    ->getName($name)
                    ->setName()
                    ->getParams($params)
                    ->setParams()
                    ->getParam(
                        function ($key) use ($params) {
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

        $this->assertInstanceOf(
            static::READABLE_STATE_MACHINE_INTERFACE,
            $subject,
            'Test subject does not implement parent interface.'
        );
    }

    /**
     * Tests the constructor to ensure that all arguments are properly handled.
     *
     * @since [*next-version*]
     */
    public function testConstructor()
    {
        $evntMgr = $this->createEventManager();
        $state = uniqid('state-');
        $format = uniqid('%s-');
        $target = new stdClass();

        $subject = $this->createInstance([$evntMgr, $state, $format, $target]);
        $reflect = $this->reflect($subject);

        $this->assertSame(
            $evntMgr,
            $reflect->_getEventManager(),
            'Set and retrieved event managers are not the same.'
        );
        $this->assertSame(
            $state,
            $subject->getState(),
            'Set and retrieved initial states are not the same.'
        );
        $this->assertSame(
            $format,
            $reflect->_getEventNameFormat(),
            'Set and retrieved event name format are not the same.'
        );
        $this->assertSame(
            $target,
            $reflect->_getTarget(),
            'Set and retrieved event target are not the same.'
        );
    }

    /**
     * Tests the event manager getter and setter methods.
     *
     * @since [*next-version*]
     */
    public function testGetSetEventManager()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $reflect->_setEventManager($evtMngr = $this->createEventManager());

        $this->assertSame(
            $evtMngr,
            $reflect->_getEventManager(),
            'Set and retrieved target instances are not the same.'
        );
    }

    /**
     * Tests the event name format getter and setter methods.
     *
     * @since [*next-version*]
     */
    public function testGetSetEventNameFormat()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $reflect->_setEventNameFormat($format = uniqid('%s-'));

        $this->assertEquals(
            $format,
            $reflect->_getEventNameFormat(),
            'Set and retrieved event name formats are not equal.'
        );
    }

    /**
     * Tests the event name format getter and setter methods with a null value.
     *
     * @since [*next-version*]
     */
    public function testGetSetEventNameFormatNull()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $reflect->_setEventNameFormat(null);

        $this->assertEquals(
            TestSubject::DEFAULT_EVENT_NAME_FORMAT,
            $reflect->_getEventNameFormat(),
            'Set and retrieved event name formats are not equal.'
        );
    }

    /**
     * Tests the event name format getter and setter methods with an invalid value.
     *
     * @since [*next-version*]
     */
    public function testGetSetEventNameFormatInvalid()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $this->setExpectedException('Dhii\Exception\InvalidArgumentException');

        $reflect->_setEventNameFormat(new stdClass());
    }

    /**
     * Tests the event target getter and setter methods.
     *
     * @since [*next-version*]
     */
    public function testGetSetTarget()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $reflect->_setTarget($target = new stdClass());

        $this->assertSame(
            $target,
            $reflect->_getTarget(),
            'Set and retrieved target instances are not the same.'
        );
    }

    /**
     * Tests the string/stringable validation method.
     *
     * @since [*next-version*]
     */
    public function testIsValidString()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $string = uniqid('string-');
        $stringable = $this->createStringable(uniqid('string-'));
        $invalid = 108;
        $invalidObj = new \stdClass();

        $this->assertTrue($reflect->_isValidString($string), 'Strings should be valid.');
        $this->assertTrue($reflect->_isValidString($stringable), 'Stringable instances should be valid.');
        $this->assertFalse($reflect->_isValidString($invalid), 'Non-string values should be invalid');
        $this->assertFalse($reflect->_isValidString($invalidObj), 'Non-stringable instances should be invalid');
    }

    /**
     * Tests the transition event generator method.
     *
     * @since [*next-version*]
     */
    public function testCreateTransitionEvent()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $name = uniqid('event-');
        $transition = $this->createStringable(uniqid('transition-'));
        $target = new stdClass();
        $params = [
            'a' => 19,
            'b' => uniqid('b-'),
        ];

        $this->assertInstanceOf(
            static::TRANSITION_EVENT_INTERFACE,
            $event = $reflect->_createTransitionEvent($name, $transition, $target, $params),
            'Created event does not implement the transition-event interface'
        );

        $this->assertSame($name, $event->getName(), 'Event name is incorrect.');
        $this->assertSame($transition, $event->getTransition(), 'Event transition is incorrect.');
        $this->assertSame($target, $event->getTarget(), 'Event target is incorrect.');
        $this->assertSame($params, $event->getParams(), 'Event params are incorrect.');
    }

    /**
     * Tests the event parameters getter method.
     *
     * @since [*next-version*]
     */
    public function testGetEventParams()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $params = $reflect->_getEventParams(uniqid('transition-'));

        $this->assertArrayHasKey(TestSubject::K_PARAM_NEW_STATE, $params);
        $this->assertArrayHasKey(TestSubject::K_PARAM_CURRENT_STATE, $params);
    }

    /**
     * Tests the event name generator method.
     *
     * @since [*next-version*]
     */
    public function testGenerateEventName()
    {
        // Create test subject
        $evtMngr = $this->createEventManager();
        $state = uniqid('state-');
        $format = uniqid('%s-');
        $subject = $this->createInstance([$evtMngr, $state, $format]);
        $reflect = $this->reflect($subject);

        $transition = uniqid('transition-');
        $expected = sprintf($format, $transition);

        $this->assertEquals(
            $expected,
            $reflect->_generateEventName($transition),
            'Generated event name does not match expected name.'
        );
    }

    /**
     * Tests the transition event getter method to ensure that the retrieved event has correct data.
     *
     * @since [*next-version*]
     */
    public function testGetTransitionEvent()
    {
        // Create test subject
        $evtMngr = $this->createEventManager();
        $state = uniqid('state-');
        $format = uniqid('%s-');
        $target = new stdClass();
        $subject = $this->createInstance([$evtMngr, $state, $format, $target], ['_getEventParams']);
        $reflect = $this->reflect($subject);

        // The transition
        $transition = uniqid('transition-');

        // Expect the event params method to be called
        $subject->expects($this->once())
                ->method('_getEventParams')
                ->with($transition)
                ->willReturn([]);

        $event = $reflect->_getTransitionEvent($transition);

        $this->assertInstanceOf(
            static::TRANSITION_EVENT_INTERFACE,
            $event,
            'Retrieved event does not implement the expected interface.'
        );
        $this->assertEquals(
            sprintf($format, $transition),
            $event->getName(),
            'Event name is incorrect.'
        );
        $this->assertSame(
            $transition,
            $event->getTransition(),
            'Event\'s transition and transition given to method are not the same.'
        );
        $this->assertSame(
            $target,
            $event->getTarget(),
            'Target set to instance and event\'s target are not the same.'
        );
    }

    /**
     * Tests the transition capability check method.
     *
     * @since [*next-version*]
     */
    public function testCanTransition()
    {
        $subject = $this->createInstance();

        $this->assertTrue($subject->canTransition(uniqid('state-')));
    }

    /**
     * Tests the transition method to ensure that the state in the returned machine is updated according to the event.
     *
     * @since [*next-version*]
     */
    public function testTransition()
    {
        // Create test subject
        $evtMngr = $this->createEventManager();
        $state = uniqid('state-');
        $format = uniqid('%s-');
        $target = new stdClass();
        $subject = $this->createInstance([$evtMngr, $state, $format, $target], ['_getTransitionEvent']);

        // Prepare transition event mock
        $transition = uniqid('transition-');
        $eventName = sprintf($format, $transition);
        $newState = uniqid('new-state-');
        $event = $this->createTransitionEvent($eventName, [TestSubject::K_PARAM_NEW_STATE => $newState]);

        // Mock transition event retrieval
        $subject->method('_getTransitionEvent')
                ->willReturn($event);

        // Expect event to be triggered by event manager
        $evtMngr->expects($this->once())
                ->method('trigger')
                ->with($event);

        $machine = $subject->transition($transition);

        $this->assertSame(
            $newState,
            $machine->getState(),
            'The machine\'s state was not correctly updated.'
        );
    }

    /**
     * Tests the transition method with an invalid new state in the event.
     *
     * @since [*next-version*]
     */
    public function testTransitionInvalidNewState()
    {
        // Create test subject
        $evtMngr = $this->createEventManager();
        $state = uniqid('state-');
        $format = uniqid('%s-');
        $target = new stdClass();
        $subject = $this->createInstance([$evtMngr, $state, $format, $target], ['_getTransitionEvent']);

        // Create transition event mock
        $transition = uniqid('transition-');
        $eventName = sprintf($format, $transition);
        $newState = new stdClass();
        $event = $this->createTransitionEvent($eventName, [TestSubject::K_PARAM_NEW_STATE => $newState]);

        // Mock transition event retrieval
        $subject->method('_getTransitionEvent')
                ->willReturn($event);

        // Expect exception since the new state is not valid
        $this->setExpectedException('Dhii\State\Exception\CouldNotTransitionExceptionInterface');

        $subject->transition($transition);
    }

    /**
     * Tests the transition method with an exception thrown by an event handler.
     *
     * @since [*next-version*]
     */
    public function testTransitionTriggerException()
    {
        // Create test subject
        $evtMngr = $this->createEventManager();
        $state = uniqid('state-');
        $format = uniqid('%s-');
        $target = new stdClass();
        $subject = $this->createInstance([$evtMngr, $state, $format, $target], ['_getTransitionEvent']);

        // Prepare transition event mock
        $transition = uniqid('transition-');
        $eventName = sprintf($format, $transition);
        $newState = uniqid('new-state-');
        $event = $this->createTransitionEvent($eventName, [TestSubject::K_PARAM_NEW_STATE => $newState]);

        // Mock transition event retrieval
        $subject->method('_getTransitionEvent')
                ->willReturn($event);

        // Mock event manager trigger() method to throw an exception
        $evtMngr->expects($this->once())
                ->method('trigger')
                ->willThrowException(new Exception());

        // Expect the exception to be wrapped in a could-not-transition exception
        $this->setExpectedException('Dhii\State\Exception\CouldNotTransitionExceptionInterface');

        $subject->transition($transition);
    }

    /**
     * Tests the transition method with an event that aborts the transition to ensure that the state does not change.
     *
     * @since [*next-version*]
     */
    public function testTransitionAbortTransition()
    {
        // Create test subject
        $evtMngr = $this->createEventManager();
        $state = uniqid('state-');
        $format = uniqid('%s-');
        $target = new stdClass();
        $subject = $this->createInstance([$evtMngr, $state, $format, $target], ['_getTransitionEvent']);

        // Mock transition event - will abort transition
        $transition = uniqid('transition-');
        $eventName = sprintf($format, $transition);
        $newState = uniqid('new-state-');
        $event = $this->createTransitionEvent(
            $eventName,
            [
                TestSubject::K_PARAM_NEW_STATE => $newState,
            ],
            null, // target
            true // aborted flag
        );

        // Mock transition event retrieval
        $subject->method('_getTransitionEvent')
                ->willReturn($event);

        $machine = $subject->transition($transition);

        $this->assertSame(
            $state,
            $machine->getState(),
            'State should not have been updated; the transition was aborted.'
        );
    }
}
