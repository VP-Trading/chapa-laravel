<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('chapa_webhook_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('event_type');
            $table->string('tx_ref')->nullable();
            $table->string('chapa_ref')->nullable();
            $table->string('status');
            $table->string('type');
            $table->decimal('amount', 10, 2);
            $table->decimal('charge', 10, 2);
            $table->string('currency', 10)->default('ETB');
            $table->json('data')->nullable();
            $table->unique(['event_type', 'tx_ref']);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('chapa_webhook_events');
    }
};
