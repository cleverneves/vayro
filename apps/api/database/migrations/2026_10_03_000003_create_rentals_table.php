<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('renter_id')->constrained('clientes')->restrictOnDelete();
            $table->foreignId('vehicle_id')->constrained('carros')->restrictOnDelete();
            $table->date('starts_on');
            $table->smallInteger('day_count');
            $table->string('reason');
            $table->text('comment')->nullable();
            $table->string('status');
            $table->text('admin_note')->nullable();
            $table->date('requested_on');
            $table->timestamps();

            $table->index('requested_on');
            $table->index(['vehicle_id', 'starts_on', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};
