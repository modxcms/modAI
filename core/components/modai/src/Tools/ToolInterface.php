<?php

namespace modAI\Tools;

use MODX\Revolution\modX;

interface ToolInterface
{
    /**
     * Create an instance of the tool, taking in an array of the configuration by the site's admin.
     *
     * The config may be empty in which case you need to apply your own default values.
     *
     * @param modX $modx
     * @param array $config
     */
    public function __construct(modX $modx, array $config = []);

    /**
     * The suggested name for the tool, this will be pre-filled for the user when configuring the tool.
     *
     * @return string
     */
    public static function getSuggestedName(): string;


    /**
     * Internal description
     *
     * @return string
     */
    public static function getDescription(): string;

    /**
     * The prompt (description) passed into the LLM, explaining when and how to use this particular tool.
     *
     * This is a natural language prompt, so properly explaining its usage is important.
     *
     * You can use the provided user config to customise or let the site's admin determine part
     * of your prompt.
     *
     * @param modX $modx
     * @return string
     */
    public static function getPrompt(modX $modx): string;

    /**
     * Checks if user has permissions to run this tool.
     *
     * @param modX $modx
     * @return bool
     */
    public static function checkPermissions(modX $modx): bool;

    /**
     * Set the parameters that the LLM should or must provide when calling your function. Has to return valid JSON-schema.
     *
     * @param modX $modx
     * @return array Returns the parameters as an array.
     */
    public static function getParameters(modX $modx): array;

    /**
     * An array of tool parameters that should be exposed to the site's admin to configure your tool.
     *
     * Can be empty, but it is recommended to allow the user to configure the tool.
     *
     * @param modX $modx
     * @return array
     */
    public static function getConfig(modX $modx): array;

    /**
     * Your tool is being called by the model! Do the thing!
     *
     * @param array $arguments
     * @return string
     */
    public function runTool(array $arguments): string;
}
