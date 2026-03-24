<?php

namespace App\Http\Controllers;

use App\Http\Resources\PackResource;
use App\Models\Pack;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PacksController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return PackResource::collection(
            Pack::query()->orderBy('id')->get()
        );
    }

    public function show(Pack $pack): PackResource
    {
        return new PackResource($pack->load('cards'));
    }
}
