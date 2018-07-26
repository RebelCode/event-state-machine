<?php

namespace RebelCode\State;

use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\ContainerHasCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Container\NormalizeContainerCapableTrait;
use Dhii\Data\Container\NormalizeKeyCapableTrait;
use Dhii\Event\EventFactoryInterface;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\State\StateMachineFactoryInterface;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Dhii\Util\Normalization\NormalizeStringableCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use Psr\EventManager\EventManagerInterface;
use stdClass;
use Traversable;

/**
 * Implementation of a factory that creates {@see EventStateMachine} instances.
 *
 * @since [*next-version*]
 */
class EventStateMachineFactory implements StateMachineFactoryInterface
{
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
     * Provides key normalization functionality.
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
     * Provides stringable normalization functionality.
     *
     * @since [*next-version*]
     */
    use NormalizeStringableCapableTrait;

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
     * Provides functionality for creating container exceptions.
     *
     * @since [*next-version*]
     */
    use CreateContainerExceptionCapableTrait;

    /*
     * Provides functionality for creating not found exceptions.
     *
     * @since [*next-version*]
     */
    use CreateNotFoundExceptionCapableTrait;

    /*
     * Provides functionality for creating invalid-argument exceptions.
     *
     * @since [*next-version*]
     */
    use CreateInvalidArgumentExceptionCapableTrait;

    /*
     * Provides functionality for creating out-of-range exceptions.
     *
     * @since [*next-version*]
     */
    use CreateOutOfRangeExceptionCapableTrait;

    /*
     * Provides string translation functionality.
     *
     * @since [*next-version*]
     */
    use StringTranslatingTrait;

    /**
     * The config key for the event manager.
     *
     * @since [*next-version*]
     */
    const K_CFG_EVENT_MANAGER = 'event_manager';

    /**
     * The config key for the event factory.
     *
     * @since [*next-version*]
     */
    const K_CFG_EVENT_FACTORY = 'event_factory';

    /**
     * The config key for the initial state.
     *
     * @since [*next-version*]
     */
    const K_CFG_INITIAL_STATE = 'initial_state';

    /**
     * The config key for the transitions.
     *
     * @since [*next-version*]
     */
    const K_CFG_TRANSITIONS = 'transitions';

    /**
     * The config key for the event name format.
     *
     * @since [*next-version*]
     */
    const K_CFG_EVENT_NAME_FORMAT = 'event_name_format';

    /**
     * The config key for the event target.
     *
     * @since [*next-version*]
     */
    const K_CFG_EVENT_TARGET = 'event_target';

    /**
     * The config key for the event params.
     *
     * @since [*next-version*]
     */
    const K_CFG_EVENT_PARAMS = 'event_params';

    /**
     * The event manager to use for created instances.
     *
     * @since [*next-version*]
     *
     * @var null|EventManagerInterface
     */
    protected $eventManager;

    /**
     * The event factory to use for created instances.
     *
     * @since [*next-version*]
     *
     * @var null|EventFactoryInterface
     */
    protected $eventFactory;

    /**
     * The event name format to use for created instances.
     *
     * @since [*next-version*]
     *
     * @var Stringable|null|string
     */
    protected $eventNameFormat;

    /**
     * The event params to use for created instances.
     *
     * @since [*next-version*]
     *
     * @var array|null
     */
    protected $eventParams;

    /**
     * The event target to use for created instances.
     *
     * @since [*next-version*]
     *
     * @var mixed|null
     */
    protected $eventTarget;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param EventManagerInterface|null      $eventManager    The event manager to use for created instances.
     * @param EventFactoryInterface|null      $eventFactory    The event factory to use for created instances.
     * @param string|Stringable|null          $eventNameFormat The event name format to use for created instances.
     * @param array|stdClass|Traversable|null $eventParams     The event params to use for created instances.
     * @param mixed|null                      $eventTarget     The event target to use for created instances.
     */
    public function __construct(
        EventManagerInterface $eventManager = null,
        EventFactoryInterface $eventFactory = null,
        $eventNameFormat = null,
        $eventParams = null,
        $eventTarget = null
    ) {
        $this->eventManager = $eventManager;
        $this->eventFactory = $eventFactory;
        $this->eventTarget  = $eventTarget;

        $this->eventNameFormat = ($eventNameFormat !== null)
            ? $this->_normalizeStringable($eventNameFormat)
            : null;

        $this->eventParams = ($eventParams !== null)
            ? $this->_normalizeArray($eventParams)
            : null;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function make($config = null)
    {
        $initialState = $this->_containerGet($config, static::K_CFG_INITIAL_STATE);
        $transitions  = $this->_containerGet($config, static::K_CFG_TRANSITIONS);

        $eventManager = $this->_containerHas($config, static::K_CFG_EVENT_MANAGER)
            ? $this->_containerGet($config, static::K_CFG_EVENT_MANAGER)
            : $this->eventManager;
            
        $eventFactory = $this->_containerHas($config, static::K_CFG_EVENT_FACTORY)
            ? $this->_containerGet($config, static::K_CFG_EVENT_FACTORY)
            : $this->eventFactory;

        $eventNameFormat = $this->_containerHas($config, static::K_CFG_EVENT_NAME_FORMAT)
            ? $this->_containerGet($config, static::K_CFG_EVENT_NAME_FORMAT)
            : $this->eventNameFormat;

        $target = $this->_containerHas($config, static::K_CFG_EVENT_TARGET)
            ? $this->_containerGet($config, static::K_CFG_EVENT_TARGET)
            : $this->eventTarget;

        $params = $this->_containerHas($config, static::K_CFG_EVENT_PARAMS)
            ? $this->_containerGet($config, static::K_CFG_EVENT_PARAMS)
            : $this->eventParams;

        return new EventStateMachine(
            $eventManager, $eventFactory, $initialState, $transitions, $eventNameFormat, $target, $params
        );
    }
}
