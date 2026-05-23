<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('feedback', function (Blueprint $table) {
            $table->boolean('reply_sent')->default(false)->after('followup');
            $table->timestamp('replied_at')->nullable()->after('reply_sent');
        });
    }

    public function down()
    {
        Schema::table('feedback', function (Blueprint $table) {
            $table->dropColumn(['reply_sent', 'replied_at']);
        });
    }
};
