<?php

namespace App\Mcp\Tools;

use App\Models\Pack;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('List all One Piece TCG packs.')]
class ListPacksTool extends Tool
{
    public function handle(Request $request): Response
    {
        $packs = Pack::orderBy('id')->get(['id', 'name', 'label']);

        return Response::json($packs->toArray());
    }

    /**
     * @return array<string, JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
