<?php
// includes/calculos.php

class CalculadoraPredial
{
    private $db;
    private $anio;
    private $uit;

    public function __construct($db, $anio)
    {
        $this->db = $db;
        $this->anio = $anio;
        $this->loadParams();
    }

    private function loadParams()
    {
        // Cargar UIT del año
        $stmt = $this->db->prepare("SELECT valor_uit FROM imp_param_principales WHERE anio = ?");
        $stmt->execute([$this->anio]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->uit = $row ? $row['valor_uit'] : 4950.00; // Default 2023
    }

    // Calcula el Impuesto Predial basado en tramos de UIT
    public function calcularImpuesto($base_imponible)
    {
        $impuesto = 0;
        $uit = $this->uit;
        $remanente = $base_imponible;

        // Tramo 1: Hasta 15 UIT (0.2%)
        $tramo1 = 15 * $uit;
        if ($remanente > 0) {
            $base = min($remanente, $tramo1);
            $impuesto += $base * 0.002;
            $remanente -= $base;
        }

        // Tramo 2: Exceso de 15 hasta 60 UIT (0.6%)
        $tramo2 = (60 - 15) * $uit; // 45 UIT
        if ($remanente > 0) {
            $base = min($remanente, $tramo2);
            $impuesto += $base * 0.006;
            $remanente -= $base;
        }

        // Tramo 3: Exceso de 60 UIT (1.0%)
        if ($remanente > 0) {
            $impuesto += $remanente * 0.010;
        }

        return round($impuesto, 2);
    }

    // Calcula Alcabala (Referencial)
    public function calcularAlcabala($valor_transferencia)
    {
        $tramo_exonerado = 10 * $this->uit;
        if ($valor_transferencia <= $tramo_exonerado)
            return 0.00;

        $base = $valor_transferencia - $tramo_exonerado;
        return round($base * 0.03, 2);
    }

    public function getUIT()
    {
        return $this->uit;
    }
}
?>