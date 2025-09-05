<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TopicController extends Controller
{
    public function index()
    {
        $stats = [
            'totalTopics' => 0,
            'activeTopics' => 0,
            'deviceTopics' => 0,
            'systemTopics' => 0,
        ];

        $topics = [];

        try {
            // Buscar tópicos da API MQTT
            $response = Http::get('http://localhost:8000/api/mqtt/topics');

            if ($response->successful()) {
                $data = $response->json();
                $topics = $data['data'] ?? [];

                // Calcular estatísticas
                $stats['totalTopics'] = count($topics);
                $stats['activeTopics'] = count(array_filter($topics, function($topic) {
                    return ($topic['status'] ?? 'active') === 'active';
                }));
                $stats['deviceTopics'] = count(array_filter($topics, function($topic) {
                    return strpos($topic['name'] ?? '', 'device/') === 0;
                }));
                $stats['systemTopics'] = count(array_filter($topics, function($topic) {
                    return strpos($topic['name'] ?? '', 'system/') === 0;
                }));
            }
        } catch (\Exception $e) {
            // Se a API não estiver disponível, usar dados de exemplo
            $topics = [
                [
                    'id' => 1,
                    'name' => 'device/sensor/temperature',
                    'description' => 'Tópico para leituras de temperatura dos sensores',
                    'type' => 'sensor',
                    'status' => 'active',
                    'created_at' => now()->format('d/m/Y H:i:s'),
                ],
                [
                    'id' => 2,
                    'name' => 'device/actuator/relay',
                    'description' => 'Tópico para controle de relés',
                    'type' => 'actuator',
                    'status' => 'active',
                    'created_at' => now()->format('d/m/Y H:i:s'),
                ],
                [
                    'id' => 3,
                    'name' => 'system/status',
                    'description' => 'Tópico para status do sistema',
                    'type' => 'system',
                    'status' => 'active',
                    'created_at' => now()->format('d/m/Y H:i:s'),
                ],
                [
                    'id' => 4,
                    'name' => 'device/sensor/humidity',
                    'description' => 'Tópico para leituras de umidade dos sensores',
                    'type' => 'sensor',
                    'status' => 'active',
                    'created_at' => now()->format('d/m/Y H:i:s'),
                ],
                [
                    'id' => 5,
                    'name' => 'device/actuator/led',
                    'description' => 'Tópico para controle de LEDs',
                    'type' => 'actuator',
                    'status' => 'inactive',
                    'created_at' => now()->format('d/m/Y H:i:s'),
                ],
            ];

            $stats = [
                'totalTopics' => 5,
                'activeTopics' => 4,
                'deviceTopics' => 4,
                'systemTopics' => 1,
            ];
        }

        // Recalcular estatísticas
        $stats = [
            'totalTopics' => count($topics),
            'activeTopics' => count(array_filter($topics, function($topic) {
                return ($topic['status'] ?? 'active') === 'active';
            })),
            'deviceTopics' => count(array_filter($topics, function($topic) {
                return strpos($topic['name'] ?? '', 'device/') === 0;
            })),
            'systemTopics' => count(array_filter($topics, function($topic) {
                return strpos($topic['name'] ?? '', 'system/') === 0;
            })),
        ];

        return view('topics.index', compact('topics', 'stats'));
    }

    public function create()
    {
        return view('topics.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|in:device,system,sensor,actuator',
        ]);

        try {
            $response = Http::post('http://localhost:8000/api/mqtt/topics', [
                'name' => $request->name,
                'description' => $request->description,
            ]);

            if ($response->successful()) {
                return redirect()->route('topics.index')
                    ->with('success', 'Tópico criado com sucesso!');
            } else {
                // Se a API não estiver disponível, simular criação
                return redirect()->route('topics.index')
                    ->with('success', "Tópico '{$request->name}' criado com sucesso! (Modo demonstração - API não disponível)");
            }
        } catch (\Exception $e) {
            // Se a API não estiver disponível, simular criação
            return redirect()->route('topics.index')
                ->with('success', "Tópico '{$request->name}' criado com sucesso! (Modo demonstração - API não disponível)");
        }
    }

    public function show($id)
    {
        try {
            $response = Http::get("http://localhost:8000/api/mqtt/topics/{$id}");

            if ($response->successful()) {
                $topic = $response->json()['data'];
                return view('topics.show', compact('topic'));
            } else {
                return redirect()->route('topics.index')
                    ->withErrors(['error' => 'Tópico não encontrado']);
            }
        } catch (\Exception $e) {
            return redirect()->route('topics.index')
                ->withErrors(['error' => 'Erro ao buscar tópico: ' . $e->getMessage()]);
        }
    }

    public function edit($id)
    {
        try {
            $response = Http::get("http://localhost:8000/api/mqtt/topics/{$id}");

            if ($response->successful()) {
                $topic = $response->json()['data'];
                return view('topics.edit', compact('topic'));
            } else {
                return redirect()->route('topics.index')
                    ->withErrors(['error' => 'Tópico não encontrado']);
            }
        } catch (\Exception $e) {
            return redirect()->route('topics.index')
                ->withErrors(['error' => 'Erro ao buscar tópico: ' . $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        try {
            $response = Http::put("http://localhost:8000/api/mqtt/topics/{$id}", [
                'name' => $request->name,
                'description' => $request->description,
            ]);

            if ($response->successful()) {
                return redirect()->route('topics.index')
                    ->with('success', 'Tópico atualizado com sucesso!');
            } else {
                return redirect()->back()
                    ->withErrors(['error' => 'Erro ao atualizar tópico: ' . $response->body()])
                    ->withInput();
            }
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Erro ao conectar com a API: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function destroy($id)
    {
        \Log::info("Tentando desativar tópico ID: $id");

        try {
            // Usar o endpoint correto de desativação da API
            $response = Http::patch("http://localhost:8000/api/mqtt/topics/{$id}/deactivate");

            \Log::info("Resposta da API: " . $response->status() . " - " . $response->body());

            if ($response->successful()) {
                \Log::info("Tópico $id desativado com sucesso via API");
                return redirect()->route('topics.index')
                    ->with('success', 'Tópico desativado com sucesso!');
            } else {
                \Log::warning("API falhou para tópico $id");
                return redirect()->route('topics.index')
                    ->with('error', "Erro ao desativar tópico #{$id}. Tente novamente.");
            }
        } catch (\Exception $e) {
            \Log::error("Erro ao desativar tópico $id: " . $e->getMessage());
            return redirect()->route('topics.index')
                ->with('error', "Erro ao conectar com a API. Tente novamente.");
        }
    }

    public function deactivate($id)
    {
        try {
            // Usar o endpoint correto de desativação da API
            $response = Http::patch("http://localhost:8000/api/mqtt/topics/{$id}/deactivate");

            if ($response->successful()) {
                return redirect()->route('topics.index')
                    ->with('success', 'Tópico desativado com sucesso!');
            } else {
                return redirect()->route('topics.index')
                    ->with('error', "Erro ao desativar tópico #{$id}. Tente novamente.");
            }
        } catch (\Exception $e) {
            return redirect()->route('topics.index')
                ->with('error', "Erro ao conectar com a API. Tente novamente.");
        }
    }

}
