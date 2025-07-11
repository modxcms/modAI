<?php

return new class() {
    /**
     * @var \MODX\Revolution\modX
     */
    private $modx;

    /**
     * @var int
     */
    private $action;

    /**
    * @param \MODX\Revolution\modX $modx
    * @param int $action
    * @return bool
    */
    public function __invoke(&$modx, $action)
    {
        $this->modx =& $modx;
        $this->action = $action;

        $events = [
            'modAIOnContextProviderRegister',
            'modAIOnToolRegister',
            'modAIOnServiceRegister',
            'modAIOnInit',
        ];

        if ($this->action === \xPDO\Transport\xPDOTransport::ACTION_UNINSTALL) {
            foreach ($events as $eventName) {
                $event = $modx->getObject('modEvent', ['name' => $eventName]);
                if ($event) {
                    $event->remove();
                }
            }

            return true;
        }

        foreach ($events as $eventName) {
            $event = $modx->getObject('modEvent', ['name' => $eventName]);
            if (!$event) {
                $event = $modx->newObject('modEvent');
                $event->set('name', $eventName);
                $event->set('service', 6);
                $event->set('groupname', 'modAI');
                $event->save();
            }
        }

        return true;
    }
};
