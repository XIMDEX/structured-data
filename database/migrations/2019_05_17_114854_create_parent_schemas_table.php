<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Ximdex\StructuredData\Core\Migration;

class CreateParentSchemasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("{$this->baseName}parent_schemas", function (Blueprint $table) {
            
            // Fields
            $table->unsignedBigInteger('schema_id');
            $table->unsignedBigInteger('parent_schema_id');
            $table->unsignedTinyInteger('priority')->default(1);
            $table->timestamps();
            
            // Indexes
            $table->primary(['schema_id', 'parent_schema_id']);
            
            // Relations
            $table->foreign('schema_id')
                ->references('id')->on("{$this->baseName}schemas")
                ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('parent_schema_id')
                ->references('id')->on("{$this->baseName}schemas")
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
        Schema::dropIfExists("{$this->baseName}parent_schemas");
    }
}
