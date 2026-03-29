<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->string('card_set')->nullable()->after('pack_id');
            $table->integer('alt_art_variant')->nullable()->after('img_url');
        });

        DB::statement("UPDATE cards SET card_set = substr(id, 1, instr(id, '-') - 1)");
        DB::statement("UPDATE cards SET alt_art_variant = CAST(substr(id, instr(id, '_p') + 2) AS INTEGER) WHERE INSTR(id, '_p') > 0");
    }

    public function down(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->dropColumn(['card_set', 'alt_art_variant']);
        });
    }
};
