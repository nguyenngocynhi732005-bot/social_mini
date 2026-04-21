<?php

namespace App\Http\Controllers\SocialConnection;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class SearchController extends Controller
{
    private function existingNameColumns(): array
    {
        $candidates = ['name', 'Name', 'first_name', 'First_name', 'last_name', 'Last_name'];

        return array_values(array_filter($candidates, function ($column) {
            return Schema::hasColumn('users', $column);
        }));
    }

    private function applyNameSearch($query, string $keyword)
    {
        $nameColumns = $this->existingNameColumns();

        if (empty($nameColumns)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function ($subQuery) use ($nameColumns, $keyword) {
            foreach ($nameColumns as $index => $column) {
                if ($index === 0) {
                    $subQuery->where($column, 'LIKE', '%' . $keyword . '%');
                } else {
                    $subQuery->orWhere($column, 'LIKE', '%' . $keyword . '%');
                }
            }
        });
    }

    private function resolveDisplayName(User $user): string
    {
        $directNames = [
            $user->name ?? null,
            $user->Name ?? null,
        ];

        foreach ($directNames as $candidate) {
            if (!empty($candidate)) {
                return $candidate;
            }
        }

        $fullName = trim(implode(' ', array_filter([
            $user->first_name ?? null,
            $user->First_name ?? null,
            $user->last_name ?? null,
            $user->Last_name ?? null,
        ])));

        return $fullName !== '' ? $fullName : ('User #' . $user->id);
    }

    private function resolveAvatar(User $user): string
    {
        if (!empty($user->AvatarURL)) {
            return $user->AvatarURL;
        }

        if (!empty($user->avatar)) {
            return $user->avatar;
        }

        if (!empty($user->img)) {
            return $user->img;
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->resolveDisplayName($user)) . '&background=random';
    }

    public function index(Request $request)
    {
        $keyword = trim($request->query('q', ''));
        $users = collect();

        if ($keyword !== '') {
            $query = User::query();

            $this->applyNameSearch($query, $keyword);

            if (auth()->check()) {
                $query->whereKeyNot(auth()->id());
            }

            $users = $query->orderBy($query->getModel()->getKeyName(), 'desc')->limit(20)->get();
        }

        return view('pages.search', compact('keyword', 'users'));
    }

    public function autocomplete(Request $request)
    {
        $keyword = trim($request->query('q', ''));

        if ($keyword === '') {
            return response()->json([]);
        }

        $query = User::query();
        $this->applyNameSearch($query, $keyword);

        if (auth()->check()) {
            $query->whereKeyNot(auth()->id());
        }

        $users = $query->orderBy($query->getModel()->getKeyName(), 'desc')->limit(5)->get();

        $formatted = $users->map(function (User $user) {
            return [
                'id' => $user->id,
                'type' => 'user',
                'name' => $this->resolveDisplayName($user),
                'avatar_url' => $this->resolveAvatar($user),
            ];
        });

        return response()->json($formatted);
    }
}