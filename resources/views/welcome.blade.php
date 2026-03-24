<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solução Guincho - Orçamento Rápido</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <div class="app-container">
        <header class="header">
            <img src="{{ asset('img/logo.png') }}" alt="Solução Guincho" class="site-logo">
        </header>

        <main class="form-wrapper">
            @if(session('success'))
            <div
                style="background: #e8f5e9; border: 1px solid #c8e6c9; color: #2e7d32; padding: 1.5rem; border-radius: 0.75rem; margin-bottom: 2rem; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem; color: #1b5e20;">Orçamento
                    Gerado!</h3>
                <p style="margin-bottom: 0.5rem; color: #2e7d32;">Distância Calculada: <strong>{{ session('distance') }}
                        km</strong></p>
                <p style="margin-bottom: 1.5rem; font-size: 1.1rem; color: #1b5e20;">Valor Estimado: <strong>R$ {{
                        number_format(session('price'), 2, ',', '.') }}</strong></p>
                <a href="{{ session('waLink') }}" target="_blank" class="contact-link whatsapp"
                    style="text-decoration: none; padding: 1rem; font-size: 1.1rem; justify-content: center; width: 100%; box-sizing: border-box; max-width: none;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path
                            d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z" />
                    </svg>
                    Enviar Confirmação para o Paulo
                </a>
            </div>
            @endif

            <form action="{{ route('orcamento.calcular') }}" method="POST" class="quotation-form" id="gui-form">
                @csrf
                <div class="form-header">
                    <h2>Solicite seu Orçamento</h2>
                    <p>Preencha os dados e receba o valor na hora.</p>
                </div>

                <div class="input-group">
                    <label for="pickup">Local de Retirada (Onde o carro está)</label>
                    <input type="text" id="pickup" name="pickup"
                        placeholder="Ex: Rua das Flores, 123 - Centro, Sorocaba" required>
                </div>

                <div class="input-group">
                    <label for="destination">Local de Destino (Para onde levá-lo)</label>
                    <input type="text" id="destination" name="destination"
                        placeholder="Ex: Oficina V8 - Campolim, Sorocaba" required>
                </div>

                <div class="input-group">
                    <label for="vehicle_type">Tipo de Veículo que será guinchado</label>
                    <select id="vehicle_type" name="vehicle_type" required>
                        <option value="" disabled selected>Selecione o veículo...</option>
                        <option value="carro">Carro de Passeio</option>
                        <option value="caminhonete">Caminhonete</option>
                        <option value="van">Van</option>
                        <option value="moto">Moto</option>
                    </select>
                </div>

                <div class="conditions-grid">
                    <label class="checkbox-card">
                        <input type="checkbox" name="rodas_livres" value="1" checked>
                        <span class="card-content">
                            <span class="card-title">Rodas Livres</span>
                            <span class="card-desc">As rodas não estão travadas.</span>
                        </span>
                    </label>

                    <label class="checkbox-card">
                        <input type="checkbox" name="cambio_destravado" value="1" checked>
                        <span class="card-content">
                            <span class="card-title">Câmbio Destravado</span>
                            <span class="card-desc">Veículo em neutro/ponto morto.</span>
                        </span>
                    </label>

                    <label class="checkbox-card" id="van-condition" style="display: none;">
                        <input type="checkbox" name="sem_carga" value="1" checked>
                        <span class="card-content">
                            <span class="card-title">Descarregada (Sem Carga)</span>
                            <span class="card-desc">Veículo sem mercadorias extras.</span>
                        </span>
                    </label>
                </div>

                <button type="submit" class="btn-submit">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 21l1.65-3.8a9 9 0 1 1 3.4 2.9L3 21" />
                        <path
                            d="M9 10a.5.5 0 0 0 1 0V9a.5.5 0 0 0-1 0v1a5 5 0 0 0 5 5h1a.5.5 0 0 0 0-1h-1a.5.5 0 0 0 0 1" />
                    </svg>
                    Calcular pelo WhatsApp
                </button>
            </form>
        </main>

        <footer class="contact-info">
            <p>Precisa de ajuda agora? Fale com a gente:</p>
            <div class="contact-buttons">
                <a href="https://wa.me/5515981655797" class="contact-link whatsapp" target="_blank">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 21l1.65-3.8a9 9 0 1 1 3.4 2.9L3 21" />
                        <path
                            d="M9 10a.5.5 0 0 0 1 0V9a.5.5 0 0 0-1 0v1a5 5 0 0 0 5 5h1a.5.5 0 0 0 0-1h-1a.5.5 0 0 0 0 1" />
                    </svg>
                    (15) 99776-1186
                </a>
                <a href="tel:+5515991641873" class="contact-link phone">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path
                            d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                    </svg>
                    (15) 99164-1873
                </a>
            </div>
        </footer>
    </div>

    <script>
        // Lógica simples para mostrar/esconder checkbox "Sem Carga" para Van e Caminhonete
        document.getElementById('vehicle_type').addEventListener('change', function (e) {
            const vanCondition = document.getElementById('van-condition');
            if (e.target.value === 'van' || e.target.value === 'caminhonete') {
                vanCondition.style.display = 'flex';
                // Trigger a micro-animation
                vanCondition.style.opacity = '0';
                setTimeout(() => {
                    vanCondition.style.transition = 'opacity 0.3s ease';
                    vanCondition.style.opacity = '1';
                }, 10);
            } else {
                vanCondition.style.display = 'none';
                vanCondition.querySelector('input').checked = true; // reset ao padrão
            }
        });

        // O form agora enviará os dados para o backend de forma nativa.
    </script>
</body>

</html>