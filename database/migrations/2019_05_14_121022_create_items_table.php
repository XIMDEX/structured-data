<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Ximdex\StructuredData\Core\Migration;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("{$this->baseName}items", function (Blueprint $table) {
            
            // Fields
            $table->bigIncrements('id');
            $table->unsignedBigInteger('schema_id');
            $table->timestamps();
            
            // Relations
            $table->foreign('schema_id')
                ->references('id')->on("{$this->baseName}schemas")
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
        Schema::dropIfExists("{$this->baseName}items");
    }
}
