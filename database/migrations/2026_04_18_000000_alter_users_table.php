<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Xóa các cột cũ nếu tồn tại
            if (Schema::hasColumn('users', 'name')) {
                $table->dropColumn('name');
            }
            if (Schema::hasColumn('users', 'email')) {
                $table->dropColumn('email');
            }
            if (Schema::hasColumn('users', 'email_verified_at')) {
                $table->dropColumn('email_verified_at');
            }
            if (Schema::hasColumn('users', 'password')) {
                $table->dropColumn('password');
            }
            if (Schema::hasColumn('users', 'remember_token')) {
                $table->dropColumn('remember_token');
            }
            if (Schema::hasColumn('users', 'created_at')) {
                $table->dropColumn('created_at');
            }
            if (Schema::hasColumn('users', 'updated_at')) {
                $table->dropColumn('updated_at');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            // Thêm các cột mới nếu chưa tồn tại
            if (!Schema::hasColumn('users', 'unique_id')) {
                $table->string('unique_id')->unique()->after('ID');
            }
            if (!Schema::hasColumn('users', 'user_id')) {
                $table->string('user_id')->nullable()->after('unique_id');
            }
            if (!Schema::hasColumn('users', 'Email')) {
                $table->string('Email')->nullable()->unique()->after('user_id');
            }
            if (!Schema::hasColumn('users', 'Phone')) {
                $table->string('Phone')->nullable()->after('Email');
            }
            if (!Schema::hasColumn('users', 'Password')) {
                $table->string('Password')->after('Phone');
            }
            if (!Schema::hasColumn('users', 'Name')) {
                $table->string('Name')->after('Password');
            }
            if (!Schema::hasColumn('users', 'img')) {
                $table->string('img')->nullable()->after('Name');
            }
            if (!Schema::hasColumn('users', 'Gender')) {
                $table->string('Gender')->nullable()->after('img');
            }
            if (!Schema::hasColumn('users', 'BirthDate')) {
                $table->date('BirthDate')->nullable()->after('Gender');
            }
            if (!Schema::hasColumn('users', 'online_status')) {
                $table->boolean('online_status')->default(false)->after('BirthDate');
            }
            if (!Schema::hasColumn('users', 'Status')) {
                $table->string('Status')->default('active')->after('online_status');
            }
            if (!Schema::hasColumn('users', 'CreatedAt')) {
                $table->timestamp('CreatedAt')->useCurrent()->after('Status');
            }
            if (!Schema::hasColumn('users', 'UpdatedAt')) {
                $table->timestamp('UpdatedAt')->useCurrent()->useCurrentOnUpdate()->after('CreatedAt');
            }
            if (!Schema::hasColumn('users', 'is_admin')) {
                $table->boolean('is_admin')->default(false)->after('UpdatedAt');
            }
            if (!Schema::hasColumn('users', 'Reputation')) {
                $table->integer('Reputation')->default(0)->after('is_admin');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'unique_id')) $table->dropColumn('unique_id');
            if (Schema::hasColumn('users', 'user_id')) $table->dropColumn('user_id');
            if (Schema::hasColumn('users', 'Email')) $table->dropColumn('Email');
            if (Schema::hasColumn('users', 'Phone')) $table->dropColumn('Phone');
            if (Schema::hasColumn('users', 'Password')) $table->dropColumn('Password');
            if (Schema::hasColumn('users', 'Name')) $table->dropColumn('Name');
            if (Schema::hasColumn('users', 'img')) $table->dropColumn('img');
            if (Schema::hasColumn('users', 'Gender')) $table->dropColumn('Gender');
            if (Schema::hasColumn('users', 'BirthDate')) $table->dropColumn('BirthDate');
            if (Schema::hasColumn('users', 'online_status')) $table->dropColumn('online_status');
            if (Schema::hasColumn('users', 'Status')) $table->dropColumn('Status');
            if (Schema::hasColumn('users', 'CreatedAt')) $table->dropColumn('CreatedAt');
            if (Schema::hasColumn('users', 'UpdatedAt')) $table->dropColumn('UpdatedAt');
            if (Schema::hasColumn('users', 'is_admin')) $table->dropColumn('is_admin');
            if (Schema::hasColumn('users', 'Reputation')) $table->dropColumn('Reputation');
        });
    }
}
