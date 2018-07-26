<?php

namespace RebelCode\State\FuncTest;

use Dhii\Event\EventFactoryInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Psr\EventManager\EventManagerInterface;
use RebelCode\State\EventStateMachine;
use RebelCode\State\EventStateMachineFactory;
use stdClass;
use Xpmock\TestCase;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class EventStateMachineFactoryTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\State\EventStateMachineFactory';

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
     * @return MockObject The object that extends and implements the specified class and interfaces.
     */
    public function mockClassAndInterfaces($className, $interfaceNames = [])
    {
        $paddingClassName = uniqid($className);
        $definition       = vsprintf(
            'abstract class %1$s extends %2$s implements %3$s {}',
            [
                $paddingClassName,
                $className,
                implode(', ', $interfaceNames),
            ]
        );
        eval($definition);

        return $this->getMockForAbstractClass($paddingClassName);
    }

    /**
     * Tests whether a valid instance of the test subject can be created.
     *
     * @since [*next-version*]
     */
    public function testCanBeCreated()
    {
        $subject = new EventStateMachineFactory();

        $this->assertInstanceOf(
            'Dhii\State\StateMachineFactoryInterface',
            $subject,
            'Test subject does not implement expected interface.'
        );
    }

    /**
     * Tests the factory to assert whether the created object is valid.
     *
     * @since [*next-version*]
     */
    public function testFactory()
    {
        $subject = new EventStateMachineFactory();

        $eventManager    = $this->getMock('Psr\EventManager\EventManagerInterface');
        $eventFactory    = $this->getMock('Dhii\Event\EventFactoryInterface');
        $eventNameFormat = uniqid('format-');
        $transitions     = [
            uniqid('state-') => [
                uniqid('transition-'),
                uniqid('transition-'),
            ],
            uniqid('state-') => [
                uniqid('transition-'),
            ],
        ];
        $target          = new stdClass();
        $initialState    = uniqid('state-');
        $params          = [
            uniqid('key-') => uniqid('val-'),
            uniqid('key-') => uniqid('val-'),
        ];

        /* @var $actual EventStateMachine */
        $actual = $subject->make(
            [
                EventStateMachineFactory::K_CFG_EVENT_TARGET      => $target,
                EventStateMachineFactory::K_CFG_EVENT_MANAGER     => $eventManager,
                EventStateMachineFactory::K_CFG_EVENT_FACTORY     => $eventFactory,
                EventStateMachineFactory::K_CFG_EVENT_NAME_FORMAT => $eventNameFormat,
                EventStateMachineFactory::K_CFG_TRANSITIONS       => $transitions,
                EventStateMachineFactory::K_CFG_INITIAL_STATE     => $initialState,
                EventStateMachineFactory::K_CFG_EVENT_PARAMS      => $params,
            ]
        );

        $this->assertInstanceOf(
            'RebelCode\State\EventStateMachine',
            $actual,
            'Created instance is not a valid event state machine instance.'
        );
        $this->assertEquals($initialState, $actual->getState(), 'Initial state is wrong.');
    }

    /**
     * Tests the factory to assert whether the created state machine uses the static event manager when not given
     * in the config.
     *
     * @since [*next-version*]
     */
    public function testFactoryStaticEventManager()
    {
        /* @var $eventManager EventManagerInterface|MockObject */
        $eventManager    = $this->getMock('Psr\EventManager\EventManagerInterface');
        $eventFactory    = $this->getMock('Dhii\Event\EventFactoryInterface');
        $eventNameFormat = uniqid('format-');
        $transitions     = [
            uniqid('state-') => [
                uniqid('transition-'),
                uniqid('transition-'),
            ],
            uniqid('state-') => [
                uniqid('transition-'),
            ],
        ];
        $target          = new stdClass();
        $initialState    = uniqid('state-');
        $params          = [
            uniqid('key-') => uniqid('val-'),
            uniqid('key-') => uniqid('val-'),
        ];

        $subject = new EventStateMachineFactory($eventManager);

        /* @var $actual EventStateMachine */
        $actual = $subject->make(
            [
                EventStateMachineFactory::K_CFG_EVENT_FACTORY     => $eventFactory,
                EventStateMachineFactory::K_CFG_EVENT_TARGET      => $target,
                EventStateMachineFactory::K_CFG_EVENT_NAME_FORMAT => $eventNameFormat,
                EventStateMachineFactory::K_CFG_TRANSITIONS       => $transitions,
                EventStateMachineFactory::K_CFG_INITIAL_STATE     => $initialState,
                EventStateMachineFactory::K_CFG_EVENT_PARAMS      => $params,
            ]
        );

        $this->assertInstanceOf(
            'RebelCode\State\EventStateMachine',
            $actual,
            'Created instance is not a valid event state machine instance.'
        );
        $this->assertSame($eventManager, $this->reflect($actual)->_getEventManager(), 'Event manager is wrong.');
    }

    /**
     * Tests the factory to assert whether the created state machine uses the static event factory when not given
     * in the config.
     *
     * @since [*next-version*]
     */
    public function testFactoryStaticEventFactory()
    {
        /* @var $eventManager EventManagerInterface|MockObject */
        $eventManager    = $this->getMock('Psr\EventManager\EventManagerInterface');
        /* @var $eventFactory EventFactoryInterface|MockObject */
        $eventFactory    = $this->getMock('Dhii\Event\EventFactoryInterface');
        $eventNameFormat = uniqid('format-');
        $transitions     = [
            uniqid('state-') => [
                uniqid('transition-'),
                uniqid('transition-'),
            ],
            uniqid('state-') => [
                uniqid('transition-'),
            ],
        ];
        $target          = new stdClass();
        $initialState    = uniqid('state-');
        $params          = [
            uniqid('key-') => uniqid('val-'),
            uniqid('key-') => uniqid('val-'),
        ];

        $subject = new EventStateMachineFactory(null, $eventFactory);

        /* @var $actual EventStateMachine */
        $actual = $subject->make(
            [
                EventStateMachineFactory::K_CFG_EVENT_MANAGER     => $eventManager,
                EventStateMachineFactory::K_CFG_EVENT_TARGET      => $target,
                EventStateMachineFactory::K_CFG_EVENT_NAME_FORMAT => $eventNameFormat,
                EventStateMachineFactory::K_CFG_TRANSITIONS       => $transitions,
                EventStateMachineFactory::K_CFG_INITIAL_STATE     => $initialState,
                EventStateMachineFactory::K_CFG_EVENT_PARAMS      => $params,
            ]
        );

        $this->assertInstanceOf(
            'RebelCode\State\EventStateMachine',
            $actual,
            'Created instance is not a valid event state machine instance.'
        );
        $this->assertSame($eventFactory, $this->reflect($actual)->_getEventFactory(), 'Event factory is wrong.');
    }

    /**
     * Tests the factory to assert whether the created state machine uses the static event name format when not given
     * in the config.
     *
     * @since [*next-version*]
     */
    public function testFactoryStaticEventNameFormat()
    {
        /* @var $eventManager EventManagerInterface|MockObject */
        $eventManager    = $this->getMock('Psr\EventManager\EventManagerInterface');
        $eventFactory    = $this->getMock('Dhii\Event\EventFactoryInterface');
        $eventNameFormat = uniqid('format-');
        $transitions     = [
            uniqid('state-') => [
                uniqid('transition-'),
                uniqid('transition-'),
            ],
            uniqid('state-') => [
                uniqid('transition-'),
            ],
        ];
        $target          = new stdClass();
        $initialState    = uniqid('state-');
        $params          = [
            uniqid('key-') => uniqid('val-'),
            uniqid('key-') => uniqid('val-'),
        ];

        $subject = new EventStateMachineFactory(null, null, $eventNameFormat);

        /* @var $actual EventStateMachine */
        $actual = $subject->make(
            [
                EventStateMachineFactory::K_CFG_EVENT_FACTORY => $eventFactory,
                EventStateMachineFactory::K_CFG_EVENT_TARGET  => $target,
                EventStateMachineFactory::K_CFG_EVENT_MANAGER => $eventManager,
                EventStateMachineFactory::K_CFG_TRANSITIONS   => $transitions,
                EventStateMachineFactory::K_CFG_INITIAL_STATE => $initialState,
                EventStateMachineFactory::K_CFG_EVENT_PARAMS  => $params,
            ]
        );

        $this->assertInstanceOf(
            'RebelCode\State\EventStateMachine',
            $actual,
            'Created instance is not a valid event state machine instance.'
        );
        $this->assertEquals(
            $eventNameFormat,
            $this->reflect($actual)->_getEventNameFormat(),
            'Event name format is wrong.'
        );
    }

    /**
     * Tests the factory to assert whether the created state machine uses the static event params when not given
     * in the config.
     *
     * @since [*next-version*]
     */
    public function testFactoryStaticEventParams()
    {
        /* @var $eventManager EventManagerInterface|MockObject */
        $eventManager    = $this->getMock('Psr\EventManager\EventManagerInterface');
        $eventFactory    = $this->getMock('Dhii\Event\EventFactoryInterface');
        $eventNameFormat = uniqid('format-');
        $transitions     = [
            uniqid('state-') => [
                uniqid('transition-'),
                uniqid('transition-'),
            ],
            uniqid('state-') => [
                uniqid('transition-'),
            ],
        ];
        $target          = new stdClass();
        $initialState    = uniqid('state-');
        $params          = [
            uniqid('key-') => uniqid('val-'),
            uniqid('key-') => uniqid('val-'),
        ];

        $subject = new EventStateMachineFactory(null, null, null, $params);

        /* @var $actual EventStateMachine */
        $actual = $subject->make(
            [
                EventStateMachineFactory::K_CFG_EVENT_FACTORY     => $eventFactory,
                EventStateMachineFactory::K_CFG_EVENT_TARGET      => $target,
                EventStateMachineFactory::K_CFG_EVENT_MANAGER     => $eventManager,
                EventStateMachineFactory::K_CFG_EVENT_NAME_FORMAT => $eventNameFormat,
                EventStateMachineFactory::K_CFG_TRANSITIONS       => $transitions,
                EventStateMachineFactory::K_CFG_INITIAL_STATE     => $initialState,
            ]
        );

        $this->assertInstanceOf(
            'RebelCode\State\EventStateMachine',
            $actual,
            'Created instance is not a valid event state machine instance.'
        );
        $this->assertEquals(
            $params,
            $this->reflect($actual)->_getEventParams(),
            'Event params are wrong.'
        );
    }

    /**
     * Tests the factory to assert whether the created state machine uses the static event target when not given
     * in the config.
     *
     * @since [*next-version*]
     */
    public function testFactoryStaticEventTarget()
    {
        /* @var $eventManager EventManagerInterface|MockObject */
        $eventManager    = $this->getMock('Psr\EventManager\EventManagerInterface');
        $eventFactory    = $this->getMock('Dhii\Event\EventFactoryInterface');
        $eventNameFormat = uniqid('format-');
        $transitions     = [
            uniqid('state-') => [
                uniqid('transition-'),
                uniqid('transition-'),
            ],
            uniqid('state-') => [
                uniqid('transition-'),
            ],
        ];
        $target          = new stdClass();
        $initialState    = uniqid('state-');
        $params          = [
            uniqid('key-') => uniqid('val-'),
            uniqid('key-') => uniqid('val-'),
        ];

        $subject = new EventStateMachineFactory(null, null, null, null, $target);

        /* @var $actual EventStateMachine */
        $actual = $subject->make(
            [
                EventStateMachineFactory::K_CFG_EVENT_FACTORY     => $eventFactory,
                EventStateMachineFactory::K_CFG_EVENT_MANAGER     => $eventManager,
                EventStateMachineFactory::K_CFG_EVENT_NAME_FORMAT => $eventNameFormat,
                EventStateMachineFactory::K_CFG_EVENT_PARAMS      => $params,
                EventStateMachineFactory::K_CFG_TRANSITIONS       => $transitions,
                EventStateMachineFactory::K_CFG_INITIAL_STATE     => $initialState,
            ]
        );

        $this->assertInstanceOf(
            'RebelCode\State\EventStateMachine',
            $actual,
            'Created instance is not a valid event state machine instance.'
        );
        $this->assertEquals(
            $target,
            $this->reflect($actual)->_getTarget(),
            'Event target is wrong.'
        );
    }
}
