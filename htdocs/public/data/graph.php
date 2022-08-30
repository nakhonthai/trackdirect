<?php

require dirname(__DIR__) . "../../includes/bootstrap.php";

$response = [];
$station = StationRepository::getInstance()->getObjectById($_GET['id'] ?? null);
if ($station->isExistingObject()) {
    $graphIdx = $_GET['index'] ?? 0;
    $graphType = $_GET['type'] ?? '';

    $maxDays = 10;
    if (!isAllowedToShowOlderData()) {
        $maxDays = 1;
    }

    if ($graphType == 'telemetry') {       // No more than 250 rows for graphs
      $telemetryColors = array(
        0 => null,
        1 => array('borderColor' => '#2E2EFE', 'backgroundColor' => '#81BEF7'),
        2 => array('borderColor' => '#2E2EFE', 'backgroundColor' => '#81BEF7'),
        3 => array('borderColor' => '#2E2EFE', 'backgroundColor' => '#81BEF7'),
        4 => array('borderColor' => '#2E2EFE', 'backgroundColor' => '#81BEF7'),
        5 => array('borderColor' => '#2E2EFE', 'backgroundColor' => '#81BEF7'),
      );

      $telemetryPackets = PacketTelemetryRepository::getInstance()->getLatestObjectListByStationId($station->id, 250, 0, $maxDays);
      $latestPacketTelemetry = (count($telemetryPackets) > 0 ? $telemetryPackets[0] : new PacketTelemetry(null));

      // Ajax graph data
      if ($graphIdx > 0) {
        $response = array_merge($response, $telemetryColors[$graphIdx]);
        $response['label'] = $latestPacketTelemetry->getValueParameterName($graphIdx);
        foreach ($telemetryPackets as $packetTelemetry) {
            $response['data'][] = array('x' => ($packetTelemetry->wxRawTimestamp != null ? $packetTelemetry->wxRawTimestamp : $packetTelemetry->timestamp) * 1000, 'y' => ($packetTelemetry->val1 !== null) ? round($packetTelemetry->getValue($graphIdx), 2) : '');
        }
        $response['latest_timestamp'] = $response['data'][0]['x'] / 1000;
        $response['oldest_timestamp'] = $response['data'][sizeof($response['data'])-1]['x'] / 1000;
        $response['records'] = sizeof($response['data']);
      }
    }

    if ($graphType == 'weather') {       // No more than 250 rows for graphs
      $graphLabels = array('Time', 'Temperature', 'Humidity', 'Pressure', 'Rain (Last Hour)', 'Rain (Last 24 Hours)', 'Rain (Since Midnight)', 'Wind Speed', 'Wind Direction', 'Luminosity', 'Snow');
      $weatherPackets = PacketWeatherRepository::getInstance()->getLatestObjectListByStationIdAndLimit($station->id, 250, 0, $maxDays);

      // Ajax graph data
      if ($graphIdx > 0) {
        $response['label'] = $graphLabels[$graphIdx];
        switch ($graphIdx) {
          case 0:
            break;
          case 1: // Temperature
            foreach ($weatherPackets as $packetWeather) {
              $response['data'][] = array('x' => ($packetWeather->wxRawTimestamp != null ? $packetWeather->wxRawTimestamp : $packetWeather->timestamp) * 1000, 'y' => isImperialUnitUser() ? round(convertCelciusToFahrenheit($packetWeather->temperature), 2) : round($packetWeather->temperature, 2));
            }
            $response['borderColor'] = '#2E2EFE';
            $response['backgroundColor'] = '#81BEF7';
            break;
          case 2: // Humidity
            foreach ($weatherPackets as $packetWeather) {
              $response['data'][] = array('x' => ($packetWeather->wxRawTimestamp != null ? $packetWeather->wxRawTimestamp : $packetWeather->timestamp) * 1000, 'y' => $packetWeather->humidity);
            }
            $response['borderColor'] = '#31B404';
            $response['backgroundColor'] = '#3ADF00';
            break;
          case 3: // Pressure
            foreach ($weatherPackets as $packetWeather) {
              $response['data'][] = array('x' => ($packetWeather->wxRawTimestamp != null ? $packetWeather->wxRawTimestamp : $packetWeather->timestamp) * 1000, 'y' => isImperialUnitUser() ? round(convertMbarToMmhg($packetWeather->pressure), 1) : round($packetWeather->pressure, 1));
            }
            $response['borderColor'] = '#DF0101';
            $response['backgroundColor'] = '#FA5858';
            break;
          case 4: // Rain - Last hour
            if ($weatherPackets[0]->rain_1h !== null) {
              foreach ($weatherPackets as $packetWeather) {
                $response['data'][] = array('x' => ($packetWeather->wxRawTimestamp != null ? $packetWeather->wxRawTimestamp : $packetWeather->timestamp) * 1000, 'y' => isImperialUnitUser() ? round(convertMmToInch($packetWeather->rain_1h), 2) : round($packetWeather->rain_1h, 2));
              }
            }
            $response['borderColor'] = '#31B404';
            $response['backgroundColor'] = '#3ADF00';
            break;
          case 5: // Rain - Last 24 hours
            if ($weatherPackets[0]->rain_24h !== null) {
              foreach ($weatherPackets as $packetWeather) {
                $response['data'][] = array('x' => ($packetWeather->wxRawTimestamp != null ? $packetWeather->wxRawTimestamp : $packetWeather->timestamp) * 1000, 'y' => isImperialUnitUser() ? round(convertMmToInch($packetWeather->rain_24h), 2) : round($packetWeather->rain_24h, 2));
              }
            }
            $response['borderColor'] = '#31B404';
            $response['backgroundColor'] = '#3ADF00';
            break;
          case 6: // Rain - Since midnight
            if ($weatherPackets[0]->rain_since_midnight !== null) {
              foreach ($weatherPackets as $packetWeather) {
                $response['data'][] = array('x' => ($packetWeather->wxRawTimestamp != null ? $packetWeather->wxRawTimestamp : $packetWeather->timestamp) * 1000, 'y' => isImperialUnitUser() ? round(convertMmToInch($packetWeather->rain_since_midnight), 2) : round($packetWeather->rain_since_midnight, 2));
              }
            }
            $response['borderColor'] = '#31B404';
            $response['backgroundColor'] = '#3ADF00';
            break;
          case 7: // Wind speed
            foreach ($weatherPackets as $packetWeather) {
              if ($packetWeather->wind_speed !== null && $packetWeather->wind_speed > 0) {
                $response['data'][] = array('x' => ($packetWeather->wxRawTimestamp != null ? $packetWeather->wxRawTimestamp : $packetWeather->timestamp) * 1000, 'y' => isImperialUnitUser() ? round(convertMpsToMph($packetWeather->wind_speed), 2) : round($packetWeather->wind_speed, 2));
              } else {
                $response['data'][] = array('x' => ($packetWeather->wxRawTimestamp != null ? $packetWeather->wxRawTimestamp : $packetWeather->timestamp) * 1000, 'y' => 0);
              }
            }
            $response['borderColor'] = '#0174DF';
            $response['backgroundColor'] = '#81BEF7';
            break;
          case 8: // Wind direction
            foreach ($weatherPackets as $packetWeather) {
              if ($packetWeather->wind_direction !== null && $packetWeather->wind_direction > 0) {
                $response['data'][] = array('x' => ($packetWeather->wxRawTimestamp != null ? $packetWeather->wxRawTimestamp : $packetWeather->timestamp) * 1000, 'y' => round($packetWeather->wind_direction, 0));
              } else {
                $response['data'][] = array('x' => ($packetWeather->wxRawTimestamp != null ? $packetWeather->wxRawTimestamp : $packetWeather->timestamp) * 1000, 'y' => 0);
              }
            }
            $response['borderColor'] = '#0174DF';
            $response['backgroundColor'] = '#81BEF7';
            break;
          case 9: // Luminosity
            foreach ($weatherPackets as $packetWeather) {
              $response['data'][] = array('x' => ($packetWeather->wxRawTimestamp != null ? $packetWeather->wxRawTimestamp : $packetWeather->timestamp) * 1000, 'y' => round($packetWeather->luminosity, 0));
            }
            $response['borderColor'] = '#FF0080';
            $response['backgroundColor'] = '#B4045F';
            break;
          case 10: // Snow
            foreach ($weatherPackets as $packetWeather) {
              $response['data'][] = array('x' => ($packetWeather->wxRawTimestamp != null ? $packetWeather->wxRawTimestamp : $packetWeather->timestamp) * 1000, 'y' => isImperialUnitUser() ? round(convertMmToInch($packetWeather->snow), 0) : round($packetWeather->snow, 0));
            }
            $response['borderColor'] = '#A4A4A4';
            $response['backgroundColor'] = '#E0ECF8';
            break;
        }
        $response['latest_timestamp'] = $response['data'][0]['x'] / 1000;
        $response['oldest_timestamp'] = $response['data'][sizeof($response['data'])-1]['x'] / 1000;
        $response['records'] = sizeof($response['data']);
      }
    }
}

header('Content-type: application/json');
echo json_encode($response);
