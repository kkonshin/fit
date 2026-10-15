<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            SELECT setval(
                pg_get_serial_sequence('moonshine_user_roles', 'id'),
                COALESCE((SELECT MAX(id) FROM moonshine_user_roles), 1),
                (SELECT COUNT(*) > 0 FROM moonshine_user_roles)
            )
        SQL);
    }

    public function down(): void
    {
        //
    }
};
