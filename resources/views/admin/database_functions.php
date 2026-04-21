<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

if (!function_exists('fetch_all')) {
    function fetch_all($sql, array $params = [])
    {
        $rows = DB::select($sql, $params);
        return array_map(function ($row) {
            return (array) $row;
        }, $rows);
    }
}

if (!function_exists('fetch_one')) {
    function fetch_one($sql, array $params = [])
    {
        $rows = fetch_all($sql, $params);
        return $rows[0] ?? null;
    }
}

if (!function_exists('execute_query')) {
    function execute_query($sql, array $params = [])
    {
        return DB::statement($sql, $params);
    }
}

if (!function_exists('table_exists')) {
    function table_exists(string $table): bool
    {
        return Schema::hasTable($table);
    }
}

if (!function_exists('column_exists')) {
    function column_exists(string $table, string $column): bool
    {
        return Schema::hasColumn($table, $column);
    }
}

if (!function_exists('admin_array_value')) {
    function admin_array_value(array $row, array $keys, $default = '')
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row) && $row[$key] !== null && $row[$key] !== '') {
                return $row[$key];
            }
        }

        return $default;
    }
}

if (!function_exists('admin_user_name')) {
    function admin_user_name(array $row): string
    {
        $first = trim((string) admin_array_value($row, ['first_name', 'First_name']));
        $last = trim((string) admin_array_value($row, ['last_name', 'Last_name']));
        $full = trim($last . ' ' . $first);

        if ($full !== '') {
            return $full;
        }

        $name = trim((string) admin_array_value($row, ['name', 'Name']));
        if ($name !== '') {
            return $name;
        }

        return (string) admin_array_value($row, ['email', 'Email'], 'Người dùng');
    }
}

if (!function_exists('admin_avatar_url')) {
    function admin_avatar_url(array $row): string
    {
        $avatar = trim((string) admin_array_value($row, ['avatar_url', 'AvatarURL', 'avatar_path', 'avatar', 'img']));
        if ($avatar === '') {
            return 'https://ui-avatars.com/api/?name=' . urlencode(admin_user_name($row)) . '&background=random';
        }

        if (preg_match('#^https?://#i', $avatar)) {
            return $avatar;
        }

        $avatar = ltrim(str_replace('\\\\', '/', $avatar), '/');
        if (preg_match('#^(storage/|uploads/|images/)#i', $avatar)) {
            return asset($avatar);
        }

        return asset('storage/' . $avatar);
    }
}

if (!function_exists('admin_post_media_url')) {
    function admin_post_media_url(array $row): ?string
    {
        $media = trim((string) admin_array_value($row, ['media_path', 'image_url', 'post_image']));
        if ($media === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $media)) {
            return $media;
        }

        $media = ltrim(str_replace('\\\\', '/', $media), '/');
        if (preg_match('#^(storage/|uploads/|images/)#i', $media)) {
            return asset($media);
        }

        return asset('storage/' . $media);
    }
}
