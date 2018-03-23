<?php

namespace RebelCode\State\FuncTest;

use RebelCode\State\EventStateMachine;
use RebelCode\State\EventStateMachineFactory as TestSubject;
use RebelCode\State\EventStateMachineFactory;
use stdClass;
use Xpmock\TestCase;
use Exception as RootException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

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
            'Dhii\Factory\FactoryInterface',
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

        /* @var $actual EventStateMachine */
        $actual = $subject->make(
            [
                EventStateMachineFactory::K_CFG_TARGET            => $target,
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
        $this->assertEquals($initialState, $actual->getState(), 'Initial state is wrong.');
    }
}
