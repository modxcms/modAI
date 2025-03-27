<?php

namespace modAI\Exceptions;

use Throwable;

class RequiredSettingException extends LexiconException
{
    public function __construct($setting = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct('modai.error.system_setting_required', ['setting' => $setting], $code, $previous);
    }
}
