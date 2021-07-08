<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
        
            $table->id();
            $table->string('name', 255);
            $table->string('fname', 32)->nullable();
            $table->string('lname', 32)->nullable();
            $table->string('mname', 32)->nullable();
            $table->string('email', 255);
            $table->date('dob')->nullable();
            $table->string('patientid', 16)->nullable();
            $table->string('doctor_id', 16)->nullable();
            $table->string('reader_id', 16)->nullable();
            $table->tinyInteger('user_active')->default(0)->comment('user\'s activation status');
            $table->string('user_roles', 32)->default('[]')->comment('user\'s account type (basic, premium, etc)');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password', 255);
            $table->rememberToken();
            $table->string('current_team_id', 255)->nullable();
            $table->text('profile_photo_path')->nullable();
            $table->nullableTimestamps();
            $table->unique('name', 'users_name_unique');
            $table->unique('email', 'users_email_unique');
            $table->unique('patientid', 'patientid');
            
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}

//             $table->string('user_name', 16)->nullable()->comment('user\'s name, unique');
//             $table->string('user_email', 32)->nullable()->comment('user\'s email, unique');
//             $table->tinyInteger('user_active')->default(0)->comment('user\'s activation status');
//             $table->string('user_roles', 32)->default('[]')->comment('user\'s account type (basic, premium, etc)');
//             $table->string('session_id', 48)->nullable()->comment('stores session cookie id to prevent session concurrency');
//             $table->string('fname', 32)->nullable();
//             $table->string('lname', 32)->nullable();
//             $table->string('mname', 32)->nullable();
//             $table->date('dob')->nullable();
//             $table->string('linked_account_type_table', 32)->nullable()->comment('table with linked e-mail');
//             $table->string('patientid', 16)->nullable();
//             $table->string('doctor_id', 16)->nullable();
//             $table->string('reader_id', 16)->nullable();
