<?php

namespace App\Mcp\Tools;

use App\Models\Card;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Get a single One Piece TCG card by card ID.')]
class GetCardTool extends Tool
{
    public function handle(Request $request): Response
    {
        $cardId = $request->get('card_id');

        try {
            $card = Card::findOrFail($cardId);
        } catch (ModelNotFoundException) {
            return Response::error("Card '{$cardId}' not found.");
        }

        return Response::json($card->toArray());
    }

    /**
     * @return array<string, JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'card_id' => $schema->string()->required()->description('The card ID, e.g. "OP01-001"'),
        ];
    }
}
