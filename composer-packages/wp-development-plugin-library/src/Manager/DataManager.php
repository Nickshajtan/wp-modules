<?php

namespace HCC\Plugin\Manager;

/**
 * Class for managing plugin text data
 */
class DataManager
{
    protected \stdClass $pluginData;

    public function __construct(string $mainPluginFile, CallbacksStore $callbacks)
    {
        $pluginData = $this->setupPluginData($mainPluginFile, $callbacks);
        $this->setPluginData(array_combine(
            array_map(fn($key) => lcfirst($key), array_keys($pluginData)),
            array_values($pluginData)
        ));
    }

    protected function setupPluginData(string $dataPluginFile, CallbacksStore $callbacks): array
    {
        return array_merge(
            call_user_func($callbacks->dataReader, $dataPluginFile, false, true),
            array(
                'basePath' => call_user_func($callbacks->pathReader, $dataPluginFile),
                'baseUrl' => call_user_func($callbacks->urlReader, $dataPluginFile),
                'mainFile' => basename($dataPluginFile),
            )
        );
    }

    public function modifyPluginData(array $data): void
    {
        $this->pluginData = (object) array_merge(
            (array) $this->pluginData,
            $data
        );
    }

    public function setPluginData(array $pluginData): void
    {
        $this->pluginData = (object) $pluginData;
    }

    public function getPluginData(): \stdClass
    {
        return $this->pluginData;
    }
}
