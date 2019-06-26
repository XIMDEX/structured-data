<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Ximdex\StructuredData\Core\Migration;

class CreatePropertyClassesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("{$this->baseName}property_classes", function (Blueprint $table) {
            
            // Fields
            $table->bigIncrements('id');
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('property_id');
            $table->unsignedSmallInteger('min_cardinality')->default(0);
            $table->unsignedSmallInteger('max_cardinality')->nullable()->default(null);
            $table->unsignedTinyInteger('order')->default(1);
            $table->text('default_value')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->unique(['class_id', 'property_id']);
            
            // Relations
            $table->foreign('class_id')
                ->references('id')->on("{$this->baseName}classes")
                ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('property_id')
                ->references('id')->on("{$this->baseName}properties")
                ->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("{$this->baseName}property_classes");
    }
}
