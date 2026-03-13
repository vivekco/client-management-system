<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('import_summaries', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->integer('total_rows')->default(0);
            $table->integer('inserted')->default(0);
            $table->integer('duplicates')->default(0);
            $table->integer('skipped')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('import_summaries');
    }
};
