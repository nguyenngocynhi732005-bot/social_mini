<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RecoverMissingUserEmails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('users') || !Schema::hasColumn('users', 'Email')) {
            return;
        }

        $rows = DB::table('users')
            ->select('ID', 'unique_id', 'user_id', 'Email');

        if (Schema::hasColumn('users', 'email')) {
            $rows->addSelect('email');
        }

        $rows = $rows
            ->where(function ($query) {
                $query->whereNull('Email')
                    ->orWhere('Email', '');
            })
            ->orderBy('ID')
            ->get();

        foreach ($rows as $row) {
            $restoredEmail = '';

            if (isset($row->email)) {
                $restoredEmail = trim((string) $row->email);
            }

            if ($restoredEmail === '') {
                $identifier = trim((string) ($row->unique_id ?? ''));

                if ($identifier === '') {
                    $identifier = trim((string) ($row->ID ?? $row->user_id ?? ''));
                }

                $identifier = preg_replace('/[^A-Za-z0-9._-]/', '-', $identifier) ?: 'user-' . (string) ($row->ID ?? 'unknown');
                $restoredEmail = 'missing-' . $identifier . '@socialmini.local';
            }

            $updates = [
                'Email' => $restoredEmail,
            ];

            if (Schema::hasColumn('users', 'UpdatedAt')) {
                $updates['UpdatedAt'] = now();
            }

            DB::table('users')
                ->where('ID', $row->ID)
                ->update($updates);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // This migration only restores missing values; it does not remove data on rollback.
    }
}
