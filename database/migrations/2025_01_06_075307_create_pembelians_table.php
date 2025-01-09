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
            $table->string("jumlah_barang");
            $table->enum("status", ['Success', 'Completed', 'Cancel']);
            $table->enum("payment_method", ['Cash', 'Credit Card', 'Bank Transfer']);
            $table->string("total_pembayaran");
            $table->string("note")->nullable();
            $table->foreignId("id_produk")->references("id")->on('produks')->onDelete("cascade")->onUpdate("cascade");
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
