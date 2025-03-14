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
        Schema::create('detail_pembelians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pembelian')->references('id')->on('pembelians')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('id_produk')->references('id')->on('produks')->onDelete('cascade')->onUpdate('cascade');
            $table->integer('jumlah_produk');
            $table->integer("sub_total");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_pembelians');
    }
};
