<?php

namespace App\Mcp\Tools;

use App\Models\Pack;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Get a One Piece TCG pack with all its cards by pack ID.')]
class GetPackTool extends Tool
{
    public function handle(Request $request): Response
    {
        $packId = $request->get('pack_id');

        try {
            $pack = Pack::with('cards')->findOrFail($packId);
        } catch (ModelNotFoundException) {
            return Response::error("Pack '{$packId}' not found.");
        }

        return Response::json($pack->toArray());
    }

    /**
     * @return array<string, JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'pack_id' => $schema->string()->required()->description('The pack ID, e.g. "OP-01"'),
        ];
    }
}
