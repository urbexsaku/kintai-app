<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorrectionBreakRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('correction_break_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('correction_request_id')->constrained()->cascadeOnDelete();
            $table->time('requested_start_at');
            $table->time('requested_end_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('correction_break_requests');
    }
}
