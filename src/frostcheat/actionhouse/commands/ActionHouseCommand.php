<?php

namespace frostcheat\actionhouse\commands;

use frostcheat\actionhouse\commands\subcommands\NPCSubCommand;
use frostcheat\actionhouse\commands\subcommands\SellSubCommand;
use frostcheat\actionhouse\commands\subcommands\SetLanguageSubCommand;
use frostcheat\actionhouse\house\HouseManager;
use frostcheat\actionhouse\language\LanguageManager;
use frostcheat\actionhouse\language\TranslationMessages;
use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\constraint\InGameRequiredConstraint;
use frostcheat\actionhouse\Loader;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class ActionHouseCommand extends BaseCommand
{
    public function __construct()
    {
        parent::__construct(Loader::getInstance(), "actionhouse", "Action House Main Command", ["ah"]);
        $this->setPermission("actionhouse.command");
    }

    /**
     * @inheritDoc
     */
    protected function prepare(): void
    {
        $this->addConstraint(new InGameRequiredConstraint($this));
        $this->registerSubCommand(new SellSubCommand());
        $this->registerSubCommand(new NPCSubCommand());
        $this->registerSubCommand(new SetLanguageSubCommand());
    }

    /**
     * @inheritDoc
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(LanguageManager::getInstance()->getPrefix() . LanguageManager::getInstance()->getTranslation(TranslationMessages::COMMAND_NO_PLAYER));
            return;
        }

        HouseManager::getInstance()->sendMainMenu($sender);
    }
}