<?php

$finder = (PhpCsFixer\Finder::create())
    ->in([
        __DIR__,
    ]);

$config = new class() extends PhpCsFixer\Config {
    public function __construct()
    {
        parent::__construct('customized Bedrock Streaming');
        $this->setRiskyAllowed(true);
    }

    public function getRules(): array
    {
        // Merge base rules and disable declare_strict_types
        return array_merge(
            (new M6Web\CS\Config\BedrockStreaming())->getRules(),
            [
                'declare_strict_types' => false,
                '@PHP81Migration' => true,
                '@PSR12' => true,
                'array_syntax' => ['syntax' => 'short'],
            ]
        );
    }
};

$config->setFinder($finder);

return $config;
