<?php

namespace RebelCode\State;

use Dhii\Events\TransitionEventInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use Exception;
use Psr\EventManager\EventManagerInterface;

/**
 * Abstract functionality for event-based state machines.
 *
 * @since [*next-version*]
 */
abstract class AbstractEventStateMachine
{
    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _transition($transition)
    {
        $event = $this->_getTransitionEvent($transition);

        try {
            $this->_getEventManager()->trigger($event);
        } catch (Exception $ex) {
            $exception = $this->_createCouldNotTransitionException(
                $this->__('The triggered event threw an exception'),
                null,
                $ex,
                $transition
            );
        }

        if (!$event->isTransitionAborted()) {
            $this->_setState($this->_getNewState($event));
        }

        if (isset($exception) && $exception instanceof Exception) {
            throw $exception;
        }
    }

    /**
     * Retrieves the event manager associated with this instance.
     *
     * @since [*next-version*]
     *
     * @return EventManagerInterface The event manager instance.
     */
    abstract protected function _getEventManager();

    /**
     * Retrieves the transition event instance for a transition.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $transition The transition related to the transition event.
     *
     * @return TransitionEventInterface The transition event instance.
     */
    abstract protected function _getTransitionEvent($transition);

    /**
     * Retrieves the new state for a given event transition.
     *
     * @since [*next-version*]
     *
     * @param TransitionEventInterface $event The event.
     *
     * @return string|Stringable The new state.
     */
    abstract protected function _getNewState(TransitionEventInterface $event);

    /**
     * Sets the state for this instance.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|null $state The state, or null.
     */
    abstract protected function _setState($state);

    /**
     * Creates an state machine related exception.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|null $message  The error message, if any.
     * @param int|null               $code     The error code, if any.
     * @param Exception|null         $previous The previous exception for chaining, if any.
     *
     * @return CouldNotTransitionExceptionInterface The created exception.
     */
    abstract protected function _createStateMachineException(
        $message = null,
        $code = null,
        Exception $previous = null
    );

    /**
     * Creates an exception for transition failure.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|null $message    The error message, if any.
     * @param int|null               $code       The error code, if any.
     * @param Exception|null         $previous   The previous exception for chaining, if any.
     * @param string|Stringable|null $transition The transition that failed, if any.
     *
     * @return CouldNotTransitionExceptionInterface The created exception.
     */
    abstract protected function _createCouldNotTransitionException(
        $message = null,
        $code = null,
        Exception $previous = null,
        $transition = null
    );

    /**
     * Translates a string, and replaces placeholders.
     *
     * @since [*next-version*]
     * @see   sprintf()
     * @see   _translate()
     *
     * @param string $string  The format string to translate.
     * @param array  $args    Placeholder values to replace in the string.
     * @param mixed  $context The context for translation.
     *
     * @return string The translated string.
     */
    abstract protected function __($string, $args = [], $context = null);
}
