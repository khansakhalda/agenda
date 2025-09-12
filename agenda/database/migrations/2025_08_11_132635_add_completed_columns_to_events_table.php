<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('events', function (Blueprint $t) {
            $t->boolean('is_completed')->default(false)->index();
            $t->timestamp('completed_at')->nullable()->index();
        });
    }
    public function down(): void {
        Schema::table('events', function (Blueprint $t) {
            $t->dropColumn(['is_completed','completed_at']);
        });
    }
};
