<?php

namespace _64FF00\PureChat;

final class ChatTagFormatter
{
    public static function format(
        string $format,
        string $displayName,
        string $message,
        string $world,
        string $prefix,
        string $suffix,
        string $factionName = "",
        string $factionRank = ""
    ): string {
        return strtr($format, [
            "{display_name}" => $displayName,
            "{msg}" => $message,
            "{fac_name}" => $factionName,
            "{fac_rank}" => $factionRank,
            "{world}" => $world,
            "{prefix}" => $prefix,
            "{suffix}" => $suffix,
        ]);
    }
}
