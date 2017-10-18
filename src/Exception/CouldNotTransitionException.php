<?php

namespace RebelCode\State\Exception;

use Dhii\State\Exception\CouldNotTransitionExceptionInterface;
use Dhii\State\StateMachineInterface;
use Dhii\State\TransitionAwareTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use Exception;

/**
 * An exception thrown when a transition failed.
 *
 * @since [*next-version*]
 */
class CouldNotTransitionException
    extends AbstractBaseStateMachineException
    implements CouldNotTransitionExceptionInterface
{
    /*
     * Provides awareness of a transition.
     *
     * @since [*next-version*]
     */
    use TransitionAwareTrait {
        _getTransition as public getTransition;
    }

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string                     $message      The error message.
     * @param int                        $code         The error code.
     * @param Exception|null             $previous     The previous exception, for chaining.
     * @param StateMachineInterface|null $stateMachine The state machine that erred.
     * @param string|Stringable|null     $transition   The transition that failed.
     */
    public function __construct(
        $message = '',
        $code = 0,
        Exception $previous = null,
        StateMachineInterface $stateMachine = null,
        $transition = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->_setStateMachine($stateMachine);
        $this->_setTransition($transition);
    }
}
