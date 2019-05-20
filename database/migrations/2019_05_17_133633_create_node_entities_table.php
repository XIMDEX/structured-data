<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Ximdex\StructuredData\Core\Migration;

class CreateNodeEntitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("{$this->baseName}node_entities", function (Blueprint $table) {
            
            // Fields
            $table->unsignedBigInteger('node_id');
            $table->unsignedBigInteger('entity_id');
            $table->timestamps();
            
            // Indexes
            $table->primary(['node_id', 'entity_id']);
            
            // Relations
            $table->foreign('node_id')
                ->references('id')->on("{$this->baseName}nodes" )
                ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('entity_id')
                ->references('id')->on("{$this->baseName}entities")
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
        Schema::dropIfExists("{$this->baseName}node_entities");
    }
}
