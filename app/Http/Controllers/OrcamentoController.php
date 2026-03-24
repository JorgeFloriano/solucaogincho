<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OrcamentoController extends Controller
{
    public function calcular(Request $request)
    {
        $request->validate([
            'vehicle_type' => 'required|string',
            'pickup' => 'required|string',
            'destination' => 'required|string',
        ]);

        $pickup = $request->input('pickup');
        $destination = $request->input('destination');
        $vehicleType = $request->input('vehicle_type');
        $rodasLivres = $request->has('rodas_livres');
        $cambioDestravado = $request->has('cambio_destravado');
        $semCarga = $request->has('sem_carga');

        // 1. Calculate Distance via Google Maps API
        $distanceKm = $this->getDistance($pickup, $destination);

        // 2. Calculate Pricing
        $price = $this->calculatePrice($distanceKm, $vehicleType);

        // 3. Generate WhatsApp Link
        $message = $this->generateWhatsAppMessage(
            $pickup, $destination, $vehicleType, $distanceKm, $price, $rodasLivres, $cambioDestravado, $semCarga
        );

        $phone = "5515981655797"; // Central do WhatsApp do Paulo
        $waLink = "https://wa.me/{$phone}?text=" . urlencode($message);

        return back()->with([
            'success' => true,
            'distance' => $distanceKm,
            'price' => $price,
            'waLink' => $waLink
        ]);
    }

    private function getDistance($origin, $destination)
    {
        $apiKey = env('GOOGLE_MAPS_API_KEY');

        // Se a chave não estiver configurada no .env, usamos 12.5km como Mock de Teste para o layout
        if (!$apiKey) {
            return 12.5;
        }

        $response = Http::get('https://maps.googleapis.com/maps/api/distancematrix/json', [
            'origins' => $origin,
            'destinations' => $destination,
            'key' => $apiKey
        ]);

        if ($response->successful()) {
            $data = $response->json();
            if (($data['status'] === 'OK') && isset($data['rows'][0]['elements'][0]['distance'])) {
                $meters = $data['rows'][0]['elements'][0]['distance']['value'];
                return round($meters / 1000, 1);
            }
        }

        // Falha ou não encontrado local exato (fallback de segurança para o sistema não quebrar)
        return 10.0;
    }

    private function calculatePrice($distanceKm, $vehicleType)
    {
        if ($vehicleType === 'van' || $vehicleType === 'caminhonete') {
            return round($distanceKm * 6.50, 2);
        }

        // Carro de Passeio ou Moto
        if ($distanceKm <= 5) {
            return 130.00;
        }
        elseif ($distanceKm <= 10) {
            return 150.00;
        }
        else {
            $extraKm = $distanceKm - 10;
            return 150.00 + ($extraKm * 5.00);
        }
    }

    private function generateWhatsAppMessage($pickup, $destination, $vehicleType, $distanceKm, $price, $rodas, $cambio, $semCarga)
    {
        $priceFormatted = number_format($price, 2, ',', '.');
        $distFormatted = number_format($distanceKm, 1, ',', '.');

        $tipos = [
            'carro' => 'Carro de Passeio',
            'caminhonete' => 'Caminhonete',
            'van' => 'Van',
            'moto' => 'Moto'
        ];
        $nomeVeiculo = $tipos[$vehicleType] ?? 'Veículo';

        $msg = "*NOVO ORÇAMENTO DE GUINCHO* 🚨\n\n";
        $msg .= "📍 *Retirada:* {$pickup}\n";
        $msg .= "🏁 *Destino:* {$destination}\n";
        $msg .= "📏 *Distância Exata:* {$distFormatted} km\n\n";

        $msg .= "🚗 *Veículo:* {$nomeVeiculo}\n";
        $msg .= "⚙️ *Rodas Livres?* " . ($rodas ? 'Sim' : 'Não') . "\n";
        $msg .= "🕹️ *Câmbio Destravado?* " . ($cambio ? 'Sim' : 'Não') . "\n";

        if ($vehicleType === 'van' || $vehicleType === 'caminhonete') {
            $msg .= "📦 *Sem Carga?* " . ($semCarga ? 'Sim' : 'Não') . "\n";
        }

        $msg .= "\n💰 *VALOR ESTIMADO:* R$ {$priceFormatted}";

        return $msg;
    }
}