<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Ximdex\StructuredData\Core\Migration;

class CreateItemNodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("{$this->baseName}item_nodes", function (Blueprint $table) {
            
            // Fields
            $table->unsignedBigInteger('node_id');
            $table->unsignedBigInteger('item_id');
            $table->timestamps();
            
            // Indexes
            $table->primary(['node_id', 'item_id']);
            
            // Relations
            $table->foreign('node_id')
                ->references('id')->on("{$this->baseName}nodes" )
                ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('item_id')
                ->references('id')->on("{$this->baseName}items")
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
        Schema::dropIfExists("{$this->baseName}item_nodes");
    }
}
