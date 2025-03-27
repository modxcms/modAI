<?php

namespace modAI\API\Prompt;

use modAI\API\API;
use modAI\Exceptions\LexiconException;
use modAI\Services\AIServiceFactory;
use modAI\Services\Config\ImageConfig;
use modAI\Settings;
use Psr\Http\Message\ServerRequestInterface;

class Image extends API
{
    public function post(ServerRequestInterface $request): void
    {
        set_time_limit(0);

        $data = $request->getParsedBody();

        $prompt = $this->modx->getOption('prompt', $data);
        $field = $this->modx->getOption('field', $data, '');
        $namespace = $this->modx->getOption('namespace', $data, 'modai');

        if (empty($prompt)) {
            throw new LexiconException('modai.error.prompt_required');
        }

        $model = Settings::getImageSetting($this->modx, $field, 'model', $namespace);
        $size = Settings::getImageSetting($this->modx, $field, 'size', $namespace);
        $quality = Settings::getImageSetting($this->modx, $field, 'quality', $namespace);
        $style = Settings::getImageSetting($this->modx, $field, 'style', $namespace);
        $customOptions = Settings::getImageSetting($this->modx, $field, 'custom_options', $namespace, false);

        $aiService = AIServiceFactory::new($model, $this->modx);
        $result = $aiService->generateImage(
            $prompt,
            ImageConfig::new($model)
                ->customOptions($customOptions)
                ->size($size)
                ->quality($quality)
                ->style($style)
        );

        $this->proxyAIResponse($result);

//        header("x-modai-service: chatgpt");
//        header("x-modai-parser: image");
//        header("x-modai-stream: 0");
//        header("x-modai-proxy: 1");
//
//        $this->success([
//            "created" => 1741978325,
//            "data" => [
//                [
//                    "revised_prompt" => "Visualize a delightful scene of diverse array of puppies. Include a variety of breeds: a black Labrador, a golden Retriever, a dalmatian, and a poodle, playing merrily in a grassy park. The sun is shining bright in the blue sky, casting golden rays on the puppies as they frolick around, chasing each other and their tails. They are innocently engaged in their playful antics, unaware of the world around them. Their joyous barks and wagging tails depict the vibrancy of their young lives. They embody pure innocence and boundless energy.",
//                    "url" => "https://oaidalleapiprodscus.blob.core.windows.net/private/org-mVSFcxs98WiJjLumY11bjhrz/user-9JkYqLT6GTOuJBFdlVL6iJFG/img-31ggmDoh71G1bfz1olEMEofk.png?st=2025-03-24T15%3A43%3A44Z&se=2025-03-24T17%3A43%3A44Z&sp=r&sv=2024-08-04&sr=b&rscd=inline&rsct=image/png&skoid=d505667d-d6c1-4a0a-bac7-5c84a87759f8&sktid=a48cca56-e6da-484e-a814-9c849652bcb3&skt=2025-03-24T08%3A25%3A16Z&ske=2025-03-25T08%3A25%3A16Z&sks=b&skv=2024-08-04&sig=K%2BCaXLZSFUJuF%2BmBu7gEYsjUUj5kQga3W6JP5h6I6A0%3D"
//                ]
//            ]
//        ]);
    }
}
