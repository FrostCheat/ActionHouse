<?php

namespace frostcheat\actionhouse\commands\subcommands;

use frostcheat\actionhouse\language\LanguageManager;
use frostcheat\actionhouse\language\TranslationMessages;
use frostcheat\actionhouse\libs\CortexPE\Commando\BaseSubCommand;
use frostcheat\actionhouse\libs\CortexPE\Commando\constraint\InGameRequiredConstraint;
use frostcheat\actionhouse\npc\ActionHouseNPC;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class NPCSubCommand extends BaseSubCommand
{

    public function __construct()
    {
        parent::__construct("npc", "For spawn a ActionHouse NPC");
        $this->setPermission("actionhouse.command.npc");
    }

    /**
     * @inheritDoc
     */
    protected function prepare(): void
    {
        $this->addConstraint(new InGameRequiredConstraint($this));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(LanguageManager::getInstance()->getPrefix() . LanguageManager::getInstance()->getTranslation(TranslationMessages::COMMAND_NO_PLAYER));
            return;
        }

        $npc = new ActionHouseNPC($sender->getLocation(), $sender->getSkin());
        $npc->spawnToAll();
        $sender->sendMessage(LanguageManager::getInstance()->getPrefix() . LanguageManager::getInstance()->getTranslation(TranslationMessages::NPC_SPAWN));
    }
}