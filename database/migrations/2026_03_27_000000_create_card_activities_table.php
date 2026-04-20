<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('card_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // moved, member_added, member_removed, label_added, label_removed, due_date_set, due_date_removed, description_changed, cover_changed, archived, restored, created, checklist_item_completed, attachment_added
            $table->json('data')->nullable(); // extra context e.g. from_list, to_list, member_name, label_name
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('card_activities');
    }
};
