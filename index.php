<?php
session_start();

// Processamento da API (Backend)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $data = json_decode(file_get_contents('php://input'), true);

    // Ação: Salvar Chave na Sessão
    if (isset($data['action']) && $data['action'] === 'save_key') {
        $_SESSION['pollinations_api_key'] = trim($data['api_key']);
        echo json_encode(['success' => true]);
        exit;
    }

    $apiKey = $_SESSION['pollinations_api_key'] ?? '';
    if (empty($apiKey)) {
        echo json_encode(['success' => false, 'error' => 'Chave API ausente. Por favor, configure sua chave primeiro.']);
        exit;
    }

    // Rate Limiting Simples (Proteção do Servidor)
    $limite_segundos = 20;
    if (isset($_SESSION['last_req']) && time() - $_SESSION['last_req'] < $limite_segundos) {
        $faltam = $limite_segundos - (time() - $_SESSION['last_req']);
        echo json_encode(['success' => false, 'error' => "Aguarde {$faltam} segundos para gerar outra imagem."]);
        exit;
    }
    $_SESSION['last_req'] = time();

    // INTERVENÇÃO CIRÚRGICA: Liberar a sessão ANTES do cURL para evitar Session Locking
    session_write_close();

    $prompt = $data['prompt'] ?? '';
    $estilo = $data['estilo'] ?? 'photo';
    $proporcao = $data['proporcao'] ?? '1:1';

    if (empty($prompt)) {
        echo json_encode(['success' => false, 'error' => 'O prompt não pode estar vazio.']);
        exit;
    }

    // Mapeamento de Estilos
    $styleModifiers = [
        'cartoon' => 'cute cartoon style, colorful, flat shading',
        'illustration' => 'digital illustration, highly detailed, vibrant, aesthetic',
        'photo' => 'realistic photograph, 8k, highly detailed, photorealistic, cinematic lighting'
    ];
    $finalPrompt = $prompt . ', ' . $styleModifiers[$estilo];

    // Resoluções no padrão da API OpenAI / Pollinations
    $size = ($proporcao === '16:9') ? '1360x768' : '1024x1024';

    // Fallback de Modelos
    $models = ['flux', 'zimage', 'klein', 'gptimage'];
    $success = false;
    $imageUrl = '';
    $modelUsed = '';
    $fallbackAlert = '';

    foreach ($models as $index => $model) {
        $url = "https://gen.pollinations.ai/v1/images/generations";

        $payload = json_encode([
            'prompt' => $finalPrompt,
            'model' => $model,
            'size' => $size,
            'response_format' => 'b64_json',
            'n' => 1
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                "Authorization: Bearer {$apiKey}"
            ],
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Verifica sucesso na API compativel com OpenAI
        if ($httpCode === 200 && $response) {
            $responseData = json_decode($response, true);
            if (isset($responseData['data'][0]['b64_json'])) {
                $base64 = $responseData['data'][0]['b64_json'];
                $imageUrl = "data:image/jpeg;base64,{$base64}";
                $success = true;
                $modelUsed = $model;
                
                if ($index > 0) {
                    $fallbackAlert = "Modelo(s) anterior(es) indisponível(is). Fallback utilizado: " . strtoupper($model);
                }
                break; // Sai do loop imediatamente
            }
        }
    }

    if ($success) {
        echo json_encode([
            'success' => true,
            'url' => $imageUrl,
            'modelo' => $modelUsed,
            'fallback_usado' => !empty($fallbackAlert),
            'mensagem_aviso' => $fallbackAlert
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Todos os modelos da Pollinations falharam. Tente novamente mais tarde.']);
    }
    exit;
}

// Renderização da Interface (Frontend)
$hasKey = !empty($_SESSION['pollinations_api_key']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerador de Imagens IA</title>
    <link rel="icon" type="image/png" href="https://pollinations.ai/favicon.ico">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-900 text-white font-sans antialiased min-h-screen flex flex-col">

    <main class="flex-1 container mx-auto px-4 py-8 max-w-6xl">
        <div class="text-center mb-10">
            <h1 class="text-4xl font-bold mb-2">Gerador de Imagens IA</h1>
            <p class="text-gray-400">Crie imagens incríveis sem limites. Suas imagens não são salvas no nosso servidor.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <div class="lg:col-span-4 space-y-6">
                
                <div class="bg-gray-800 p-6 rounded-2xl border <?= $hasKey ? 'border-green-500' : 'border-gray-700' ?> shadow-xl">
                    <h3 class="text-lg font-bold mb-4 <?= $hasKey ? 'text-green-400' : 'text-gray-200' ?>">
                        <i class="fas fa-key mr-2"></i> Chave API Pollinations
                    </h3>
                    <div class="flex gap-2">
                        <input type="password" id="api-key" placeholder="Cole sua chave aqui..." class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-blue-500" value="<?= $hasKey ? '********' : '' ?>">
                        <button id="btn-save-key" class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-lg font-bold transition">Salvar</button>
                    </div>
                    <p class="text-xs text-gray-400 mt-2">A chave fica salva apenas na sua sessão atual.</p>
                </div>

                <div class="bg-gray-800 p-6 rounded-2xl border border-gray-700 shadow-xl">
                    <form id="image-form" class="space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-300 mb-2">Descrição da Imagem</label>
                            <textarea id="img-prompt" rows="4" placeholder="Ex: Um gato astronauta explorando marte..." class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500 resize-none" required></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-300 mb-2">Proporção</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="cursor-pointer relative">
                                    <input type="radio" name="img-proporcao" value="1:1" class="peer sr-only" checked>
                                    <div class="w-full bg-gray-700 border-2 border-gray-600 rounded-lg py-2 px-3 hover:bg-gray-600 transition-colors peer-checked:border-blue-500 peer-checked:bg-gray-700 flex flex-col items-center justify-center">
                                        <p class="font-bold text-gray-200 text-sm">1:1 (Quadrado)</p>
                                    </div>
                                </label>
                                <label class="cursor-pointer relative">
                                    <input type="radio" name="img-proporcao" value="16:9" class="peer sr-only">
                                    <div class="w-full bg-gray-700 border-2 border-gray-600 rounded-lg py-2 px-3 hover:bg-gray-600 transition-colors peer-checked:border-blue-500 peer-checked:bg-gray-700 flex flex-col items-center justify-center">
                                        <p class="font-bold text-gray-200 text-sm">16:9 (Widescreen)</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-300 mb-2">Estilo Visual</label>
                            <div class="grid grid-cols-1 gap-3">
                                <label class="cursor-pointer relative">
                                    <input type="radio" name="img-estilo" value="photo" class="peer sr-only" checked>
                                    <div class="w-full bg-gray-700 border-2 border-gray-600 rounded-lg p-3 hover:bg-gray-600 transition-colors peer-checked:border-blue-500 peer-checked:bg-gray-700">
                                        <p class="font-bold text-gray-200"><i class="fas fa-camera mr-2 text-blue-400"></i> Foto Realista</p>
                                    </div>
                                </label>
                                <label class="cursor-pointer relative">
                                    <input type="radio" name="img-estilo" value="illustration" class="peer sr-only">
                                    <div class="w-full bg-gray-700 border-2 border-gray-600 rounded-lg p-3 hover:bg-gray-600 transition-colors peer-checked:border-blue-500 peer-checked:bg-gray-700">
                                        <p class="font-bold text-gray-200"><i class="fas fa-paint-brush mr-2 text-purple-400"></i> Ilustração Digital</p>
                                    </div>
                                </label>
                                <label class="cursor-pointer relative">
                                    <input type="radio" name="img-estilo" value="cartoon" class="peer sr-only">
                                    <div class="w-full bg-gray-700 border-2 border-gray-600 rounded-lg p-3 hover:bg-gray-600 transition-colors peer-checked:border-blue-500 peer-checked:bg-gray-700">
                                        <p class="font-bold text-gray-200"><i class="fas fa-paw mr-2 text-pink-400"></i> Cartoon</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <button type="submit" id="btn-gerar" class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg transition-colors flex justify-center items-center text-lg shadow-lg" <?= !$hasKey ? 'disabled' : '' ?>>
                            <i class="fas fa-magic mr-2"></i> Gerar Imagem
                        </button>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-8 bg-gray-800 p-8 rounded-2xl border border-gray-700 min-h-[500px] flex flex-col relative shadow-xl">
                
                <div id="fallback-alert" class="hidden w-full bg-yellow-500/20 border border-yellow-500/50 rounded-lg p-4 mb-4 text-yellow-200 text-sm flex items-start">
                    <i class="fas fa-exclamation-triangle text-yellow-500 mt-0.5 mr-3"></i>
                    <span id="fallback-alert-text"></span>
                </div>

                <div id="state-empty" class="flex-1 flex flex-col items-center justify-center text-center opacity-80">
                    <i class="fas fa-image text-6xl text-gray-600 mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-300">Nenhuma imagem gerada</h3>
                    <p class="text-gray-400 mt-2">Insira sua chave, digite um prompt e clique em gerar.</p>
                </div>

                <div id="state-loading" class="absolute inset-0 bg-gray-800 z-10 flex flex-col items-center justify-center hidden rounded-2xl">
                    <i class="fas fa-spinner fa-spin text-5xl text-blue-500 mb-4"></i>
                    <h2 class="text-2xl font-bold text-white mb-2">Processando...</h2>
                    <p class="text-blue-300 text-lg">Comunicando com a IA via Pollinations.</p>
                </div>

                <div id="state-result" class="flex-1 flex flex-col items-center hidden w-full h-full">
                    <div class="flex-1 flex items-center justify-center bg-gray-900 rounded-xl w-full p-4 shadow-inner mb-6">
                        <img id="result-image" src="" alt="Imagem Gerada" class="max-w-full max-h-[600px] object-contain rounded">
                    </div>

                    <a id="btn-download" href="#" download="Gerador_IA.jpg" class="px-8 py-3 bg-green-600 hover:bg-green-700 text-white font-bold rounded-lg transition-colors flex items-center">
                        <i class="fas fa-download mr-2"></i> Baixar Imagem
                    </a>
                </div>
            </div>
        </div>
    </main>

    <footer class="text-center py-6 border-t border-gray-800 mt-auto">
        <p class="text-gray-500 text-sm">
            Powered by <a href="https://pollinations.ai" target="_blank" class="text-blue-400 hover:underline font-bold">Pollinations.ai</a>
        </p>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('image-form');
        const btnGerar = document.getElementById('btn-gerar');
        const btnSaveKey = document.getElementById('btn-save-key');
        const inputApiKey = document.getElementById('api-key');
        
        const stateEmpty = document.getElementById('state-empty');
        const stateLoading = document.getElementById('state-loading');
        const stateResult = document.getElementById('state-result');
        const resultImage = document.getElementById('result-image');
        const btnDownload = document.getElementById('btn-download');
        
        const fallbackAlert = document.getElementById('fallback-alert');
        const fallbackAlertText = document.getElementById('fallback-alert-text');

        // Salvar API Key
        btnSaveKey.addEventListener('click', async () => {
            const key = inputApiKey.value.trim();
            if(!key) return alert('Digite uma chave.');
            
            if(key === '********') return;

            try {
                const res = await fetch('', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'save_key', api_key: key })
                });
                const data = await res.json();
                if(data.success) {
                    alert('Chave salva na sessão com sucesso!');
                    btnGerar.disabled = false;
                    inputApiKey.parentElement.parentElement.classList.replace('border-gray-700', 'border-green-500');
                    inputApiKey.parentElement.parentElement.querySelector('h3').classList.replace('text-gray-200', 'text-green-400');
                    inputApiKey.value = '********';
                }
            } catch (e) {
                alert('Erro ao salvar chave.');
            }
        });

        // Gerar Imagem
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const prompt = document.getElementById('img-prompt').value;
            const estilo = document.querySelector('input[name="img-estilo"]:checked').value;
            const proporcao = document.querySelector('input[name="img-proporcao"]:checked').value;

            stateEmpty.classList.add('hidden');
            stateResult.classList.add('hidden');
            fallbackAlert.classList.add('hidden');
            stateLoading.classList.remove('hidden');
            btnGerar.disabled = true;

            try {
                const response = await fetch('', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ prompt, estilo, proporcao })
                });

                const data = await response.json();

                if (!data.success) throw new Error(data.error);

                if (data.fallback_usado) {
                    fallbackAlertText.innerText = data.mensagem_aviso;
                    fallbackAlert.classList.remove('hidden');
                }

                resultImage.src = data.url;
                
                const cleanName = prompt.substring(0, 20).replace(/[^a-zA-Z0-9]/g, '_');
                btnDownload.href = data.url;
                btnDownload.download = `IA_${estilo}_${cleanName}.jpg`;

                stateLoading.classList.add('hidden');
                stateResult.classList.remove('hidden');

            } catch (error) {
                alert('Erro: ' + error.message);
                stateLoading.classList.add('hidden');
                stateEmpty.classList.remove('hidden');
            } finally {
                btnGerar.disabled = false;
            }
        });
    });
    </script>
</body>
</html>
