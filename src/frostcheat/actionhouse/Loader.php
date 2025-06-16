<?php

namespace frostcheat\actionhouse;

use frostcheat\actionhouse\commands\ActionHouseCommand;
use frostcheat\actionhouse\house\HouseManager;
use frostcheat\actionhouse\language\LanguageManager;
use frostcheat\actionhouse\libs\CortexPE\Commando\exception\HookAlreadyRegistered;
use frostcheat\actionhouse\libs\CortexPE\Commando\PacketHooker;
use frostcheat\actionhouse\libs\JackMD\ConfigUpdater\ConfigUpdater;
use frostcheat\actionhouse\libs\JackMD\UpdateNotifier\UpdateNotifier;
use frostcheat\actionhouse\libs\muqsit\invmenu\InvMenuHandler;
use frostcheat\actionhouse\npc\ActionHouseNPC;
use frostcheat\actionhouse\provider\Provider;
use pocketmine\command\Command;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\World;

class Loader extends PluginBase
{
    use SingletonTrait;

    private const CONFIG_VERSION = 1;

    public function onLoad(): void {
        self::setInstance($this);
        LanguageManager::getInstance()->init($this, $this->getConfig()->get("language"));
        HouseManager::getInstance()->init();
    }

    /**
     * @throws HookAlreadyRegistered
     */
    public function onEnable(): void {
        UpdateNotifier::checkUpdate($this->getDescription()->getName(), $this->getDescription()->getVersion());
        if (ConfigUpdater::checkUpdate($this, $this->getConfig(), "config-version", self::CONFIG_VERSION)) {
            $this->reloadConfig();
        }

        if (!PacketHooker::isRegistered())
            PacketHooker::register($this);

        if (!InvMenuHandler::isRegistered())
            InvMenuHandler::register($this);

        $this->saveDefaultConfig();
        $this->saveResource("language/de-DE.yml");
        $this->saveResource("language/en-US.yml");
        $this->saveResource("language/es-ES.yml");
        $this->saveResource("language/fr-FR.yml");
        $this->saveResource("language/pr-IT.yml");
        $this->saveResource("language/ru-ru.yml");

        $this->registerCommands([
            new ActionHouseCommand(),
        ]);

        $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (): void {
            Provider::getInstance()->saveItems();
        }), 60 * 20);

        EntityFactory::getInstance()->register(ActionHouseNPC::class, function (World $world, CompoundTag $nbt) : ActionHouseNPC {
            return new ActionHouseNPC(EntityDataHelper::parseLocation($nbt, $world), ActionHouseNPC::parseSkinNBT($nbt), $nbt);
        }, ['ActionHouseNPC']);

        $this->getLogger()->info("Plugin has been enabled - author Fr0stCh34t");
        $this->getLogger()->info("Default Language: " . LanguageManager::getInstance()->getLanguage());
        $this->getLogger()->info(count(HouseManager::getInstance()->getItems()) . " items saved");
    }

    private function registerCommands(array $commands): void {
        foreach ($commands as $command) {
            if ($command instanceof Command) {
                $this->getServer()->getCommandMap()->register("actionhouse", $command);
            }
        }
    }

    protected function onDisable(): void
    {
        $this->getLogger()->info("Plugin has been disabled - author Fr0stCh34t");
        Provider::getInstance()->saveItems();
    }

}
?>