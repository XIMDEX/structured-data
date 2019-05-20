<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Ximdex\StructuredData\Core\Migration;

class CreatePropertySchemasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("{$this->baseName}property_schemas", function (Blueprint $table) {
            
            // Fields
            $table->bigIncrements('id');
            $table->unsignedBigInteger('schema_id');
            $table->unsignedBigInteger('property_id');
            $table->unsignedSmallInteger('min_cardinality')->default(0);
            $table->unsignedSmallInteger('max_cardinality')->nullable()->default(null);
            $table->unsignedTinyInteger('order')->default(0);
            $table->text('default_value')->nullable();
            $table->timestamps();
            
            // Relations
            $table->foreign('schema_id')
                ->references('id')->on("{$this->baseName}schemas")
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
        Schema::dropIfExists("{$this->baseName}property_schemas");
    }
}
