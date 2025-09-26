<?php
defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;

class PlgContentIammeter extends CMSPlugin
{
    public function onContentPrepare($context, &$article, &$params, $limitstart)
    {
        if (strpos($article->text, '{iammeter}') === false) {
            return;
        }

        // Zähler und Labels
        $meters = [
            'Solarproduktion' => 'your meter sn 1',
            'Netzeinspeisung' => 'your meter sn 2'
        ];

        // Farben für die Blöcke
        $colors = [
            'Solarproduktion' => '#4CAF50',
            'Netzeinspeisung' => '#2196F3'
        ];

        // Offsets & Tokens aus Backend-Parametern
        $offsets = [
            'Solarproduktion' => (float) $this->params->get('offset_solar', 0),
            'Netzeinspeisung' => (float) $this->params->get('offset_grid', 0)
        ];

        $tokens = [
            'Solarproduktion' => trim($this->params->get('token_solar', '')),
            'Netzeinspeisung' => trim($this->params->get('token_grid', ''))
        ];

        // Container starten
        $output = "<div style='display:flex; flex-wrap:wrap; gap:20px;'>";

        foreach ($meters as $label => $sn) {
            $token = $tokens[$label] ?: '';
            if (!$token) {
                $output .= "<div style='color:red;'>$label: Kein Token gesetzt</div>";
                continue;
            }

            $url = "https://www.iammeter.com/api/v1/site/meterdata/" . urlencode($sn);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['token: ' . $token]);

            $response = curl_exec($ch);
            $curl_error = curl_error($ch);
            curl_close($ch);

            if ($response === false) {
                $output .= "<div style='color:red;'>$label: Fehler (" . htmlspecialchars($curl_error) . ")</div>";
                continue;
            }

            $data = json_decode($response, true);
            if (!isset($data['data']['values'][0])) {
                $output .= "<div style='color:red;'>$label: Keine Daten erhalten</div>";
                continue;
            }

            $values = $data['data']['values'][0];
            $voltage      = isset($values[0]) ? htmlspecialchars($values[0]) . ' V' : 'n/a';
            $current      = isset($values[1]) ? htmlspecialchars($values[1]) . ' A' : 'n/a';
            $power        = isset($values[2]) ? htmlspecialchars($values[2]) . ' W' : 'n/a';

            // Import-Energie (Bezug) – Offset je Zähler
            $importEnergy = isset($values[3]) ? htmlspecialchars(number_format($values[3] + $offsets[$label], 2)) . ' kWh' : 'n/a';

            // Export-Energie (Einspeisung) – unverändert
            $exportEnergy = isset($values[4]) ? htmlspecialchars(number_format($values[4], 2)) . ' kWh' : 'n/a';

            $localTime = isset($data['data']['localTime']) ? htmlspecialchars($data['data']['localTime']) : '';

            // Ausgabe pro Block
            $output .= "<div style='border:1px solid #ccc; padding:15px; flex:1; min-width:250px; background-color:{$colors[$label]}; color:white; border-radius:8px;'>";
            $output .= "<h3 style='margin-top:0;'>$label</h3>";
            $output .= "<table style='border-collapse:collapse; width:100%;'>";
            $output .= "<tr><td>⚡ Spannung</td><td>{$voltage}</td></tr>";
            $output .= "<tr><td>🔌 Strom</td><td>{$current}</td></tr>";
            $output .= "<tr><td>☀️ Leistung</td><td style='color:yellow;'>{$power}</td></tr>";
            $output .= "<tr><td>🏠 Bezug</td><td>{$importEnergy}</td></tr>";
            $output .= "<tr><td>🌐 Einspeisung</td><td>{$exportEnergy}</td></tr>";
            $output .= "<tr><td colspan='2' style='font-size:0.8em; text-align:right;'>Letzte Aktualisierung: {$localTime}</td></tr>";
            $output .= "</table></div>";
        }

        $output .= "</div>";

        $article->text = str_replace('{iammeter}', $output, $article->text);
    }
}
