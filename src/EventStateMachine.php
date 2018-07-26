<?php

namespace RebelCode\State;

use ArrayAccess;
use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\ContainerHasCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Container\NormalizeContainerCapableTrait;
use Dhii\Data\Container\NormalizeKeyCapableTrait;
use Dhii\Event\EventFactoryInterface;
use Dhii\Events\TransitionEventInterface;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\State\PossibleTransitionsAwareInterface;
use Dhii\State\ReadableStateMachineInterface;
use Dhii\State\StateAwareTrait;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use Exception;
use InvalidArgumentException;
use OutOfRangeException;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\EventManager\EventManagerInterface;
use RebelCode\State\Exception\CouldNotTransitionException;
use RebelCode\State\Exception\StateMachineException;
use stdClass;

/**
 * A readable, event-driven state machine.
 *
 * This implementation does not use an internal state graph. Instead, it will attempt to retrieve the new state from the
 * event params. If no such data is present in the event, it will use the transition key for the new state. As such,
 * it is perfectly valid to transition to the same state.
 *
 * An event manager is used to trigger events when a transition is being applied. The handlers attached to the manager
 * can abort the transition via {@link `TransitionEventInterface::abortTransition()`}.
 *
 * Unless the transition is explicitly aborted by handlers, it will be applied after execution of all handlers even if
 * an exception is thrown. If this is not desirable behaviour, handlers can catch exceptions and abort the transition
 * in their `catch` body.
 *
 * It is important to note that handlers should not rely on {@link `TransitionEventInterface::abortTransition()`} to
 * guarantee abortion. Handlers that are invoked at a later time may explicitly call `$event->abortTransition(false)`.
 * To guarantee that a transition has been aborted, handlers must also stop propagation via
 * {@link `Psr\EventManager\EventInterface::stopPropagation()`}.
 *
 * @since [*next-version*]
 */
class EventStateMachine extends AbstractEventStateMachine implements
    ReadableStateMachineInterface,
    PossibleTransitionsAwareInterface
{
    /**
     * The key for the current state in event params.
     *
     * @since [*next-version*]
     */
    const K_PARAM_CURRENT_STATE = 'current_state';

    /**
     * The key for the new state in event params.
     *
     * @since [*next-version*]
     */
    const K_PARAM_NEW_STATE = 'new_state';

    /**
     * The key for the transition in event params.
     *
     * @since [*next-version*]
     */
    const K_PARAM_TRANSITION = 'transition';

    /**
     * The default sprintf-style format for event names.
     *
     * @since [*next-version*]
     */
    const DEFAULT_EVENT_NAME_FORMAT = 'on_transition';

    /*
     * Provides awareness of a state.
     *
     * @since [*next-version*]
     */
    use StateAwareTrait;

    /*
     * Provides awareness of a container of possible transitions.
     *
     * @since [*next-version*]
     */
    use PossibleTransitionsAwareTrait;

    /*
     * Provides functionality for creating invalid argument exceptions.
     *
     * @since [*next-version*]
     */
    use CreateInvalidArgumentExceptionCapableTrait;

    /*
     * Provides string translating functionality.
     *
     * @since [*next-version*]
     */
    use StringTranslatingTrait;

    /*
     * Provides functionality for reading from any type of container.
     *
     * @since [*next-version*]
     */
    use ContainerGetCapableTrait;

    /*
     * Provides functionality for key-checking any type of container.
     *
     * @since [*next-version*]
     */
    use ContainerHasCapableTrait;

    /*
     * Provides container key normalization functionality.
     *
     * @since [*next-version*]
     */
    use NormalizeKeyCapableTrait;

    /*
     * Provides string normalization functionality.
     *
     * @since [*next-version*]
     */
    use NormalizeStringCapableTrait;

    /*
     * Provides array normalization functionality.
     *
     * @since [*next-version*]
     */
    use NormalizeArrayCapableTrait;

    /*
     * Provides container normalization functionality.
     *
     * @since [*next-version*]
     */
    use NormalizeContainerCapableTrait;

    /*
     * Provides functionality for creating out-of-range exceptions.
     *
     * @since [*next-version*]
     */
    use CreateOutOfRangeExceptionCapableTrait;

    /*
     * Provides functionality for creating container exceptions.
     *
     * @since [*next-version*]
     */
    use CreateContainerExceptionCapableTrait;

    /*
     * Provides functionality for creating container not-found exceptions.
     *
     * @since [*next-version*]
     */
    use CreateNotFoundExceptionCapableTrait;

    /**
     * The event manager instance.
     *
     * @since [*next-version*]
     *
     * @var EventManagerInterface
     */
    protected $eventManager;

    /**
     * The event factory.
     *
     * @since [*next-version*]
     *
     * @var EventFactoryInterface
     */
    protected $eventFactory;

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
     * Additional params for events.
     * Additional params for events.
     *
     * @since [*next-version*]
     *
     * @var array
     */
    protected $eventParams;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param EventManagerInterface                         $eventManager    The event manager.
     * @param EventFactoryInterface                         $eventFactory    The event factory.
     * @param string|Stringable                             $state           The initial state.
     * @param array|ArrayAccess|stdClass|ContainerInterface $transitions     A mapping of state keys to lists of
     *                                                                       transitions.
     * @param string|null                                   $eventNameFormat The format for event names.
     * @param mixed|null                                    $target          The target for triggered events, used for
     *                                                                       context.
     * @param array                                         $eventParams     Additional params for events.
     */
    public function __construct(
        EventManagerInterface $eventManager,
        EventFactoryInterface $eventFactory,
        $state,
        $transitions,
        $eventNameFormat = null,
        $target = null,
        $eventParams = []
    ) {
        $this->_setEventManager($eventManager);
        $this->_setEventFactory($eventFactory);
        $this->_setState($state);
        $this->_setEventNameFormat($eventNameFormat);
        $this->_setTarget($target);
        $this->_setEventParams($eventParams);
        $this->_setPossibleTransitions($transitions);
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
        return $this->_containerHas($this->getPossibleTransitions(), $transition);
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
    public function getPossibleTransitions()
    {
        try {
            $key = $this->getState();
            $container = $this->_getPossibleTransitions();

            return $this->_containerGet($container, $key);
        } catch (NotFoundExceptionInterface $notFoundException) {
            return [];
        }
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getNewState(TransitionEventInterface $event)
    {
        $params = $event->getParams();

        return array_key_exists(static::K_PARAM_NEW_STATE, $params)
            ? $params[static::K_PARAM_NEW_STATE]
            : $event->getTransition();
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
     * Retrieves the event factory associated with this instance.
     *
     * @since [*next-version*]
     *
     * @return EventFactoryInterface The event factory.
     */
    protected function _getEventFactory()
    {
        return $this->eventFactory;
    }

    /**
     * Retrieves the event factory associated with this instance.
     *
     * @since [*next-version*]
     *
     * @param EventFactoryInterface $eventFactory The event factory.
     */
    protected function _setEventFactory(EventFactoryInterface $eventFactory)
    {
        $this->eventFactory = $eventFactory;
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
     * Retrieves the additional params for events.
     *
     * @since [*next-version*]
     *
     * @return array Additional params for events.
     */
    protected function _getEventParams()
    {
        return $this->eventParams;
    }

    /**
     * Sets the additional params for events.
     *
     * @since [*next-version*]
     *
     * @param array $eventParams Additional params for events.
     *
     * @throws InvalidArgumentException If the argument is valid.
     */
    protected function _setEventParams($eventParams)
    {
        if (!is_array($eventParams)) {
            throw $this->_createInvalidArgumentException(
                $this->__('Argument is not an array'),
                null,
                null,
                $eventParams
            );
        }

        $this->eventParams = $eventParams;
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
            $this->_getTransitionEventParams($transition)
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
    protected function _getTransitionEventParams($transition)
    {
        $staticParams     = $this->_getEventParams();
        $transitionParams = [static::K_PARAM_CURRENT_STATE => $this->_getState()];

        return $staticParams + $transitionParams;
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
     * @throws OutOfRangeException If the event created by the factory is not a transition event instance.
     *
     * @return TransitionEventInterface The created instance.
     */
    protected function _createTransitionEvent($name, $transition, $target = null, array $params = [])
    {
        $event = $this->_getEventFactory()->make(
            [
                'name'       => $name,
                'transition' => $transition,
                'target'     => $target,
                'params'     => $this->_normalizeArray($params),
            ]
        );

        if (!($event instanceof TransitionEventInterface)) {
            throw $this->_createOutOfRangeException(
                $this->__('Created event instance is not a transition event'),
                null,
                null,
                $event
            );
        }

        return $event;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _createStateMachineException(
        $message = null,
        $code = null,
        Exception $previous = null
    ) {
        return new StateMachineException($message, $code, $previous, $this);
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
