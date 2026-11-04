<?php

namespace _64FF00\PureChat;

use _64FF00\PurePerms\PPGroup;
use _64FF00\PurePerms\PurePerms;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use RuntimeException;

class PureChat extends PluginBase
{
    /*
        PureChat by 64FF00 (Twitter: @64FF00)

          888  888    .d8888b.      d8888  8888888888 8888888888 .d8888b.   .d8888b.
          888  888   d88P  Y88b    d8P888  888        888       d88P  Y88b d88P  Y88b
        888888888888 888          d8P 888  888        888       888    888 888    888
          888  888   888d888b.   d8P  888  8888888    8888888   888    888 888    888
          888  888   888P "Y88b d88   888  888        888       888    888 888    888
        888888888888 888    888 8888888888 888        888       888    888 888    888
          888  888   Y88b  d88P       888  888        888       Y88b  d88P Y88b  d88P
          888  888    "Y8888P"        888  888        888        "Y8888P"   "Y8888P"
    */

    const MAIN_PREFIX = "\x5b\x50\x75\x72\x65\x43\x68\x61\x74\x3a\x36\x34\x46\x46\x30\x30\x5d";

    private Config $config;

    private PCMessages $messages;

    private PurePerms $purePerms;
    public function getPurePerms(): PurePerms
    {
        return $this->purePerms;
    }

    public function getthisConfig(): Config
    {
        return $this->config;
    }


    public function onLoad(): void
    {
        $this->saveDefaultConfig();
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        if(!is_string($this->config->get("language")))
        {
            $this->config->set("language", "en");
            $this->config->save();
            $this->config->reload();
        }
        $this->messages = new PCMessages($this);
        if(!$this->config->get("version"))
        {
            $version = $this->getDescription()->getVersion();
            $this->config->set("version", $version);
            $this->fixOldConfig();
        }

        $purePerms = $this->getServer()->getPluginManager()->getPlugin("PurePerms");
        if(!$purePerms instanceof PurePerms)
            throw new RuntimeException($this->getMessage("errors.pureperms_required"));
        $this->purePerms = $purePerms;
    }
    
    public function onEnable(): void
    {
        $this->getServer()->getPluginManager()->registerEvents(new PCListener($this), $this);
    }

    /**
     * @param CommandSender $sender
     * @param Command $cmd
     * @param string $label
     * @param list<string> $args
     */
    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool{
        switch(strtolower($cmd->getName()))
        {
            case "setformat":

                if(count($args) < 3)
                {
                    $sender->sendMessage(TextFormat::GREEN . self::MAIN_PREFIX . " " . $this->getMessage("cmds.setformat.usage"));
                    return true;
                }

                $group = $this->purePerms->getGroup($args[0]);

                if($group === null)
                {
                    $sender->sendMessage(TextFormat::RED . self::MAIN_PREFIX . " " . $this->getMessage("cmds.setformat.group_not_exist", $args[0]));

                    return true;
                }
                $WorldName = null;
                if($args[1] !== "null" and $args[1] !== "global")
                {
                    $World = $this->getServer()->getWorldManager()->getWorldByName($args[1]);
                    if ($World === null) {
                        $sender->sendMessage(TextFormat::RED . self::MAIN_PREFIX . " " . $this->getMessage("cmds.setformat.world_not_exist"));
                        return true;
                    }

                    $WorldName = $World->getDisplayName();
                }

                $chatFormat = implode(" ", array_slice($args, 2));
                $this->setOriginalChatFormat($group, $chatFormat, $WorldName);
                $sender->sendMessage(TextFormat::GREEN . self::MAIN_PREFIX . " " . $this->getMessage("cmds.setformat.success", $chatFormat));
                break;

            case "setprefix":

                if(!$sender instanceof Player)
                {
                    $sender->sendMessage(TextFormat::GREEN . self::MAIN_PREFIX . " " . $this->getMessage("cmds.setprefix.player_only"));
                    return true;
                }

                if(!isset($args[0]))
                {
                    $sender->sendMessage(TextFormat::GREEN . self::MAIN_PREFIX . " " . $this->getMessage("cmds.setprefix.usage"));
                    return true;
                }

                $WorldName = $this->config->get("enable-multiworld-chat") === true ? $sender->getWorld()->getDisplayName() : null;
                $prefix = str_replace("{BLANK}", ' ', implode('', $args));
                $this->setPrefix($prefix, $sender, $WorldName);
                $sender->sendMessage(TextFormat::GREEN . self::MAIN_PREFIX . " " . $this->getMessage("cmds.setprefix.success", $prefix));
                break;

            case "setsuffix":

                if(!$sender instanceof Player)
                {
                    $sender->sendMessage(TextFormat::GREEN . self::MAIN_PREFIX . " " . $this->getMessage("cmds.setsuffix.player_only"));
                    return true;
                }

                if(!isset($args[0]))
                {
                    $sender->sendMessage(TextFormat::GREEN . self::MAIN_PREFIX . " " . $this->getMessage("cmds.setsuffix.usage"));
                    return true;
                }

                $WorldName = $this->config->get("enable-multiworld-chat") === true ? $sender->getWorld()->getDisplayName() : null;
                $suffix = str_replace("{BLANK}", ' ', implode('', $args));
                $this->setSuffix($suffix, $sender, $WorldName);
                $sender->sendMessage(TextFormat::GREEN . self::MAIN_PREFIX . " " . $this->getMessage("cmds.setsuffix.success", $suffix));
                break;
        }

        return true;
    }

    private function fixOldConfig(): void
    {
        $tempData = $this->stringKeyedArray($this->config->getAll());
        $version = $this->getDescription()->getVersion();
        $tempData["version"] = $version;
        $tempData["enable-multiworld-chat"] = $tempData["enable-multiworld-support"] ?? false;
        if(isset($tempData["enable-multiworld-support"]))
            unset($tempData["enable-multiworld-support"]);

        $groups = $this->stringKeyedArray($tempData["groups"] ?? null);
        if($groups !== [])
        {
            $convertedGroups = [];
            foreach($groups as $groupName => $rawGroupData)
            {
                $tempGroupData = $this->stringKeyedArray($rawGroupData);
                if(isset($tempGroupData["default-chat"]))
                {
                    if(is_string($tempGroupData["default-chat"]))
                        $tempGroupData["chat"] = $this->fixOldData($tempGroupData["default-chat"]);
                    unset($tempGroupData["default-chat"]);
                }

                if(isset($tempGroupData["default-nametag"]))
                {
                    if(is_string($tempGroupData["default-nametag"]))
                        $tempGroupData["nametag"] = $this->fixOldData($tempGroupData["default-nametag"]);
                    unset($tempGroupData["default-nametag"]);
                }

                $worlds = $this->stringKeyedArray($tempGroupData["worlds"] ?? null);
                if($worlds !== [])
                {
                    $convertedWorlds = [];
                    foreach($worlds as $worldName => $rawWorldData)
                    {
                        $worldData = $this->stringKeyedArray($rawWorldData);
                        if(isset($worldData["default-chat"]))
                        {
                            if(is_string($worldData["default-chat"]))
                                $worldData["chat"] = $this->fixOldData($worldData["default-chat"]);
                            unset($worldData["default-chat"]);
                        }

                        if(isset($worldData["default-nametag"]))
                        {
                            if(is_string($worldData["default-nametag"]))
                                $worldData["nametag"] = $this->fixOldData($worldData["default-nametag"]);
                            unset($worldData["default-nametag"]);
                        }

                        $convertedWorlds[$worldName] = $worldData;
                    }
                    $tempGroupData["worlds"] = $convertedWorlds;
                }
                $convertedGroups[$groupName] = $tempGroupData;
            }
            $tempData["groups"] = $convertedGroups;
        }
        $this->config->setAll($tempData);
        $this->config->save();
        $this->config->reload();
        $this->getLogger()->notice($this->getMessage("logger_messages.config_upgraded"));
    }

    public function getMessage(string $node, mixed ...$vars): string
    {
        return $this->messages->getMessage($node, ...$vars);
    }

    /**
     * @param mixed $value
     * @return array<string, mixed>
     */
    private function stringKeyedArray(mixed $value): array
    {
        if(!is_array($value))
            return [];
        $result = [];
        foreach($value as $key => $item)
        {
            if(is_string($key))
                $result[$key] = $item;
        }
        return $result;
    }

    private function fixOldData(string $string): string
    {
        $string = str_replace("{COLOR_BLACK}", "&0", $string);
        $string = str_replace("{COLOR_DARK_BLUE}", "&1", $string);
        $string = str_replace("{COLOR_DARK_GREEN}", "&2", $string);
        $string = str_replace("{COLOR_DARK_AQUA}", "&3", $string);
        $string = str_replace("{COLOR_DARK_RED}", "&4", $string);
        $string = str_replace("{COLOR_DARK_PURPLE}", "&5", $string);
        $string = str_replace("{COLOR_GOLD}", "&6", $string);
        $string = str_replace("{COLOR_GRAY}", "&7", $string);
        $string = str_replace("{COLOR_DARK_GRAY}", "&8", $string);
        $string = str_replace("{COLOR_BLUE}", "&9", $string);
        $string = str_replace("{COLOR_GREEN}", "&a", $string);
        $string = str_replace("{COLOR_AQUA}", "&b", $string);
        $string = str_replace("{COLOR_RED}", "&c", $string);
        $string = str_replace("{COLOR_LIGHT_PURPLE}", "&d", $string);
        $string = str_replace("{COLOR_YELLOW}", "&e", $string);
        $string = str_replace("{COLOR_WHITE}", "&f", $string);
        $string = str_replace("{FORMAT_OBFUSCATED}", "&k", $string);
        $string = str_replace("{FORMAT_BOLD}", "&l", $string);
        $string = str_replace("{FORMAT_STRIKETHROUGH}", "&m", $string);
        $string = str_replace("{FORMAT_UNDERLINE}", "&n", $string);
        $string = str_replace("{FORMAT_ITALIC}", "&o", $string);
        $string = str_replace("{FORMAT_RESET}", "&r", $string);
        $string = str_replace("{world_name}", "{world}", $string);
        $string = str_replace("{user_name}", "{display_name}", $string);
        $string = str_replace("{message}", "{msg}", $string);
        return $string;
    }

    /*
          888  888          d8888 8888888b. 8888888
          888  888         d88888 888   Y88b  888
        888888888888      d88P888 888    888  888
          888  888       d88P 888 888   d88P  888
          888  888      d88P  888 8888888P"   888
        888888888888   d88P   888 888         888
          888  888    d8888888888 888         888
          888  888   d88P     888 888       8888888
    */




    public function getNametag(Player $player, ?string $WorldName = null): string
    {
        $originalNametag = $this->getOriginalNametag($player, $WorldName);
        $nameTag = $this->applyColors($originalNametag);
        $nameTag = $this->applyPCTags($nameTag, $player, null, $WorldName);
        return $nameTag;
    }



    public function getOriginalNametag(Player $player, ?string $WorldName = null): string
    {
        $group = $this->purePerms->getUserDataMgr()->getGroup($player, $WorldName);
        if($group === null)
            throw new RuntimeException("PurePerms returned no group for player");
        if($WorldName === null)
        {
        	$originalNametag = $this->config->getNested("groups." . $group->getName() . ".nametag");
            if(!is_string($originalNametag))
            {
                $this->getLogger()->critical($this->getMessage("logger_messages.invalid_nametag", $group->getName()));
                $this->config->setNested("groups." . $group->getName() . ".nametag", $originalNametag = "&8&l[" . $group->getName() . "]&f&r {display_name}");
                $this->config->save();
                $this->config->reload();
            }
            return $originalNametag;
        }
        else
        {
            $originalNametag = $this->config->getNested("groups." . $group->getName() . ".worlds.$WorldName.nametag");
            if(!is_string(($originalNametag)))
            {
                $this->getLogger()->critical($this->getMessage("logger_messages.invalid_nametag_world", $group->getName(), $WorldName));
                $this->config->setNested("groups." . $group->getName() . ".worlds.$WorldName.nametag", $originalNametag = "&8&l[" . $group->getName() . "]&f&r {display_name}");
                $this->config->save();
                $this->config->reload();
            }
            return $originalNametag;
        }
    }

    public function getPrefix(Player $player, ?string $WorldName = null): string
    {
        if($WorldName === null)
        {
        	$prefix = $this->purePerms->getUserDataMgr()->getNode($player, "prefix");
            return is_string($prefix) ? $prefix : '';
        }
        else
        {
            $worldData = $this->purePerms->getUserDataMgr()->getWorldData($player, $WorldName);
            if(!isset($worldData["prefix"]) || !is_string($worldData["prefix"]))
                return "";
            return $worldData["prefix"];
        }
    }

    public function getSuffix(Player $player, ?string $WorldName = null): string
    {
        if($WorldName === null)
        {
        	$suffix = $this->purePerms->getUserDataMgr()->getNode($player, "suffix");
            return is_string($suffix) ? $suffix : '';
        }
        else
        {
            $worldData = $this->purePerms->getUserDataMgr()->getWorldData($player, $WorldName);

            if(!isset($worldData["suffix"]) || !is_string($worldData["suffix"]))
                return "";
            return $worldData["suffix"];
        }
    }

    public function setOriginalChatFormat(PPGroup $group, string $chatFormat, ?string $WorldName = null): bool
    {
        if($WorldName === null)
        {
            $this->config->setNested("groups." . $group->getName() . ".chat", $chatFormat);
        }
        else
        {
            $this->config->setNested("groups." . $group->getName() . ".worlds.$WorldName.chat", $chatFormat);
        }
        $this->config->save();
        $this->config->reload();
        return true;
    }

    public function setOriginalNametag(PPGroup $group, string $nameTag, ?string $WorldName = null): bool
    {
        if($WorldName === null)
        {
            $this->config->setNested("groups." . $group->getName() . ".nametag", $nameTag);
        }
        else
        {
            $this->config->setNested("groups." . $group->getName() . ".worlds.$WorldName.nametag", $nameTag);
        }
        $this->config->save();
        $this->config->reload();
        return true;
    }

    public function setPrefix(string $prefix, Player $player, ?string $WorldName = null): bool
    {
        if($WorldName === null)
        {
            $this->purePerms->getUserDataMgr()->setNode($player, "prefix", $prefix);
        }
        else
        {
            $worldData = $this->purePerms->getUserDataMgr()->getWorldData($player, $WorldName);
            $worldData["prefix"] = $prefix;
            $this->purePerms->getUserDataMgr()->setWorldData($player, $WorldName, $worldData);
        }

        return true;
    }

    public function setSuffix(string $suffix, Player $player, ?string $WorldName = null): bool
    {
        if($WorldName === null)
        {
            $this->purePerms->getUserDataMgr()->setNode($player, "suffix", $suffix);
        }
        else
        {
            $worldData = $this->purePerms->getUserDataMgr()->getWorldData($player, $WorldName);
            $worldData["suffix"] = $suffix;
            $this->purePerms->getUserDataMgr()->setWorldData($player, $WorldName, $worldData);
        }

        return true;
    }

    public function stripColors(string $string): string
    {
        return TextFormat::clean($string);
    }

    public function applyColors(string $string): string
    {
        return TextFormat::colorize($string);
    }

    public function applyPCTags(string $string, Player $player, ?string $message, ?string $WorldName): string
    {
        if($message === null)
            $message = "";
        if($player->hasPermission("pchat.coloredMessages"))
        {
            $message = $this->applyColors($message);
        }
        else
        {
            $message = $this->stripColors($message);
        }
        return ChatTagFormatter::format(
            $string,
            $player->getDisplayName(),
            $message,
            $WorldName ?? "",
            $this->getPrefix($player, $WorldName),
            $this->getSuffix($player, $WorldName)
        );
    }
    public function getChatFormat(Player $player, ?string $message, ?string $WorldName = null): string
    {
        $originalChatFormat = $this->getOriginalChatFormat($player, $WorldName);
        $chatFormat = $this->applyColors($originalChatFormat);
        $chatFormat = $this->applyPCTags($chatFormat, $player, $message, $WorldName);
        return $chatFormat;
    }
    public function getOriginalChatFormat(Player $player, ?string $WorldName = null): string
    {
        $group = $this->purePerms->getUserDataMgr()->getGroup($player, $WorldName);
        if($group === null)
            throw new RuntimeException("PurePerms returned no group for player");
        if($WorldName === null)
        {
            $originalChatFormat = $this->config->getNested("groups." . $group->getName() . ".chat");
            if(!is_string($originalChatFormat))
            {
                $this->getLogger()->critical($this->getMessage("logger_messages.invalid_chat_format", $group->getName()));
                $this->config->setNested("groups." . $group->getName() . ".chat", $originalChatFormat = "&8&l[" . $group->getName() . "]&f&r {display_name} &7> {msg}");
                $this->config->save();
                $this->config->reload();
            }

            return $originalChatFormat;
        }
        else
        {
            $originalChatFormat = $this->config->getNested("groups." . $group->getName() . ".worlds.$WorldName.chat");
            if(!is_string($originalChatFormat))
            {
                $this->getLogger()->critical($this->getMessage("logger_messages.invalid_chat_format_world", $group->getName(), $WorldName));
                $this->config->setNested("groups." . $group->getName() . ".worlds.$WorldName.chat", $originalChatFormat = "&8&l[" . $group->getName() . "]&f&r {display_name} &7> {msg}");
                $this->config->save();
                $this->config->reload();
            }

            return $originalChatFormat;
        }
    }


}
