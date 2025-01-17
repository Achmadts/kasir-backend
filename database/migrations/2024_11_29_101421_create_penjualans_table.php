<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('penjualans', function (Blueprint $table) {
            $table->id();
            $table->date("tanggal_penjualan");
            $table->integer("total_harga");
            $table->integer("quantity");
            $table->decimal("pajak", 10, 2);
            $table->decimal("diskon", 10, 2)->nullable();
            $table->enum("status", ["Pending", "Completed", "Cancelled"]);
            $table->enum("metode_pembayaran", ["Cash", "Credit Card", "Bank Transfer"]);
            $table->string("catatan")->nullable();
            $table->foreignId("id_pelanggan")->references("id")->on("pelanggans")->onDelete("cascade")->onUpdate("cascade");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penjualans');
    }
};
