<?php

namespace _64FF00\PureChat;

use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

final class FactionTagProvider
{
    private function __construct(
        private Plugin $plugin,
        private string $type
    )
    {
    }

    public static function create(Plugin $plugin, string $type): ?self
    {
        $type = strtolower(trim($type));
        $supported = ["piggyfactions", "factionspro", "xeviouspe-factions"];
        return in_array($type, $supported, true) && $plugin->isEnabled() ? new self($plugin, $type) : null;
    }

    public function getFactionName(Player $player): string
    {
        $faction = $this->getFaction($player);
        if($faction === null)
            return "";
        if(is_string($faction))
            return $faction;
        if(is_object($faction) && method_exists($faction, "getName"))
        {
            $name = $faction->getName();
            return is_scalar($name) ? (string) $name : "";
        }
        return "";
    }

    public function getFactionRank(Player $player): string
    {
        if($this->type === "piggyfactions")
        {
            $member = $this->getPiggyMember($player);
            if($member === null || !method_exists($member, "getRole"))
                return "";
            $role = $member->getRole();
            return is_scalar($role) ? (string) $role : "";
        }

        $api = $this->getLegacyApi();
        if($api === null || !method_exists($api, "isInFaction") || !$api->isInFaction($player->getName()))
            return "";
        if(method_exists($api, "isLeader") && $api->isLeader($player->getName()))
            return "**";
        if(method_exists($api, "isOfficer") && $api->isOfficer($player->getName()))
            return "*";
        return "";
    }

    private function getFaction(Player $player): mixed
    {
        if($this->type === "piggyfactions")
        {
            $member = $this->getPiggyMember($player);
            if($member !== null && method_exists($member, "getFaction"))
                return $member->getFaction();
            return null;
        }

        $api = $this->getLegacyApi();
        if($api === null)
            return null;
        if($this->type === "factionspro" && method_exists($api, "getFaction"))
            return $api->getFaction($player->getName());
        if(method_exists($api, "getPlayerFaction"))
            return $api->getPlayerFaction($player->getName());
        return null;
    }

    private function getPiggyMember(Player $player): ?object
    {
        if(!method_exists($this->plugin, "getPlayerManager"))
            return null;
        $manager = $this->plugin->getPlayerManager();
        if(!is_object($manager) || !method_exists($manager, "getPlayer"))
            return null;
        $member = $manager->getPlayer($player);
        return is_object($member) ? $member : null;
    }

    private function getLegacyApi(): ?object
    {
        if(!method_exists($this->plugin, "getAPI"))
            return null;
        $api = $this->plugin->getAPI();
        if($this->type === "xeviouspe-factions" && is_object($api) && method_exists($api, "getProvider"))
            $api = $api->getProvider();
        return is_object($api) ? $api : null;
    }
}
