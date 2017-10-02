<?php

namespace RebelCode\State\Exception;

use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\State\Exception\StateMachineExceptionInterface;
use Dhii\State\StateMachineAwareTrait;
use Exception;

/**
 * Base common functionality for exceptions thrown in relation to a state machine.
 *
 * @since [*next-version*]
 */
class AbstractBaseStateMachineException extends Exception implements StateMachineExceptionInterface
{
    /*
     * Provides string translating functionality.
     *
     * @since [*next-version*]
     */
    use StringTranslatingTrait;

    /*
     * Provides awareness of a state machine.
     *
     * @since [*next-version*]
     */
    use StateMachineAwareTrait {
        _getStateMachine as public getStateMachine;
    }

    /*
     * Provides functionality for creating invalid argument exceptions.
     *
     * @since [*next-version*]
     */
    use CreateInvalidArgumentExceptionCapableTrait;
}
