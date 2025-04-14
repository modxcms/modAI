<?php

namespace modAI\Tools;

use MODX\Revolution\modX;

class GetWeather implements ToolInterface
{
    private $modx;

    public static function getSuggestedName(): string
    {
        return 'get_weather';
    }

    public static function getDescription(): string
    {
        return "Get the current weather in the provided location. Location must be provided as latitude and longitude, but don't ask users for that. Instead ask users for the location and then transform that to latitude/longitude. You will get this information: temperature, apparent temperature, humidity, wind speed, wind direction, cloud cover, precipitation, rain, snowfall. Don't list all variables in your output but use natural language.";
    }

    public static function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'latitude' => [
                    'type' => 'number'
                ],
                'longitude' => [
                    'type' => 'number'
                ],
            ],
            "required" => ["latitude", "longitude"]
        ];
    }

    public static function getConfig(): array
    {
        return [];
    }

    public function __construct(modX $modx, array $config)
    {
        $this->modx = $modx;
    }

    /**
     * @param array | null $parameters
     * @return string
     */
    public function runTool($parameters): string
    {
        if (!self::checkPermissions($this->modx)) {
            return json_encode(['success' => false, "message" => "You do not have permission to use this tool."]);
        }

        try {
            $res = file_get_contents(
                "https://api.open-meteo.com/v1/forecast?latitude={$parameters['latitude']}&longitude={$parameters['longitude']}&current=temperature_2m,relative_humidity_2m,apparent_temperature,is_day,precipitation,rain,showers,snowfall,cloud_cover,pressure_msl,surface_pressure,wind_speed_10m,wind_direction_10m,wind_gusts_10m&timezone=auto&forecast_days=1"
            );
            $data = json_decode($res, true);

            $output = [];
            foreach ($data['current'] as $key => $value) {
                $output[$key] = [
                    'value' => $value,
                    'unit' => $data['current_units'][$key] ?? '',
                ];
            }

            return json_encode($output, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            return json_encode(['success' => false, "message" => "Received an error looking up the weather: {$e->getMessage()}"]);
        }
    }

    public static function checkPermissions(modX $modx): bool
    {
        return true;
    }
}
