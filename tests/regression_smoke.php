<?php

declare(strict_types=1);

require dirname(__DIR__) . "/src/_64FF00/PureChat/ChatTagFormatter.php";

$result = _64FF00\PureChat\ChatTagFormatter::format(
    "{display_name} > {msg} @ {world} {prefix}{suffix}",
    "Alice",
    "hello {world} {prefix}",
    "lobby",
    "[VIP]",
    "!"
);
$expected = "Alice > hello {world} {prefix} @ lobby [VIP]!";
if($result !== $expected)
    throw new RuntimeException("Chat tag regression failed: $result");

$source = file_get_contents(dirname(__DIR__) . "/src/_64FF00/PureChat/PureChat.php");
if($source === false || substr_count($source, '".worlds.$WorldName') < 4)
    throw new RuntimeException("Multiworld configuration path regression failed");

echo "PureChat regressions: PASS\n";
