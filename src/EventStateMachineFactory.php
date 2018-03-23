<?php

namespace RebelCode\State;

use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\ContainerHasCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Container\NormalizeKeyCapableTrait;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\Factory\AbstractBaseCallbackFactory;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;

/**
 * Implementation of a factory that creates {@see EventStateMachine} instances.
 *
 * @since [*next-version*]
 */
class EventStateMachineFactory extends AbstractBaseCallbackFactory
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
     * The config key for the target.
     *
     * @since [*next-version*]
     */
    const K_CFG_TARGET = 'target';

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getFactoryCallback($config = null)
    {
        return function($config) {
            $eventManager = $this->_containerGet($config, static::K_CFG_EVENT_MANAGER);
            $initialState = $this->_containerGet($config, static::K_CFG_INITIAL_STATE);
            $transitions  = $this->_containerGet($config, static::K_CFG_TRANSITIONS);

            $eventNameFormat = $this->_containerHas($config, static::K_CFG_EVENT_NAME_FORMAT)
                ? $this->_containerGet($config, static::K_CFG_EVENT_NAME_FORMAT)
                : null;

            $target = $this->_containerHas($config, static::K_CFG_TARGET)
                ? $this->_containerGet($config, static::K_CFG_TARGET)
                : null;

            return new EventStateMachine($eventManager, $initialState, $transitions, $eventNameFormat, $target);
        };
    }
}
