<?php

namespace RebelCode\State;

use ArrayAccess;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use stdClass;

/**
 * Functionality for awareness of a container of possible transitions.
 *
 * @since [*next-version*]
 */
trait PossibleTransitionsAwareTrait
{
    /**
     * The possible transitions container, mapping states to lists of possible transitions.
     *
     * @since [*next-version*]
     *
     * @var array|ArrayAccess|stdClass|ContainerInterface
     */
    protected $possibleTransitions;

    /**
     * Retrieves the possible transitions.
     *
     * @since [*next-version*]
     *
     * @return array|ArrayAccess|stdClass|ContainerInterface A container that maps states to lists of transitions.
     */
    protected function _getPossibleTransitions()
    {
        return $this->possibleTransitions;
    }

    /**
     * Sets the possible transitions.
     *
     * @since [*next-version*]
     *
     * @param array|ArrayAccess|stdClass|ContainerInterface $possibleTransitions A container that maps states to lists
     *                                                                           of transitions.
     *
     * @throws InvalidArgumentException If the argument is not a valid container.
     */
    protected function _setPossibleTransitions($possibleTransitions)
    {
        $this->possibleTransitions = $this->_normalizeContainer($possibleTransitions);
    }

    /**
     * Normalizes a container.
     *
     * @since [*next-version*]
     *
     * @param array|ArrayAccess|stdClass|ContainerInterface $container The container to normalize.
     *
     * @throws InvalidArgumentException If the container is invalid.
     *
     * @return array|ArrayAccess|stdClass|ContainerInterface Something that can be used with
     *                                                       {@see ContainerGetCapableTrait#_containerGet()} or
     *                                                       {@see ContainerHasCapableTrait#_containerHas()}.
     */
    abstract protected function _normalizeContainer($container);
}
