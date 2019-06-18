<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Ximdex\StructuredData\Core\Migration;

class CreateVersionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("{$this->baseName}versions", function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('name', 255);
            $table->timestamps();
        });
        Schema::table("{$this->baseName}schemas", function (Blueprint $table) {
            $table->unsignedBigInteger('version_id')->nullable()->after('comment');
            $table->foreign('version_id')
                ->references('id')->on("{$this->baseName}versions")
                ->onDelete('restrict')->onUpdate('cascade');
        });
        Schema::table("{$this->baseName}properties", function (Blueprint $table) {
            $table->unsignedBigInteger('version_id')->nullable()->after('comment');
            $table->foreign('version_id')
                ->references('id')->on("{$this->baseName}versions")
                ->onDelete('restrict')->onUpdate('cascade');
        });
        Schema::table("{$this->baseName}property_schemas", function (Blueprint $table) {
            $table->unsignedBigInteger('version_id')->nullable()->after('default_value');
            $table->foreign('version_id')
                ->references('id')->on("{$this->baseName}versions")
                ->onDelete('restrict')->onUpdate('cascade');
        });
        Schema::table("{$this->baseName}available_types", function (Blueprint $table) {
            $table->unsignedBigInteger('version_id')->nullable()->after('type');
            $table->foreign('version_id')
                ->references('id')->on("{$this->baseName}versions")
                ->onDelete('restrict')->onUpdate('cascade');
        });
        Schema::table("{$this->baseName}hereditable_schemas", function (Blueprint $table) {
            $table->unsignedBigInteger('version_id')->nullable()->after('priority');
            $table->foreign('version_id')
                ->references('id')->on("{$this->baseName}versions")
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
        Schema::table("{$this->baseName}schemas", function (Blueprint $table) {
            $table->dropForeign("{$this->baseName}schemas_version_id_foreign");
            $table->dropColumn('version_id');
        });
        Schema::table("{$this->baseName}properties", function (Blueprint $table) {
            $table->dropForeign("{$this->baseName}properties_version_id_foreign");
            $table->dropColumn('version_id');
        });
        Schema::table("{$this->baseName}property_schemas", function (Blueprint $table) {
            $table->dropForeign("{$this->baseName}property_schemas_version_id_foreign");
            $table->dropColumn('version_id');
        });
        Schema::table("{$this->baseName}available_types", function (Blueprint $table) {
            $table->dropForeign("{$this->baseName}available_types_version_id_foreign");
            $table->dropColumn('version_id');
        });
        Schema::table("{$this->baseName}hereditable_schemas", function (Blueprint $table) {
            $table->dropForeign("{$this->baseName}hereditable_schemas_version_id_foreign");
            $table->dropColumn('version_id');
        });
        Schema::dropIfExists("{$this->baseName}versions");
    }
}
