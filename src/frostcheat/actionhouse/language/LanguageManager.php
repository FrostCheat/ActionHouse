<?php

namespace frostcheat\actionhouse\language;

use frostcheat\actionhouse\Loader;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\TextFormat;

final class LanguageManager
{
    use SingletonTrait;
    private const DEFAULT_LANGUAGE = "en-US";
    public const SUPPORTED_LANGUAGES = [
        "en-US",
        "es-ES",
        "fr-FR",
        "pr-BR",
        "ru-RU",
        "de-DE"
    ];

    private string $language;
    private array $translations;

    public function init(Loader $plugin, ?string $language): void
    {
        $languagesFolder = $plugin->getDataFolder() . "language";
        @mkdir($languagesFolder);

        foreach (LanguageManager::SUPPORTED_LANGUAGES as $languageCode) {
            $plugin->saveResource("language" . DIRECTORY_SEPARATOR . $languageCode . ".yml");
        }

        if (!$language || !in_array($language, LanguageManager::SUPPORTED_LANGUAGES)) {
            $language = LanguageManager::DEFAULT_LANGUAGE;
        }

        $this->language = $language;
    }

    public function getTranslation(string $translation, array $variables = []): string|array {
        $config = new Config(
            Loader::getInstance()->getDataFolder() . "language" . DIRECTORY_SEPARATOR . $this->language . ".yml",
            Config::YAML
        );

        $message = $config->get($translation, "Not Found");

        if (is_array($message)) {
            foreach ($message as &$line) {
                $line = str_replace(array_keys($variables), array_values($variables), $line);
                $line = TextFormat::colorize($line);
            }
            return $message;
        }

        $message = str_replace(array_keys($variables), array_values($variables), $message);
        return TextFormat::colorize($message);
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function setLanguage(string $language): void {
        $this->language = $language;
    }

    public function getPrefix(): string
    {
        return $this->getTranslation(TranslationMessages::PREFIX);
    }
}