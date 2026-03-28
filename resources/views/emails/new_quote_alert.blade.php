<!DOCTYPE html>
<html>
<head>
    <title>Novo Orçamento de Guincho</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <h2 style="color: #2e7d32;">🚨 Nova Solicitação de Guincho - ID: {{ $quote->id }}</h2>
    
    <div style="background: #f9f9f9; padding: 20px; border-radius: 8px; border: 1px solid #ddd;">
        <p><strong>👤 Cliente:</strong> {{ $quote->name }}</p>
        <p><strong>📱 Telefone:</strong> {{ $quote->phone }} 
            @if(!empty($quote->phone))
                <a href="https://wa.me/55{{ preg_replace('/\D/', '', $quote->phone) }}" style="color: #25D366; text-decoration: none; font-weight: bold;">(Chamar no WhatsApp)</a>
            @endif
        </p>
        
        <hr style="border-top: 1px solid #eee;">
        
        <p><strong>📍 Retirada:</strong> {{ $quote->pickup }} 
            <br><a href="https://www.google.com/maps/search/?api=1&query={{ rawurlencode($quote->pickup) }}" style="color: #3b82f6; text-decoration: none; font-size: 0.9rem;">(Ver no Mapa)</a>
        </p>
        <p><strong>🏁 Destino:</strong> {{ $quote->destination }} 
            <br><a href="https://www.google.com/maps/search/?api=1&query={{ rawurlencode($quote->destination) }}" style="color: #3b82f6; text-decoration: none; font-size: 0.9rem;">(Ver no Mapa)</a>
        </p>
        <p><strong>📏 Distância Exata:</strong> {{ number_format($quote->distance_km, 1, ',', '.') }} km</p>
        
        <hr style="border-top: 1px solid #eee;">
        
        <p><strong>🚙 Veículo:</strong> {{ ucfirst($quote->vehicle_type) }}</p>
        <p><strong>⚙️ Rodas Livres?</strong> {{ $quote->free_wheels ? 'Sim' : 'Não' }}</p>
        <p><strong>🕹️ Câmbio Destravado?</strong> {{ $quote->unlocked_gearbox ? 'Sim' : 'Não' }}</p>
        
        @if(in_array($quote->vehicle_type, ['van', 'caminhonete']))
            <p><strong>📦 Sem Carga?</strong> {{ $quote->empty_load ? 'Sim' : 'Não' }}</p>
        @endif
        
        <h3 style="color: #b71c1c;">Valor Estimado: R$ {{ number_format($quote->price, 2, ',', '.') }}</h3>
    </div>
    
    <p style="font-size: 12px; color: #777; margin-top: 20px;">Este é um e-mail gerado automaticamente pelo sistema Solução Guincho.</p>
</body>
</html>
