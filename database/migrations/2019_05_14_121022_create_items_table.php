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
            $table->unsignedBigInteger('class_id');
            $table->timestamps();
            
            // Relations
            $table->foreign('class_id')
                ->references('id')->on("{$this->baseName}classes")
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
