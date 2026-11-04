<?php

namespace _64FF00\PureChat;

use pocketmine\utils\Config;

class PCMessages
{
    /** @var array<string, string> */
    private const LANGUAGE_ALIASES = [
        "en" => "eng",
        "vi" => "vie",
        "ko" => "kor",
        "ru" => "rus",
        "es" => "spa",
        "uk" => "ukr",
        "zh" => "zho",
        "id" => "ind",
        "tr" => "tur",
        "fr" => "fra",
        "pt" => "por",
        "de" => "deu",
        "ja" => "jpn",
        "it" => "ita",
    ];

    private PureChat $plugin;
    private Config $messages;

    /** @var list<string> */
    private array $langList = [];

    public function __construct(PureChat $plugin)
    {
        $this->plugin = $plugin;
        $this->registerLanguages();
        $this->loadMessages();
    }

    public function registerLanguages(): void
    {
        $this->langList = [];
        foreach($this->plugin->getResources() as $resource)
        {
            $resourceName = strtolower($resource->getFilename());
            if(preg_match('/^messages-([a-z]{3})\.yml$/', $resourceName, $matches) === 1)
                $this->langList[] = strtolower($matches[1]);
        }
        $this->langList = array_values(array_unique($this->langList));
    }

    public function getMessage(string $node, mixed ...$vars): string
    {
        $message = $this->messages->getNested($node);
        if(!is_string($message))
            return "";

        foreach($vars as $number => $value)
            $message = str_replace("%var$number%", is_scalar($value) ? (string) $value : "", $message);

        return $message;
    }

    private function loadMessages(): void
    {
        $languageValue = $this->plugin->getthisConfig()->get("language");
        $language = is_string($languageValue) ? $this->normalizeLanguage($languageValue) : "eng";
        if(!in_array($language, $this->langList, true))
        {
            $this->plugin->getLogger()->warning("Language resource $language not found. Falling back to English.");
            $language = "eng";
        }

        $resourceName = "lang/messages-$language.yml";
        $this->plugin->saveResource($resourceName);
        $this->messages = new Config($this->plugin->getDataFolder() . $resourceName, Config::YAML);
        $this->plugin->getLogger()->info($this->getMessage("logger_messages.language_selected", $language));
    }

    public function reloadMessages(): void
    {
        $this->registerLanguages();
        $this->loadMessages();
    }

    private function normalizeLanguage(string $language): string
    {
        $language = strtolower(trim($language));
        return self::LANGUAGE_ALIASES[$language] ?? $language;
    }
}
