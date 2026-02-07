<?php

namespace App\Http\Controllers\Concerns;

trait LipidInterpretation
{
    private function determineLipidInterpretation(string $parameterName, ?float $value, ?string $sex): ?string
    {
        if ($value === null) {
            return null;
        }

        $name = strtoupper(trim($parameterName));
        $sexNormalized = strtolower(trim((string) ($sex ?? '')));

        if (str_contains($name, 'TOTAL CHOLESTEROL') && !str_contains($name, 'RATIO')) {
            return $this->interpretTotalCholesterol($value);
        }

        if (str_contains($name, 'LDL CHOLESTEROL')) {
            return $this->interpretLdlCholesterol($value);
        }

        if (str_contains($name, 'HDL CHOLESTEROL') && !str_contains($name, 'RATIO')) {
            return $this->interpretHdlCholesterol($value, $sexNormalized);
        }

        if (str_contains($name, 'VLDL CHOLESTEROL')) {
            return $this->interpretVldlCholesterol($value);
        }

        return null;
    }

    private function interpretTotalCholesterol(float $value): string
    {
        if ($value < 200.0) {
            return 'DESIRABLE';
        }
        if ($value < 240.0) {
            return 'BORDERLINE HIGH';
        }
        return 'HIGH';
    }

    private function interpretTriglycerides(float $value): string
    {
        if ($value < 150.0) {
            return 'DESIRABLE';
        }
        if ($value < 200.0) {
            return 'BORDERLINE HIGH';
        }
        if ($value < 500.0) {
            return 'HIGH';
        }
        return 'VERY HIGH';
    }

    private function interpretLdlCholesterol(float $value): string
    {
        if ($value < 100.0) {
            return 'OPTIMAL';
        }
        if ($value < 130.0) {
            return 'NEAR OPTIMAL';
        }
        if ($value < 160.0) {
            return 'BORDERLINE HIGH';
        }
        if ($value < 190.0) {
            return 'HIGH';
        }
        return 'VERY HIGH';
    }

    private function interpretHdlCholesterol(float $value, string $sex): string
    {
        if (strpos($sex, 'male') === 0) {
            if ($value >= 60.0) {
                return 'OPTIMAL';
            }
            if ($value >= 40.0) {
                return 'NEAR OPTIMAL';
            }
            return 'UNDESIRABLE';
        }

        if ($value >= 60.0) {
            return 'OPTIMAL';
        }
        if ($value >= 50.0) {
            return 'NEAR OPTIMAL';
        }
        return 'UNDESIRABLE';
    }

    private function interpretVldlCholesterol(float $value): string
    {
        if ($value < 3.3) {
            return 'OPTIMAL';
        }
        if ($value <= 4.5) {
            return 'BORDERLINE HIGH';
        }
        return 'HIGH';
    }

    private function interpretTgHdlRatio(float $value): string
    {
        if ($value < 3.3) {
            return 'OPTIMAL';
        }
        if ($value <= 4.5) {
            return 'BORDERLINE HIGH';
        }
        return 'HIGH';
    }
}
