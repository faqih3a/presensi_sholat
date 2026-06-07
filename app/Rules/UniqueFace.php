<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\Santri;

class UniqueFace implements ValidationRule
{
    protected $ignoreId;

    public function __construct($ignoreId = null)
    {
        $this->ignoreId = $ignoreId;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            return;
        }

        $newDescriptor = json_decode($value, true);
        if (!is_array($newDescriptor) || count($newDescriptor) !== 128) {
            $fail('Format face descriptor tidak valid.');
            return;
        }

        $query = Santri::query();
        if ($this->ignoreId) {
            $query->where('id', '!=', $this->ignoreId);
        }

        $existingSantris = $query->get(['id', 'nama', 'face_descriptor']);
        $threshold = 0.45; // Match threshold for face-api.js (Euclidean distance <= 0.45 is a match)

        foreach ($existingSantris as $santri) {
            $existingDescriptor = json_decode($santri->face_descriptor, true);
            if (!is_array($existingDescriptor) || count($existingDescriptor) !== 128) {
                continue;
            }

            // Calculate Euclidean distance
            $sum = 0.0;
            for ($i = 0; $i < 128; $i++) {
                $diff = $newDescriptor[$i] - $existingDescriptor[$i];
                $sum += $diff * $diff;
            }
            $distance = sqrt($sum);

            if ($distance <= $threshold) {
                $fail("Wajah ini sudah terdaftar atas nama '{$santri->nama}'. Setiap santri harus menggunakan wajah yang unik.");
                return;
            }
        }
    }
}
