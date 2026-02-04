<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('providers', function (Blueprint $table) {
            if (!Schema::hasColumn('providers', 'distribution')) {
                $table->string('distribution')->nullable()->after('name');
            }
            if (!Schema::hasColumn('providers', 'rtp')) {
                $table->integer('rtp')->nullable()->after('distribution');
            }
            if (!Schema::hasColumn('providers', 'views')) {
                $table->integer('views')->default(0)->after('rtp');
            }
            if (!Schema::hasColumn('providers', 'status')) {
                $table->boolean('status')->default(true)->after('views');
            }
        });
    }

    public function down(): void
    {
        Schema::table('providers', function (Blueprint $table) {
            if (Schema::hasColumn('providers', 'distribution')) $table->dropColumn('distribution');
            if (Schema::hasColumn('providers', 'rtp')) $table->dropColumn('rtp');
            if (Schema::hasColumn('providers', 'views')) $table->dropColumn('views');
            if (Schema::hasColumn('providers', 'status')) $table->dropColumn('status');
        });
    }
};
