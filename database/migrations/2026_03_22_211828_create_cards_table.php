<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cards', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('pack_id');
            $table->string('name');
            $table->string('rarity');
            $table->string('category');
            $table->json('colors');
            $table->integer('cost')->nullable();
            $table->integer('power')->nullable();
            $table->integer('counter')->nullable();
            $table->json('attributes');
            $table->json('types');
            $table->text('effect')->nullable();
            $table->text('trigger')->nullable();
            $table->string('img_url');
            $table->timestamps();

            $table->foreign('pack_id')->references('id')->on('packs');
        });
    }
};
