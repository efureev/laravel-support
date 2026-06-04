<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'users',
            static function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->string('email');
                $table->string('password');
                $table->string('remember_token');
                $table->timestamps();
            }
        );

        Schema::create(
            'test_table',
            static function (Blueprint $table) {
                $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));

                $table->string('title')->nullable();
                $table->boolean('enabled')->default(false);
                $table->jsonb('config')->nullable();
                $table->string('str')->nullable();
                $table->string('str_empty')->nullable();
                $table->integer('int')->default(0);
                $table->integer('user_id')->nullable();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('test_table');
        Schema::dropIfExists('users');
    }
};
