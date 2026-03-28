<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Quote;

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
        $freeWheels = $request->has('rodas_livres');
        $unlockedGearbox = $request->has('cambio_destravado');
        $emptyLoad = $request->has('sem_carga');

        // 1. Calculate Distance via Google Maps API
        $distanceKm = $this->getDistance($pickup, $destination);

        // 2. Calculate Pricing
        $price = $this->calculatePrice($distanceKm, $vehicleType);

        // 3. Instead of saving, store data in session for the next step
        $quoteData = [
            'vehicle_type' => $vehicleType,
            'pickup' => $pickup,
            'destination' => $destination,
            'distance_km' => $distanceKm,
            'price' => $price,
            'free_wheels' => $freeWheels,
            'unlocked_gearbox' => $unlockedGearbox,
            'empty_load' => $emptyLoad,
        ];

        // 4. Generate WhatsApp Link
        $message = $this->generateWhatsAppMessage(
            $pickup, $destination, $vehicleType, $distanceKm, $price, $freeWheels, $unlockedGearbox, $emptyLoad
        );

        $phone = "5515981655797"; // Central do WhatsApp do Paulo
        $waLink = "https://wa.me/{$phone}?text=" . urlencode($message);

        session()->put('pending_quote', [
            'data' => $quoteData,
            'waLink' => $waLink
        ]);

        return back()->withInput()->with([
            'success' => true,
            'distance' => $distanceKm,
            'price' => $price,
        ]);
    }

    public function confirmar()
    {
        $pending = session('pending_quote');

        if (!$pending) {
            return redirect('/');
        }

        // Save to Database
        Quote::create($pending['data']);

        // Clear session so it's not submitted twice
        session()->forget('pending_quote');

        // Redirect directly to WhatsApp Web / App
        return redirect()->away($pending['waLink']);
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
        if ($distanceKm <= 5) {
            return 130.00;
        }
        elseif ($distanceKm <= 10) {
            return 150.00;
        }
        else {
            $extraKm = $distanceKm - 10;
            if ($vehicleType === 'van' || $vehicleType === 'caminhonete') {
                return 150.00 + (round($extraKm * 6.50, 2));
            }
            return 150.00 + (round($extraKm * 5.00, 2));
        }
    }

    private function generateWhatsAppMessage($pickup, $destination, $vehicleType, $distanceKm, $price, $freeWheels, $unlockedGearbox, $emptyLoad)
    {
        $priceFormatted = number_format($price, 2, ',', '.');
        $distFormatted = number_format($distanceKm, 1, ',', '.');

        $types = [
            'carro' => 'Carro de Passeio',
            'caminhonete' => 'Caminhonete',
            'van' => 'Van',
            'moto' => 'Moto'
        ];
        $vehicleName = $types[$vehicleType] ?? 'Veículo';

        $msg = "*NOVO ORÇAMENTO DE GUINCHO* \u{1f6a8}\n\n";
        $msg .= "\u{1f4cd} *Retirada:* {$pickup}\n";
        $msg .= "\u{1f3c1} *Destino:* {$destination}\n";
        $msg .= "\u{1f4cf} *Distância Exata:* {$distFormatted} km\n\n";

        $msg .= "\u{1f697} *Veículo:* {$vehicleName}\n";
        $msg .= "\u{2699}\u{fe0f} *Rodas Livres?* " . ($freeWheels ? 'Sim' : 'Não') . "\n";
        $msg .= "\u{1f579}\u{fe0f} *Câmbio Destravado?* " . ($unlockedGearbox ? 'Sim' : 'Não') . "\n";

        if ($vehicleType === 'van' || $vehicleType === 'caminhonete') {
            $msg .= "\u{1f4e6} *Sem Carga?* " . ($emptyLoad ? 'Sim' : 'Não') . "\n";
        }

        $msg .= "\n💰 *VALOR ESTIMADO:* R$ {$priceFormatted}";

        return $msg;
    }
}