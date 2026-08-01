<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('tasks')->where('priority', 'normal')->update(['priority' => 'low']);
        DB::table('tasks')->where('priority', 'meduim')->update(['priority' => 'medium']);
        DB::table('tasks')->where('priority', 'hight')->update(['priority' => 'high']);
        DB::table('tasks')->where('status', 'inprogress')->update(['status' => 'in_progress']);
        DB::table('tasks')->where('status', 'cancelled')->update(['status' => 'todo']);

        Schema::table('tasks', function (Blueprint $table) {
            $table->string('priority')->default('low')->comment('low, medium, high')->change();
            $table->string('status')->default('todo')->comment('todo, in_progress, done')->change();
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->string('status')->default('active')->comment('active, completed, archived')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('priority')->default('normal')->comment('normal, medium, high')->change();
            $table->string('status')->default('todo')->comment('todo, done, cancelled, inprogress')->change();
        });
    }
};
