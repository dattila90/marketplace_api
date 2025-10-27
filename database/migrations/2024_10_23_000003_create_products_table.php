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
    Schema::create('products', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->text('title');
      $table->string('brand');
      $table->uuid('category_id');
      $table->decimal('price', 10, 2);
      $table->string('currency', 3)->default('USD');
      $table->integer('stock')->default(0);
      $table->uuid('seller_id');
      $table->decimal('rating', 3, 2)->default(0.00);
      $table->integer('popularity')->default(0);
      $table->jsonb('attributes');
      $table->timestamps();

      $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
      $table->foreign('seller_id')->references('id')->on('sellers')->onDelete('cascade');

      $table->index(['category_id']);
      $table->index(['seller_id']);
      $table->index(['brand']);
      $table->index(['price']);
      $table->index(['rating']);
      $table->index(['popularity']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('products');
  }
};
