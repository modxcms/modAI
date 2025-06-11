<?php
namespace modAI\Model;

use xPDO\xPDO;

/**
 * Class PromptLibraryCategory
 *
 * @property string $name
 * @property boolean $enabled
 * @property int $rank
 *
 * @property \modAI\Model\PromptLibraryPrompt[] $Prompts
 *
 * @package modAI\Model
 */
class PromptLibraryCategory extends \xPDO\Om\xPDOSimpleObject
{
}
