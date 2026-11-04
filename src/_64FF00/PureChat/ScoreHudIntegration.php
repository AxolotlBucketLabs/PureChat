<?php

namespace _64FF00\PureChat;

use pocketmine\event\Event;
use pocketmine\event\EventPriority;
use pocketmine\player\Player;
use RuntimeException;

final class ScoreHudIntegration
{
    /** @devtools-allow-dynamic-class $eventClass */
    public static function register(PureChat $plugin): bool
    {
        $eventClass = "Ifera\\ScoreHud\\event\\TagsResolveEvent";
        $scoreHud = $plugin->getServer()->getPluginManager()->getPlugin("ScoreHud");
        if($scoreHud === null || !$scoreHud->isEnabled() || version_compare($scoreHud->getDescription()->getVersion(), "8.0.0", "<"))
            return false;
        if(!class_exists($eventClass))
            throw new RuntimeException("ScoreHud TagsResolveEvent is unavailable.");
        if(!is_subclass_of($eventClass, Event::class))
            throw new RuntimeException("ScoreHud TagsResolveEvent is not a PocketMine event.");

        $plugin->getServer()->getPluginManager()->registerEvent(
            $eventClass,
            function(object $event) use ($plugin): void {
                self::resolve($plugin, $event);
            },
            EventPriority::NORMAL,
            $plugin
        );
        return true;
    }

    private static function resolve(PureChat $plugin, object $event): void
    {
        if(!method_exists($event, "getPlayer") || !method_exists($event, "getTag"))
            return;
        $player = $event->getPlayer();
        $tag = $event->getTag();
        if(!$player instanceof Player || !is_object($tag) || !method_exists($tag, "getName") || !method_exists($tag, "setValue"))
            return;

        $tagName = $tag->getName();
        if(!is_string($tagName))
            return;
        $worldName = $plugin->getConfig()->get("enable-multiworld-chat") === true ? $player->getWorld()->getDisplayName() : null;
        $value = match($tagName) {
            "purechat.prefix", "pureperms.prefix" => $plugin->getPrefix($player, $worldName),
            "purechat.suffix", "pureperms.suffix" => $plugin->getSuffix($player, $worldName),
            "pureperms.group", "pureperms.rank" => $plugin->getGroupName($player, $worldName),
            "purechat.faction_name" => $plugin->getFactionName($player),
            "purechat.faction_rank" => $plugin->getFactionRank($player),
            default => null,
        };
        if($value !== null)
            $tag->setValue($value);
    }
}
