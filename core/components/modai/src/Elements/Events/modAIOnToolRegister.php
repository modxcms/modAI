<?php
namespace modAI\Elements\Events;

use modAI\Tools\GetWeather;

class modAIOnToolRegister extends Event
{
    public function run()
    {
        $this->modx->event->output(GetWeather::class);
    }
}
