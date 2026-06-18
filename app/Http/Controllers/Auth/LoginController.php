<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!$this->databaseReachable()) {
            return back()->withErrors([
                'email' => 'Banco de dados indisponível. O servidor web não consegue conectar ao SQL Server (192.168.1.6:1433).',
            ])->onlyInput('email');
        }

        try {
            if (Auth::attempt($credentials, $request->boolean('remember'))) {
                $request->session()->regenerate();
                Auth::user()->update(['status' => 'online']);
                return redirect()->intended(route('dashboard'));
            }
        } catch (QueryException $e) {
            return back()->withErrors([
                'email' => 'Erro ao conectar ao banco de dados. Verifique a rede com o administrador.',
            ])->onlyInput('email');
        }

        return back()->withErrors([
            'email' => 'Credenciais inválidas.',
        ])->onlyInput('email');
    }

    private function databaseReachable(): bool
    {
        $host = config('database.connections.sqlsrv.host', '127.0.0.1');
        $port = (int) config('database.connections.sqlsrv.port', 1433);

        $socket = @fsockopen($host, $port, $errno, $errstr, 5);

        if ($socket) {
            fclose($socket);
            return true;
        }

        return false;
    }

    public function logout(Request $request)
    {
        Auth::user()?->update(['status' => 'offline']);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect(route('login'));
    }
}
