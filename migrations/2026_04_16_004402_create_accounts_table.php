<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('account', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->decimal('balance', 14, 2)->default(0);
            $table->string('email')->nullable();
            $table->datetimes();
        });

        \Hyperf\DbConnection\Db::table('account')->insert([
            'id' => '123e4567-e89b-12d3-a456-426614174000',
            'name' => 'Usuário Tecnofit (Seed)',
            'email' => 'suporte@tecnofit.com.br',
            'balance' => 1000.00,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account');
    }
};
