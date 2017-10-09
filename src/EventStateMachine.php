<?php

namespace RebelCode\State;

use Dhii\Events\TransitionEventInterface;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\State\ReadableStateMachineInterface;
use Dhii\State\StateAwareTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use Exception;
use Psr\EventManager\EventManagerInterface;
use RebelCode\State\Exception\CouldNotTransitionException;

/**
 * A readable event driven state machine.
 *
 * The machine triggers events on transition and determines the new state depending on the event.
 * Event handlers can set the new state through the event and can also abort the transition altogether.
 *
 * @since [*next-version*]
 */
class EventStateMachine extends AbstractEventStateMachine implements ReadableStateMachineInterface
{
    /**
     * The key for the current state in event params.
     *
     * @since [*next-version*]
     */
    const K_PARAM_CURRENT_STATE = 'current_state';

    /**
     * The default sprintf-style format for event names.
     *
     * @since [*next-version*]
     */
    const DEFAULT_EVENT_NAME_FORMAT = 'on_%s_transition';

    /*
     * Provides string translating functionality.
     *
     * @since [*next-version*]
     */
    use StringTranslatingTrait;

    /*
     * Provides functionality for creating invalid argument exceptions.
     *
     * @since [*next-version*]
     */
    use CreateInvalidArgumentExceptionCapableTrait;

    /*
     * Provides awareness of a state.
     *
     * @since [*next-version*]
     */
    use StateAwareTrait;

    /**
     * The event manager instance.
     *
     * @since [*next-version*]
     *
     * @var EventManagerInterface
     */
    protected $eventManager;

    /**
     * The event target, for context.
     *
     * @since [*next-version*]
     *
     * @var mixed
     */
    protected $target;

    /**
     * The format for event names.
     *
     * @since [*next-version*]
     *
     * @var string|Stringable
     */
    protected $eventNameFormat;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param EventManagerInterface $eventManager    The event manager.
     * @param string|Stringable     $state           The initial state.
     * @param string|null           $eventNameFormat The format for event names.
     * @param mixed|null            $target          The target for triggered events, used for context.
     */
    public function __construct(
        EventManagerInterface $eventManager,
        $state,
        $eventNameFormat = null,
        $target = null
    ) {
        $this->_setEventManager($eventManager);
        $this->_setState($state);
        $this->_setEventNameFormat($eventNameFormat);
        $this->_setTarget($target);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getState()
    {
        return $this->_getState();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function canTransition($transition)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function transition($transition)
    {
        $this->_transition($transition);

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getNewState(TransitionEventInterface $event)
    {
        return $event->getTransition();
    }

    /**
     * Checks if the subject is a valid string or stringable object.
     *
     * @since [*next-version*]
     *
     * @param mixed $subject The subject.
     *
     * @return bool True if the subject is a string or stringable object, false if not.
     */
    protected function _isValidString($subject)
    {
        return is_string($subject) || $subject instanceof Stringable;
    }

    /**
     * Retrieves the event manager associated with this instance.
     *
     * @since [*next-version*]
     *
     * @return EventManagerInterface The event manager instance.
     */
    protected function _getEventManager()
    {
        return $this->eventManager;
    }

    /**
     * Sets the event manager for this instance.
     *
     * @since [*next-version*]
     *
     * @param EventManagerInterface $eventManager The event manager instance.
     *
     * @return $this
     */
    protected function _setEventManager($eventManager)
    {
        $this->eventManager = $eventManager;

        return $this;
    }

    /**
     * Retrieves the event target.
     *
     * @since [*next-version*]
     *
     * @return mixed
     */
    protected function _getTarget()
    {
        return $this->target;
    }

    /**
     * Sets the event target.
     *
     * @since [*next-version*]
     *
     * @param mixed $target The event target.
     *
     * @return $this
     */
    protected function _setTarget($target)
    {
        $this->target = $target;

        return $this;
    }

    /**
     * Retrieves the event name format.
     *
     * @since [*next-version*]
     *
     * @return string|Stringable The sprintf-style format for event names.
     */
    protected function _getEventNameFormat()
    {
        return $this->eventNameFormat === null
            ? static::DEFAULT_EVENT_NAME_FORMAT
            : $this->eventNameFormat;
    }

    /**
     * Sets the event name format.
     *
     * @since [*next-version*]
     *
     * @param Stringable|string $eventNameFormat The sprintf-style format for event names.
     *
     * @return $this
     */
    protected function _setEventNameFormat($eventNameFormat)
    {
        if ($eventNameFormat !== null && !$this->_isValidString($eventNameFormat)) {
            throw $this->_createInvalidArgumentException(
                $this->__('Argument is not a string or stringable object'),
                null,
                null,
                $eventNameFormat
            );
        }

        $this->eventNameFormat = $eventNameFormat;

        return $this;
    }

    /**
     * Retrieves the transition event instance for a transition.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $transition The transition related to the transition event.
     *
     * @return TransitionEventInterface The transition event instance.
     */
    protected function _getTransitionEvent($transition)
    {
        return $this->_createTransitionEvent(
            $this->_generateEventName($transition),
            $transition,
            $this->_getTarget(),
            $this->_getEventParams($transition)
        );
    }

    /**
     * Generates an event name for a transition event.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $transition The transition related to the transition event.
     *
     * @return string The event name.
     */
    protected function _generateEventName($transition)
    {
        return sprintf((string) $this->_getEventNameFormat(), $transition);
    }

    /**
     * Retrieves the event params for a transition event.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $transition The transition related to the transition event.
     *
     * @return array The event params.
     */
    protected function _getEventParams($transition)
    {
        return [
            static::K_PARAM_CURRENT_STATE => $this->_getState(),
        ];
    }

    /**
     * Creates a transition event.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $name       The event name.
     * @param string|Stringable $transition The transition.
     * @param mixed|null        $target     The target, used for context.
     * @param array             $params     The event params.
     *
     * @return TransitionEvent The created instance.
     */
    protected function _createTransitionEvent($name, $transition, $target = null, array $params = [])
    {
        return new TransitionEvent((string) $name, $transition, $target, $params);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _createCouldNotTransitionException(
        $message = null,
        $code = null,
        Exception $previous = null,
        $transition = null
    ) {
        return new CouldNotTransitionException($message, $code, $previous, $this, $transition);
    }
}
