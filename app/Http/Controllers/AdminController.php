<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class AdminController extends Controller
{
    /**
     * Keep legacy endpoint but route into admin login flow.
     */
    public function index()
    {
        return redirect('/admin/login_admin.php');
    }

    public function adminHome(Request $request)
    {
        return (int) $request->session()->get('is_admin', 0) === 1
            ? redirect('/admin/admin_dashboard.php')
            : redirect('/admin/login_admin.php');
    }

    public function renderPage(Request $request, string $file)
    {
        $safeFile = basename($file);
        $path = resource_path('views/admin/' . $safeFile);

        if (!is_file($path)) {
            abort(404);
        }

        ob_start();
        include $path;
        $content = ob_get_clean();

        return response($content);
    }
}
