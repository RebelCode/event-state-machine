<?php

namespace RebelCode\State;

use Dhii\Events\TransitionEventInterface;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\State\ReadableStateMachineInterface;
use Dhii\State\StateMachineAwareTrait;
use Dhii\State\TransitionAwareTrait;
use Dhii\Util\String\StringableInterface as Stringable;

/**
 * An event triggered in relation to a transition.
 *
 * @since [*next-version*]
 */
class TransitionEvent implements TransitionEventInterface
{
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
     * Provides awareness of a transition.
     *
     * @since [*next-version*]
     */
    use TransitionAwareTrait {
        _getTransition as public getTransition;
    }

    /*
     * Provides awareness of a state machine.
     *
     * @since [*next-version*]
     */
    use StateMachineAwareTrait {
        _getStateMachine as public getStateMachine;
    }

    /**
     * The event name.
     *
     * @since [*next-version*]
     *
     * @var string
     */
    protected $name;

    /**
     * The parameters.
     *
     * @since [*next-version*]
     *
     * @var array
     */
    protected $params;

    /**
     * The target context object.
     *
     * @since [*next-version*]
     *
     * @var mixed
     */
    protected $target;

    /**
     * The stopped propagation flag.
     *
     * @since [*next-version*]
     *
     * @var bool
     */
    protected $isPropagationStopped;

    /**
     * The aborted transition flag.
     *
     * @since [*next-version*]
     *
     * @var bool
     */
    protected $isTransitionAborted;

    /**
     * Constructs a new instance.
     *
     * @param string            $name       The event name.
     * @param string|Stringable $transition The transition.
     * @param mixed             $target     The target, for context.
     * @param array             $params     The event parameters.
     */
    public function __construct(
        $name,
        $transition,
        $target = null,
        array $params = []
    ) {
        $this->setName($name);
        $this->stopPropagation(false);
        $this->setParams($params);
        $this->setTarget($target);
        $this->_setTransition($transition);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getParam($name)
    {
        return isset($this->params[$name])
            ? $this->params[$name]
            : null;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function setParams(array $params)
    {
        $this->params = $params;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     *
     * @return ReadableStateMachineInterface
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function setTarget($target)
    {
        $this->target = $target;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function isPropagationStopped()
    {
        return $this->isPropagationStopped;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function stopPropagation($flag)
    {
        $this->isPropagationStopped = $flag;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function isTransitionAborted()
    {
        return $this->isTransitionAborted;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function abortTransition($abort)
    {
        $this->isTransitionAborted = $abort;

        return $this;
    }
}
