<?php

declare(strict_types=1);

require dirname(__DIR__) . "/src/_64FF00/PureChat/ChatTagFormatter.php";

$result = _64FF00\PureChat\ChatTagFormatter::format(
    "{display_name} > {msg} @ {world} {prefix}{suffix}",
    "Alice",
    "hello {world} {prefix}",
    "lobby",
    "[VIP]",
    "!",
    "[Red]",
    "member"
);
$expected = "Alice > hello {world} {prefix} @ lobby [VIP]!";
if($result !== $expected)
    throw new RuntimeException("Chat tag regression failed: $result");

$factionResult = _64FF00\PureChat\ChatTagFormatter::format(
    "{fac_rank}{fac_name} {display_name}",
    "Alice",
    "",
    "lobby",
    "",
    "",
    "[Red]",
    "member"
);
if($factionResult !== "member[Red] Alice")
    throw new RuntimeException("Faction tag regression failed: $factionResult");

$source = file_get_contents(dirname(__DIR__) . "/src/_64FF00/PureChat/PureChat.php");
if($source === false || substr_count($source, '".worlds.$WorldName') < 4)
    throw new RuntimeException("Multiworld configuration path regression failed");
if(!str_contains($source, 'case "giveprefix"') || !str_contains($source, 'case "givesuffix"'))
    throw new RuntimeException("Prefix/suffix alias regression failed");
if(!str_contains($source, 'ScoreHudIntegration::register'))
    throw new RuntimeException("ScoreHud integration registration regression failed");

$config = file_get_contents(dirname(__DIR__) . "/resources/config.yml");
if($config === false || !str_contains($config, "default-factions-plugin"))
    throw new RuntimeException("Faction integration configuration regression failed");

$expectedLanguages = ["eng", "vie", "kor", "rus", "spa", "ukr", "zho", "ind", "tur", "fra", "por", "deu", "jpn", "ita"];
$languageFiles = glob(dirname(__DIR__) . "/resources/lang/messages-*.yml");
if($languageFiles === false)
    throw new RuntimeException("Language directory scan failed");
$actualLanguages = [];
foreach($languageFiles as $languageFile)
{
    if(preg_match('/messages-([a-z]{3})\.yml$/', $languageFile, $matches) === 1)
        $actualLanguages[] = $matches[1];
}
sort($actualLanguages);
$expectedLanguagesSorted = $expectedLanguages;
sort($expectedLanguagesSorted);
if($actualLanguages !== $expectedLanguagesSorted)
    throw new RuntimeException("PureChat language coverage regression failed");

echo "PureChat regressions: PASS\n";
