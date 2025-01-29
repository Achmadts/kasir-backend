<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pembelians', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('nama_supplier');
            $table->decimal("tax", 10, 2);
            $table->decimal("discount", 10, 2)->nullable();
            $table->integer("quantity");
            $table->enum("status", ['Pending', 'Completed']);
            $table->enum("payment_method", ['Cash', 'Bank Transfer']);
            $table->integer("total_pembayaran");
            $table->bigInteger("no_rekening_penerima")->nullable();
            $table->string("nama_rekening_penerima")->nullable();
            $table->string("bukti_transfer");
            $table->string("note")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembelians');
    }
};
