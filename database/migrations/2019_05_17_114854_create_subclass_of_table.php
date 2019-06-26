<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Ximdex\StructuredData\Core\Migration;

class CreateSubclassOfTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("{$this->baseName}subclass_of", function (Blueprint $table) {
            
            // Fields
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('parent_class_id');
            $table->unsignedTinyInteger('priority')->default(1);
            $table->timestamps();
            
            // Indexes
            $table->primary(['class_id', 'parent_class_id']);
            
            $table->foreign('class_id')
                ->references('id')->on("{$this->baseName}classes")
                ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('parent_class_id')
                ->references('id')->on("{$this->baseName}classes")
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
        Schema::dropIfExists("{$this->baseName}subclass_of");
    }
}
