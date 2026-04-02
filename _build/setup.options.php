<?php

use MODX\Revolution\modSystemSetting;
use xPDO\Transport\xPDOTransport;

$settings = [
    [
        'key'   => 'openai-key',
        'value' => '',
        'name'  => 'OpenAI (ChatGPT)'
    ],
    [
        'key'   => 'anthropic-key',
        'value' => '',
        'name'  => 'Anthropic (Claude)'
    ],
    [
        'key'   => 'google-key',
        'value' => '',
        'name'  => 'Google (Gemini)'
    ],
    [
        'key'   => 'openrouter-key',
        'value' => '',
        'name'  => 'OpenRouter (multi-vendor)'
    ],
];

switch ($options[xPDOTransport::PACKAGE_ACTION]) {
    case xPDOTransport::ACTION_INSTALL:
    case xPDOTransport::ACTION_UPGRADE:
        foreach ($settings as $key => $setting) {
            $settingObject = $modx->getObject(modSystemSetting::class, ['key' => 'modai.api.' . str_replace('-', '.', $setting['key'])]);
            if ($settingObject) {
                $value = $settingObject->get('value');
                if (!empty($value)) {
                    $settings[$key]['value'] = 'filled';
                }
            }
        }
        break;
    default:
    case xPDOTransport::ACTION_UNINSTALL:
        $output = '';
        break;
}

/* Hide default setup options text */
$output[] = '
<style type="text/css">
    #modx-setupoptions-panel { display: none; }
    .modai-setup {
        max-width: 640px;
        margin: 0 auto;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }
    .modai-setup h2 {
        margin: 0 0 10px;
        color: #10233f;
        font-size: 28px;
        line-height: 1.1;
    }
    .modai-setup p {
        margin: 0 0 10px;
        color: #51627d;
        font-size: 14px;
        line-height: 1.6;
    }
    .modai-setup-fields {
        margin-top: 24px;
        display: grid;
        gap: 16px;
    }
    .modai-setup-field {
        display: grid;
        gap: 8px;
    }
    .modai-setup-field label {
        color: #1d304d;
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 0.02em;
        text-transform: uppercase;
    }
    .modai-setup-field input {
        width: 100%;
        min-height: 48px;
        padding: 0 14px;
        border: 1px solid #c1cede;
        border-radius: 12px;
        background: #ffffff;
        color: #14263f;
        font-size: 15px;
        line-height: 1.4;
        box-sizing: border-box;
        -webkit-appearance: none;
        appearance: none;
        transition: border-color 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease;
    }
    .modai-setup-field input:hover {
        border-color: #97aac2;
        background: #fbfdff;
    }
    .modai-setup-field input:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.14);
        background: #ffffff;
    }
    .modai-setup-field input[value="filled"] {
        border-color: #7bb69a;
        background: linear-gradient(180deg, #f7fffa 0%, #eff9f3 100%);
    }
    .modai-setup-field input[value="filled"]:focus {
        box-shadow: 0 0 0 4px rgba(38, 135, 72, 0.14);
    }
    .modai-setup-note {
        margin-top: 18px;
        padding: 12px 14px;
        border-radius: 12px;
        background: #edf4ff;
        color: #32507e;
        font-size: 13px;
        line-height: 1.5;
    }
</style>
<script>
    document.getElementsByClassName("x-window-header-text")[0].innerHTML = "modAI installation";
</script>
<div class="modai-setup">
<h2>API Keys</h2>
<p>Add the API keys for the services you wish to use.</p>
<p>You can add these later in the system settings, but you will need at least one to use modAI. Click the vendor labels below to open instructions for each one. <a href="https://modxcms.github.io/modAI/supported-services.html" target="_blank">Learn more about API keys in modAI</a>.</p>
<div class="modai-setup-fields">';

foreach ($settings as $setting) {
    $str = '<div class="modai-setup-field">';
    $str .= '<label for="' . $setting['key'] . '">' . $setting['name'] . '</label>';
    $str .= '<input type="password" name="' . $setting['key'] . '"';
    $str .= ' id="' . $setting['key'] . '" value="' . $setting['value'] . '" />';
    $str .= '</div>';

    $output[] = $str;
}

$output[] = '</div><div class="modai-setup-note">Saved keys are hidden and shown as dots for security.</div></div>';

return implode('', $output);
