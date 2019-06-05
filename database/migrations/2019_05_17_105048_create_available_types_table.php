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
            $table->unsignedBigInteger('property_schema_id');
            $table->unsignedBigInteger('schema_id')->nullable();
            $table->enum('type', AvailableType::SIMPLE_TYPES)->nullable();
            $table->timestamps();
            
            // Indexes
            $table->unique(['property_schema_id', 'schema_id', 'type']);
            
            // Relations
            $table->foreign('schema_id')
                ->references('id')->on("{$this->baseName}schemas")
                ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('property_schema_id')
                ->references('id')->on("{$this->baseName}property_schemas")
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
