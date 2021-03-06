<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Ximdex\StructuredData\Core\Migration;

class CreateValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("{$this->baseName}values", function (Blueprint $table) {
            
            // Fields
            $table->bigIncrements('id');
            $table->unsignedBigInteger('available_type_id');
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('ref_item_id')->nullable();
            $table->text('value')->nullable();
            $table->unsignedSmallInteger('position')->default(1);
            $table->timestamps();
            
            // Indexes
            $table->unique(['available_type_id', 'item_id', 'ref_item_id'], 'unique_item_value');
            
            // Relations
            $table->foreign('available_type_id')
                ->references('id')->on("{$this->baseName}available_types")
                ->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('item_id')
                ->references('id')->on("{$this->baseName}items")
                ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('ref_item_id')
                ->references('id')->on("{$this->baseName}items")
                ->onDelete('restrict')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("{$this->baseName}values");
    }
}
