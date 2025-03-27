<?php
namespace modAI\Tools;

class GetWeather {
    public static function getName(): string
    {
        return 'get_weather';
    }

    public static function getDescription(): string
    {
        //Before calling this function, require the user to approve the tool call in a separate message.
        return 'Get the current weather in the provided location. Location must be provided as latitude and longitude, but don\'t ask users for that. Instead ask users for the location and then transform that to latitude/longitude. You will get this information: temperature. Don\'t list all variables in your output but use natural language.';
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

    /**
     * @param array | null $parameters
     * @return string
     */
    public function runTool($parameters): string
    {
        $res = file_get_contents("https://api.open-meteo.com/v1/forecast?latitude={$parameters['latitude']}&longitude={$parameters['longitude']}&current=temperature_2m");
        $data = json_decode($res, true);

        return json_encode([
            'temperature_2m' => $data['current']['temperature_2m'],
            'unit' => $data['current_units']['temperature_2m']
        ]);
    }
}
