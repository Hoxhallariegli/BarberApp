<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TranslationService
{
    /**
     * Përkthen një tekst ose një array tekstesh.
     */
    public function translate($data, $to, $from = 'en')
    {
        if (is_array($data)) {
            $translated = [];
            foreach ($data as $key => $value) {
                $translated[$key] = $this->translate($value, $to, $from);
            }
            return $translated;
        }

        if (empty($data) || is_numeric($data)) return $data;

        try {
            // Përdorim MyMemory API (Falas deri në 1000 fjalë/ditë pa key)
            $response = Http::get("https://api.mymemory.translated.net/get", [
                'q' => $data,
                'langpair' => "{$from}|{$to}"
            ]);

            if ($response->successful()) {
                return $response->json()['responseData']['translatedText'] ?? $data;
            }
        } catch (\Exception $e) {
            // Në rast dështimi, kthejmë tekstin origjinal
        }

        return $data;
    }
}
