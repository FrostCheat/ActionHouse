<?php

namespace frostcheat\actionhouse\commands\subcommands;

use frostcheat\actionhouse\language\LanguageManager;
use frostcheat\actionhouse\language\TranslationMessages;
use frostcheat\actionhouse\libs\CortexPE\Commando\args\RawStringArgument;
use frostcheat\actionhouse\libs\CortexPE\Commando\BaseSubCommand;
use frostcheat\actionhouse\libs\CortexPE\Commando\exception\ArgumentOrderException;
use frostcheat\actionhouse\Loader;
use JsonException;
use pocketmine\command\CommandSender;

class SetLanguageSubCommand extends BaseSubCommand
{
    public function __construct()
    {
        parent::__construct("setlanguage", "Set language of messages");
        $this->setPermission("actionhouse.command.setlanguage");
    }

    /**
     * @inheritDoc
     * @throws ArgumentOrderException
     */
    protected function prepare(): void
    {
        $this->registerArgument(0, new RawStringArgument("language"));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $language = $args["language"];

        if (!in_array($language, LanguageManager::SUPPORTED_LANGUAGES)) {
            $sender->sendMessage(LanguageManager::getInstance()->getPrefix() . LanguageManager::getInstance()->getTranslation(TranslationMessages::COMMAND_SETLANGUAGE_ERROR));
            return;
        }

        Loader::getInstance()->getConfig()->set("language", $language);
        try {
            Loader::getInstance()->getConfig()->save();
        } catch (JsonException $e) {
            Loader::getInstance()->getLogger()->error($e->getMessage());
        }
        LanguageManager::getInstance()->setLanguage($language);
        $sender->sendMessage(LanguageManager::getInstance()->getPrefix() . LanguageManager::getInstance()->getTranslation(TranslationMessages::COMMAND_SETLANGUAGE_SUCCESS));
    }
}