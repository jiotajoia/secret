<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up() {
        Schema::table('groups', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('status')->default('n');
        });
    }

    public function down(){
        Schema::table('groups', function (Blueprint $table) {
            $table->dropForeign(['user_id'])
            ->dropColumn(['user_id', 'status']);
        });
    }
};
