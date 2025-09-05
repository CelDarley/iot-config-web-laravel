<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        // Tentar autenticar via API MQTT
        try {
            $response = Http::post('http://localhost:8000/api/auth/login', [
                'email' => $request->email,
                'password' => $request->password,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['success']) {
                    $userData = $data['data']['user'];
                    
                    // Criar usuário local se não existir
                    $user = \App\Models\User::updateOrCreate(
                        ['email' => $userData['email']],
                        [
                            'name' => $userData['name'],
                            'email' => $userData['email'],
                            'tipo' => $userData['tipo'],
                            'id_comp' => $userData['id_comp'] ?? null,
                        ]
                    );

                    // Fazer login local
                    Auth::login($user);
                    
                    // Armazenar token da API
                    session(['api_token' => $data['data']['token']]);
                    
                    return redirect()->intended(route('dashboard'));
                }
            }
        } catch (\Exception $e) {
            // Se a API não estiver disponível, tentar autenticação local
            if (Auth::attempt($request->only('email', 'password'))) {
                $request->session()->regenerate();
                return redirect()->intended(route('dashboard'));
            }
        }

        throw ValidationException::withMessages([
            'email' => 'As credenciais fornecidas não correspondem aos nossos registros.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login');
    }
}

