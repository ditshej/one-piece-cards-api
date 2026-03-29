<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('cards')
            ->where('category', 'Event')
            ->whereNull('cost')
            ->update(['cost' => 0]);
    }
};
