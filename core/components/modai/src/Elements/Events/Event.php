<?php

namespace modAI\Elements\Events;

use modAI\modAI;
use MODX\Revolution\modX;

abstract class Event
{
    /** @var modX */
    protected modX $modx;

    /** @var modAI */
    protected modAI $modAI;

    /** @var array */
    protected $sp = [];

    public function __construct(modAI &$modAI, array $scriptProperties)
    {
        $this->modAI =& $modAI;
        $this->modx =& $this->modAI->modx;
        $this->sp = $scriptProperties;
    }

    abstract public function run();

    protected function getOption($key, $default = null, $skipEmpty = false)
    {
        return $this->modx->getOption($key, $this->sp, $default, $skipEmpty);
    }
}
