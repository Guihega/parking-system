<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Services\AuthService;
use Illuminate\Support\Facades\Log;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request)
    {
        try {

            $data = AuthService::login(
                $request->email,
                $request->password
            );

            Log::info('User logged in', [
                'user_id' => $data['raw_user']->id,
                'roles' => $data['user']['roles'],
            ]);

            auth()->loginUsingId($data['raw_user']->id);

            $request->session()->regenerate();

            session([
                'user_payload' => $data['user'],
                'permissions' => $data['user']['permissions'],
                'roles' => $data['user']['roles'],
            ]);

            return redirect()
                ->intended(route('parking.select.space'))
                ->withCookie(cookie(
                    'jwt_token',
                    $data['token'],
                    480,
                    '/',
                    null,
                    false,
                    true,
                    false,
                    'lax'
            ));

        } catch (\Exception $e) {
            $code = $e->getMessage();
            //dd($code); // 👈 DEBUG REAL
            Log::warning('Login failed', [
                'email' => $request->email,
                'error' => $code,
            ]);

            $message = match (true) {
                str_contains($code, 'USER_NOT_FOUND') =>
                    'No existe una cuenta con ese correo',
                str_contains($code, 'USER_DISABLED') =>
                    'El usuario está desactivado',
                str_contains($code, 'INVALID_CREDENTIALS') =>
                    'La contraseña es incorrecta',
                default =>
                    'No se pudo iniciar sesión, intenta nuevamente'
            };

            return back()->withErrors([
                'email' => $message
            ]);
        }
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $response = redirect('/');

        $response->headers->setCookie(
            new \Symfony\Component\HttpFoundation\Cookie(
                'jwt_token',
                '',
                now()->subMinute(),
                '/'
            )
        );

        return $response;
    }

}
