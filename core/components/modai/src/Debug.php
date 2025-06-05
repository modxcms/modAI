<?php
namespace modAI;

class Debug {
    public static function textResponse(string $content)
    {
        header("x-modai-service: openai");
        header("x-modai-model: debug");
        header("x-modai-parser: content");
        header("x-modai-stream: 0");
        header("x-modai-proxy: 1");

        $data = [
            "id" => "debug-" . time() . '-' . rand(11111,99999),
            "object" => "chat.completion",
            "created" => 1745303790,
            "model" => "gpt-4o-mini-2024-07-18",
            "choices" => [
                [
                    "index" => 0,
                    "message" => [
                        "role" => "assistant",
                        "content" => $content,
                        "refusal" => null,
                        "annotations" => []
                    ],
                    "logprobs" => null,
                    "finish_reason" => "stop",
                ],
            ],
            "usage" => [
                "prompt_tokens" => 65,
                "completion_tokens" => 3,
                "total_tokens" => 68,
                "prompt_tokens_details" => [
                    "cached_tokens" => 0,
                    "audio_tokens" => 0,
                ],
                "completion_tokens_details" => [
                    "reasoning_tokens" => 0,
                    "audio_tokens" => 0,
                    "accepted_prediction_tokens" => 0,
                    "rejected_prediction_tokens" => 0,
                ],
            ],
            "service_tier" => "default",
            "system_fingerprint" => "fp_f7d56a8a2c",
        ];

        http_response_code(200);
        header('Content-Type: application/json');

        echo json_encode($data);
    }
}
