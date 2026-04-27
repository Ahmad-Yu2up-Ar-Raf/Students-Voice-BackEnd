<?php

use App\TaglineType;
use App\VisibilityEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                            // $table->string('authentication');
                            $table->json('media');
                            $table->longText('caption')->nullable();
                            $table->json('tag_category')->nullable();
                            $table->string('tag_location')->nullable();

                            $table->string('tagline' )->default(TaglineType::Bahagia->value) ;
                            $table->string('visibility' )->default(VisibilityEnum::Public->value) ;

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
