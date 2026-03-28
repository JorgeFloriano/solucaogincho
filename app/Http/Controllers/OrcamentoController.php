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
            'name' => 'required|string|max:255',
            'phone' => ['required', 'string', 'regex:/^\(\d{2}\)\s\d{4,5}\-\d{4}$/'],
            'vehicle_type' => 'required|string',
            'pickup' => 'required|string',
            'destination' => 'required|string',
        ]);

        $name = $request->input('name');
        $phone_customer = $request->input('phone');
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
            'name' => $name,
            'phone' => $phone_customer,
            'vehicle_type' => $vehicleType,
            'pickup' => $pickup,
            'destination' => $destination,
            'distance_km' => $distanceKm,
            'price' => $price,
            'free_wheels' => $freeWheels,
            'unlocked_gearbox' => $unlockedGearbox,
            'empty_load' => $emptyLoad,
        ];

        session()->put('pending_quote', [
            'data' => $quoteData
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
        $quote = Quote::create($pending['data']);

        // Enviar e-mail de notificação (Substitua admin@... pelo seu e-mail)
        \Illuminate\Support\Facades\Mail::to(env('MAIL_FROM_ADDRESS', 'admin@solucaoguincho.com.br'))
            ->send(new \App\Mail\NewQuoteAlert($quote));

        // Generate WhatsApp Link with the actual Quote ID
        $message = $this->generateWhatsAppMessage($quote);

        $phone = "5515981655797"; // Central do WhatsApp do Paulo
        $waLink = "https://wa.me/{$phone}?text=" . rawurlencode($message);

        // Clear session so it's not submitted twice
        session()->forget('pending_quote');

        // Redirect directly to WhatsApp Web / App
        return redirect()->away($waLink);
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
        } elseif ($distanceKm <= 10) {
            return 150.00;
        } else {
            $extraKm = $distanceKm - 10;
            if ($vehicleType === 'van' || $vehicleType === 'caminhonete') {
                return 150.00 + (round($extraKm * 6.50, 2));
            }
            return 150.00 + (round($extraKm * 5.00, 2));
        }
    }

    private function generateWhatsAppMessage(Quote $quote)
    {
        $distFormatted = number_format($quote->distance_km, 1, ',', '.');

        $types = [
            'carro' => 'Carro de Passeio',
            'caminhonete' => 'Caminhonete',
            'van' => 'Van',
            'moto' => 'Moto'
        ];
        $vehicleName = $types[$quote->vehicle_type] ?? 'Veículo';

        $emojis = [
            'carro' => "\u{1f697}",
            'caminhonete' => "\u{1f6fb}",
            'van' => "\u{1f690}",
            'moto' => "\u{1f3cd}\u{fe0f}"
        ];
        $vehicleEmoji = $emojis[$quote->vehicle_type] ?? "\u{1f697}";

        $pickupLink = "https://www.google.com/maps/search/?api=1&query=" . rawurlencode($quote->pickup);
        $destinationLink = "https://www.google.com/maps/search/?api=1&query=" . rawurlencode($quote->destination);

        $msg = "*NOVA SOLICITAÇÃO DE GUINCHO - ID: {$quote->id}* \u{1f6a8}\n\n";
        $msg .= "\u{1f464} *Cliente:* {$quote->name}\n";

        if (!empty($quote->phone)) {
            $numericPhone = preg_replace('/\D/', '', $quote->phone);
            $waClientLink = "https://wa.me/55{$numericPhone}";
            $msg .= "\u{1f4f1} *Telefone:* {$quote->phone}\n";
            $msg .= "\u{1f4ac} *Falar com cliente:* {$waClientLink}\n\n";
        }

        $msg .= "\u{1f4cd} *Retirada:* {$quote->pickup}\n";
        $msg .= "\u{1f5fa}\u{fe0f} *Mapa (Retirada):* {$pickupLink}\n\n";
        $msg .= "\u{1f3c1} *Destino:* {$quote->destination}\n";
        $msg .= "\u{1f5fa}\u{fe0f} *Mapa (Destino):* {$destinationLink}\n\n";
        $msg .= "\u{1f4cf} *Distância Exata:* {$distFormatted} km\n\n";

        $msg .= "{$vehicleEmoji} *Veículo:* {$vehicleName}\n";
        $msg .= "\u{2699}\u{fe0f} *Rodas Livres?* " . ($quote->free_wheels ? 'Sim' : 'Não') . "\n";
        $msg .= "\u{1f579}\u{fe0f} *Câmbio Destravado?* " . ($quote->unlocked_gearbox ? 'Sim' : 'Não') . "\n";

        if ($quote->vehicle_type === 'van' || $quote->vehicle_type === 'caminhonete') {
            $msg .= "\u{1f4e6} *Sem Carga?* " . ($quote->empty_load ? 'Sim' : 'Não') . "\n";
        }

        return $msg;
    }
}