<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\GetCardTool;
use App\Mcp\Tools\GetPackTool;
use App\Mcp\Tools\ListCardsTool;
use App\Mcp\Tools\ListPacksTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('One Piece Cards')]
#[Version('1.0.0')]
#[Instructions('Use this server to query One Piece TCG card data. Available tools: list-packs (all packs), get-pack (pack with cards), list-cards (filterable card list), get-card (single card).')]
class CardsServer extends Server
{
    protected array $tools = [
        ListPacksTool::class,
        GetPackTool::class,
        ListCardsTool::class,
        GetCardTool::class,
    ];
}
