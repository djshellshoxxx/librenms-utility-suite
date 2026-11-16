<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('lus_settings', function (Blueprint $t) {$t->id();$t->string('key')->unique();$t->text('value')->nullable();$t->timestamps();});
        Schema::create('lus_bookmarks', function (Blueprint $t) {$t->id();$t->unsignedBigInteger('user_id');$t->unsignedInteger('device_id');$t->timestamps();$t->unique(['user_id','device_id']);});
        Schema::create('lus_tags', function (Blueprint $t) {$t->id();$t->string('name');$t->string('normalized')->unique();$t->timestamps();});
        Schema::create('lus_device_tags', function (Blueprint $t) {$t->unsignedInteger('device_id');$t->unsignedBigInteger('tag_id');$t->unique(['device_id','tag_id']);});
        Schema::create('lus_device_note_history', function (Blueprint $t) {$t->id();$t->unsignedInteger('device_id');$t->unsignedBigInteger('user_id');$t->text('previous_notes')->nullable();$t->text('new_notes')->nullable();$t->string('source',32)->default('ui');$t->timestamps();});
        Schema::create('lus_port_note_history', function (Blueprint $t) {$t->id();$t->unsignedInteger('port_id');$t->unsignedInteger('device_id');$t->unsignedBigInteger('user_id');$t->text('previous_notes')->nullable();$t->text('new_notes')->nullable();$t->timestamps();});
    }

    public function down(): void {
        foreach (['lus_port_note_history','lus_device_note_history','lus_device_tags','lus_tags','lus_bookmarks','lus_settings'] as $table) Schema::dropIfExists($table);
    }
};
