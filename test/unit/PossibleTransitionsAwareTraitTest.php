<?php

namespace RebelCode\State\FuncTest;

use ArrayIterator;
use \InvalidArgumentException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Xpmock\TestCase;
use RebelCode\State\PossibleTransitionsAwareTrait as TestSubject;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class PossibleTransitionsAwareTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\State\PossibleTransitionsAwareTrait';

    /**
     * Creates a new instance of the test subject.
     *
     * @since [*next-version*]
     *
     * @return MockObject|TestSubject
     */
    public function createInstance()
    {
        // Create mock
        $mock = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
                     ->setMethods(['_normalizeContainer'])
                     ->getMockForTrait();

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
            'An instance of the test subject could not be created'
        );
    }

    /**
     * Tests the getter and setter methods to ensure correct assignment and retrieval.
     *
     * @since [*next-version*]
     */
    public function testGetSetPossibleTransitions()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);
        $input   = [];

        $subject->method('_normalizeContainer')->willReturn($input);

        $reflect->_setPossibleTransitions($input);

        $this->assertSame($input, $reflect->_getPossibleTransitions(), 'Set and retrieved value are not the same.');
    }

    /**
     * Tests the getter and setter methods with an invalid value to assert whether an exception is thrown.
     *
     * @since [*next-version*]
     */
    public function testGetSetPossibleTransitionsInvalid()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);
        $input   = new ArrayIterator([]);

        $subject->method('_normalizeContainer')->willThrowException(new InvalidArgumentException());

        $this->setExpectedException('InvalidArgumentException');

        $reflect->_setPossibleTransitions($input);
    }
}
