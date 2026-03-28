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
                <a href="{{ route('orcamento.confirmar') }}" target="_blank" class="contact-link whatsapp"
                    style="text-decoration: none; padding: 1rem; font-size: 1.1rem; justify-content: center; width: 100%; box-sizing: border-box; max-width: none;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 21l1.65-3.8a9 9 0 1 1 3.4 2.9L3 21" />
                        <path
                            d="M9 10a.5.5 0 0 0 1 0V9a.5.5 0 0 0-1 0v1a5 5 0 0 0 5 5h1a.5.5 0 0 0 0-1h-1a.5.5 0 0 0 0 1" />
                    </svg>
                    Enviar Confirmação
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
                    <div style="display: flex; justify-content: space-between; align-items: baseline;">
                        <label for="pickup">Local de Retirada (Onde o carro está)</label>
                        <button type="button" id="btn-gps" style="background: none; border: none; padding: 0; color: #3b82f6; cursor: pointer; font-size: 0.85rem; font-weight: 600; display: flex; align-items: center; gap: 0.25rem;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="12" y1="2" x2="12" y2="4"/>
                                <line x1="12" y1="20" x2="12" y2="22"/>
                                <line x1="2" y1="12" x2="4" y2="12"/>
                                <line x1="20" y1="12" x2="22" y2="12"/>
                            </svg>
                            Usar GPS
                        </button>
                    </div>
                    <input type="text" id="pickup" name="pickup"
                        placeholder="Ex: Rua das Flores, 123 - Centro, Sorocaba" value="{{ old('pickup') }}" required>
                </div>

                <div class="input-group">
                    <label for="destination">Local de Destino (Para onde levá-lo)</label>
                    <input type="text" id="destination" name="destination"
                        placeholder="Ex: Oficina V8 - Campolim, Sorocaba" value="{{ old('destination') }}" required>
                </div>

                <div class="input-group">
                    <label for="vehicle_type">Tipo de Veículo que será guinchado</label>
                    <select id="vehicle_type" name="vehicle_type" required>
                        <option value="" disabled {{ old('vehicle_type') ? '' : 'selected' }}>Selecione o veículo...</option>
                        <option value="carro" {{ old('vehicle_type') == 'carro' ? 'selected' : '' }}>Carro de Passeio</option>
                        <option value="caminhonete" {{ old('vehicle_type') == 'caminhonete' ? 'selected' : '' }}>Caminhonete</option>
                        <option value="van" {{ old('vehicle_type') == 'van' ? 'selected' : '' }}>Van</option>
                        <option value="moto" {{ old('vehicle_type') == 'moto' ? 'selected' : '' }}>Motocicleta</option>
                    </select>
                </div>

                <div class="conditions-grid">
                    <label class="checkbox-card">
                        <input type="checkbox" name="rodas_livres" value="1" {{ empty(old()) ? 'checked' : (old('rodas_livres') ? 'checked' : '') }}>
                        <span class="card-content">
                            <span class="card-title">Rodas Livres</span>
                            <span class="card-desc">As rodas não estão travadas.</span>
                        </span>
                    </label>

                    <label class="checkbox-card">
                        <input type="checkbox" name="cambio_destravado" value="1" {{ empty(old()) ? 'checked' : (old('cambio_destravado') ? 'checked' : '') }}>
                        <span class="card-content">
                            <span class="card-title">Câmbio Destravado</span>
                            <span class="card-desc">Veículo em neutro/ponto morto.</span>
                        </span>
                    </label>

                    <label class="checkbox-card" id="van-condition" style="display: {{ in_array(old('vehicle_type'), ['van', 'caminhonete']) ? 'flex' : 'none' }};">
                        <input type="checkbox" name="sem_carga" value="1" {{ empty(old()) ? 'checked' : (old('sem_carga') ? 'checked' : '') }}>
                        <span class="card-content">
                            <span class="card-title">Descarregada (Sem Carga)</span>
                            <span class="card-desc">Veículo sem mercadorias extras.</span>
                        </span>
                    </label>
                </div>

                <button type="submit" class="btn-submit">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" viewBox="0 0 512 512"><!--!Font Awesome Free v7.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M256 480c141.4 0 256-107.5 256-240S397.4 0 256 0 0 107.5 0 240c0 54.3 19.2 104.3 51.6 144.5L2.8 476.8c-4.8 9-3.3 20 3.6 27.5s17.8 9.8 27.1 5.8l118.4-50.7C183.7 472.6 218.9 480 256 480zm4-352c11 0 20 9 20 20l0 4 8 0c11 0 20 9 20 20s-9 20-20 20l-47.5 0c-6.9 0-12.5 5.6-12.5 12.5 0 6.1 4.4 11.3 10.4 12.3l41.7 7c25.3 4.2 43.9 26.1 43.9 51.8 0 26.1-19 47.7-44 51.8l0 4.7c0 11-9 20-20 20s-20-9-20-20l0-4-24 0c-11 0-20-9-20-20s9-20 20-20l55.5 0c6.9 0 12.5-5.6 12.5-12.5 0-6.1-4.4-11.3-10.4-12.3l-41.7-7c-25.3-4.2-43.9-26.1-43.9-51.8 0-28.8 23.2-52.2 52-52.5l0-4c0-11 9-20 20-20z"/></svg>
                    Consultar Valor
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
        // Ativa o Autocomplete do Google nas caixas de endereço
        window.initMap = function() {
            const options = {
                componentRestrictions: { country: "br" }, // Restringe resultados ao Brasil
                fields: ["formatted_address", "name"]
            };

            const pickupInput = document.getElementById('pickup');
            const destInput = document.getElementById('destination');

            new google.maps.places.Autocomplete(pickupInput, options);
            new google.maps.places.Autocomplete(destInput, options);

        };

        // Lógica do Botão de GPS manual
        document.getElementById('btn-gps').addEventListener('click', function() {
            if (navigator.geolocation) {
                const pickupInput = document.getElementById('pickup');
                const originalPlaceholder = pickupInput.placeholder;
                const btnGps = document.getElementById('btn-gps');
                const originalBtnHtml = btnGps.innerHTML;
                
                pickupInput.value = "";
                pickupInput.placeholder = "Buscando localização...";
                btnGps.innerHTML = "Buscando...";
                btnGps.style.opacity = "0.7";
                btnGps.style.pointerEvents = "none";

                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const latlng = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude
                        };
                        const geocoder = new google.maps.Geocoder();
                        geocoder.geocode({ location: latlng }, function(results, status) {
                            if (status === "OK" && results[0]) {
                                pickupInput.value = results[0].formatted_address;
                            } else {
                                alert("Não foi possível encontrar o endereço exato.");
                            }
                            pickupInput.placeholder = originalPlaceholder;
                            btnGps.innerHTML = originalBtnHtml;
                            btnGps.style.opacity = "1";
                            btnGps.style.pointerEvents = "auto";
                        });
                    },
                    function(error) {
                        console.warn("GPS ignorado ou erro.", error);
                        alert("Não foi possível acessar seu GPS. Verifique as permissões do navegador.");
                        pickupInput.placeholder = originalPlaceholder;
                        btnGps.innerHTML = originalBtnHtml;
                        btnGps.style.opacity = "1";
                        btnGps.style.pointerEvents = "auto";
                    },
                    { enableHighAccuracy: true, timeout: 10000 }
                );
            } else {
                alert("Seu navegador não suporta localização via GPS.");
            }
        });

        // Lógica simples para mostrar/esconder checkbox "Sem Carga" para Van e Caminhonete
        document.getElementById('vehicle_type').addEventListener('change', function(e) {
            const vanCondition = document.getElementById('van-condition');
            if(e.target.value === 'van' || e.target.value === 'caminhonete') {
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
    </script>
    <!-- O script do Mapa é carregado por último e já sabe que a função initMap existe -->
    <script async src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=places&loading=async&callback=initMap"></script>
</body>

</html>