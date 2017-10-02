<?php

namespace RebelCode\State\Exception;

use Dhii\State\StateMachineInterface;
use Exception;

/**
 * A generic exception thrown in relation to a state machine.
 *
 * @since [*next-version*]
 */
class StateMachineException extends AbstractBaseStateMachineException
{
    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string                     $message      The error message.
     * @param int                        $code         The error code.
     * @param Exception|null             $previous     The previous exception, for chaining.
     * @param StateMachineInterface|null $stateMachine The state machine that erred.
     */
    public function __construct(
        $message = '',
        $code = 0,
        Exception $previous = null,
        StateMachineInterface $stateMachine = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->_setStateMachine($stateMachine);
    }
}
