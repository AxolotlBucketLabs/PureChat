<?php

namespace _64FF00\PureChat;
use pocketmine\player\chat\ChatFormatter;
use pocketmine\lang\Translatable;
use pocketmine\player\Player;
use RuntimeException;
class ChatFormat implements ChatFormatter
{

    private PureChat $PureChat;
    private Player $player;
    private ?string $WorldName;

    public function __construct(Player $player, string $message, ?string $WorldName, PureChat $PureChat)
    {
        $this->PureChat = $PureChat;
        $this->player = $player;
        $this->WorldName = $WorldName;
        $this->format("", $message);
    }

    public function format(string $username, string $message): Translatable|string{
        $msg = $this->PureChat->getChatFormat($this->player, $message, $this->WorldName);
        return $msg;
    }
}
