<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Ximdex\StructuredData\Core\Migration;
use Ximdex\StructuredData\Models\AvailableType;

class CreateAvailableTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("{$this->baseName}available_types", function (Blueprint $table) {
            
            // Fields
            $table->bigIncrements('id');
            $table->unsignedBigInteger('property_class_id');
            $table->unsignedBigInteger('class_id')->nullable();
            $table->enum('type', AvailableType::SIMPLE_TYPES)->nullable();
            $table->timestamps();
            
            // Indexes
            $table->unique(['property_class_id', 'class_id', 'type']);
            
            // Relations
            $table->foreign('class_id')
                ->references('id')->on("{$this->baseName}classes")
                ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('property_class_id')
                ->references('id')->on("{$this->baseName}property_classes")
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
        Schema::dropIfExists("{$this->baseName}available_types");
    }
}
