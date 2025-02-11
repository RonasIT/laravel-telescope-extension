<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up()
    {
        if (DB::getDefaultConnection() === 'pgsql') {
            Schema::table('telescope_entries', function (Blueprint $table) {
                $table->jsonb('content_temp')->nullable();
            });

            DB::table('telescope_entries')
                ->orderBy('sequence')
                ->chunk(100, function ($telescopeEntries) {
                    foreach ($telescopeEntries as $telescopeEntry) {
                        $content = Str::remove(['\u0000*', '\u0000'], $telescopeEntry->content);

                        DB::table('telescope_entries')
                            ->where(['sequence' => $telescopeEntry->sequence])
                            ->update(['content_temp' => $content]);
                    }
                });

            Schema::table('telescope_entries', function (Blueprint $table) {
                $table->dropColumn('content');
                $table->renameColumn('content_temp', 'content');

                DB::statement('ALTER TABLE telescope_entries ALTER COLUMN content SET NOT NULL');
            });
        }
    }

    public function down()
    {
        if (DB::getDefaultConnection() === 'pgsql') {
            Schema::table('telescope_entries', function (Blueprint $table) {
                $table->longText('content_temp')->nullable();
            });

            DB::table('telescope_entries')
                ->orderBy('sequence')
                ->chunk(100, function ($telescopeEntries) {
                    foreach ($telescopeEntries as $telescopeEntry) {
                        DB::table('telescope_entries')
                            ->where(['sequence' => $telescopeEntry->sequence])
                            ->update(['content_temp' => $telescopeEntry->content]);
                    }
                });

            Schema::table('telescope_entries', function (Blueprint $table) {
                $table->dropColumn('content');
                $table->renameColumn('content_temp', 'content');

                DB::statement('ALTER TABLE telescope_entries ALTER COLUMN content SET NOT NULL');
            });
        }
    }
};