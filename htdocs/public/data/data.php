<?php
require dirname(__DIR__) . "../../includes/bootstrap.php";

// Registered modules - for security reasons.
// eventually this could be done better.
$registered_modules = array(
  'weather' => array('getLatestWeather', 'getWeather'),
  'telemetry' => array('getLatestTelemetry', 'getTelemetryValues', 'getTelemetryBits'),
);

// Response array to send back
$response = [];

// Module exists?
$module = $_GET['module'] ?? '';
if (array_key_exists($module, $registered_modules) == false)
{
  die('Invalid Module');
}

// Command registered for the module?
$command = $_GET['command'] ?? '';
if (in_array($command, $registered_modules[$module]) == false)
{
  die('Invalid Command');
}

// Make sure station is valid
$station = StationRepository::getInstance()->getObjectById($_GET['id'] ?? null);
if ($station->isExistingObject())
{
  // Execute the command and return response
  $command($station, $response);
}
else
{
  $response['status'] = 'error';
  $response['message'] = 'Station not found.';
}


function getLatestWeather($station, &$response)
{
  $weatherPackets = PacketWeatherRepository::getInstance()->getLatestObjectListByStationIdAndLimit($station->id, 1, 0, 7);
  if ($weatherPackets != null || sizeof($weatherPackets) == 1)
  {
    $response['data']['temperature'] = isImperialUnitUser() ? round(convertCelciusToFahrenheit($weatherPackets[0]->temperature), 2) : round($weatherPackets[0]->temperature, 2);
    $response['data']['humidity'] = $weatherPackets[0]->humidity;
    $response['data']['pressure'] = isImperialUnitUser() ? round(convertMbarToInchHg($weatherPackets[0]->pressure), 1) : round($weatherPackets[0]->pressure, 1);
    $response['data']['wind_speed'] = isImperialUnitUser() ? round(convertMpsToMph($weatherPackets[0]->wind_speed), 2) : round($weatherPackets[0]->wind_speed, 2);
    $response['data']['wind_direction'] = $weatherPackets[0]->wind_direction;
    $response['data']['luminosity'] = round($weatherPackets[0]->luminosity, 0);
    if ($weatherPackets[0]->rain_1h != null)
      $response['data']['rain_1h'] = isImperialUnitUser() ? round(convertMmToInch($weatherPackets[0]->rain_1h), 2) : round($weatherPackets[0]->rain_1h, 2);
    if ($weatherPackets[0]->rain_24h != null)
      $response['data']['rain_24h'] = isImperialUnitUser() ? round(convertMmToInch($weatherPackets[0]->rain_24h), 2) : round($weatherPackets[0]->rain_24h, 2);
    if ($weatherPackets[0]->rain_since_midnight != null)
      $response['data']['rain_since_midnight'] = isImperialUnitUser() ? round(convertMmToInch($weatherPackets[0]->rain_since_midnight), 2) : round($weatherPackets[0]->rain_since_midnight, 2);
    $response['data']['snow'] = isImperialUnitUser() ? round(convertMmToInch($weatherPackets[0]->snow), 2) : round($weatherPackets[0]->snow, 2);

    $response['status'] = 'success';
    $response['message'] = '';
  }
  else
  {
    $response['status'] = 'error';
    $response['message'] = 'No weather data found.';
  }
}


function getWeather($station, &$response)
{
  $maxDays = 100;
  $start = $_GET['start'] ?? time()-864000;
  $end = $_GET['end'] ?? time();

  $rows = 5000;
  $offset = 0;

  $start_time = microtime(true);
  $weatherPackets = PacketWeatherRepository::getInstance()->getLatestObjectListByStationIdAndLimit($station->id, $rows, $offset, $maxDays, $start, $end);

  $response['data']['dbtime'] = round(microtime(true) - $start_time, 4);
  $response['status'] = 'success';
  $response['message'] = '';

  foreach ($weatherPackets as $packetWeather)
  {
    $data = array('ts' => $packetWeather->wxRawTimestamp != null ? $packetWeather->wxRawTimestamp*1000 : $packetWeather->timestamp*1000);
    $data[] = isImperialUnitUser() ? round(convertCelciusToFahrenheit($packetWeather->temperature), 2) : round($packetWeather->temperature, 2);
    $data[] = $packetWeather->humidity;
    $data[] = isImperialUnitUser() ? round(convertMbarToInchHg($packetWeather->pressure), 1) : round($packetWeather->pressure, 1);
    $data[] = isImperialUnitUser() ? round(convertMpsToMph($packetWeather->wind_speed), 2) : round($packetWeather->wind_speed, 2);
    $data[] = $packetWeather->wind_direction;
    if ($packetWeather->rain_1h != null)
      $data[] = isImperialUnitUser() ? round(convertMmToInch($packetWeather->rain_1h), 2) : round($packetWeather->rain_1h, 2);
    else
      $data[] = '';

    if ($packetWeather->rain_24h != null)
      $data[] = isImperialUnitUser() ? round(convertMmToInch($packetWeather->rain_24h), 2) : round($packetWeather->rain_24h, 2);
    else
      $data[] = '';

    if ($packetWeather->rain_since_midnight != null)
      $data[] = isImperialUnitUser() ? round(convertMmToInch($packetWeather->rain_since_midnight), 2) : round($packetWeather->rain_since_midnight, 2);
    else
      $data[] = '';

    $data[] = round($packetWeather->luminosity, 0);
    $data[] = isImperialUnitUser() ? round(convertMmToInch($packetWeather->snow), 2) : round($packetWeather->snow, 2);
    $response['data']['readings'][] = $data;
  }
}


function getLatestTelemetry($station, &$response)
{
  $telemetryPackets = PacketTelemetryRepository::getInstance()->getLatestObjectListByStationId($station->id, 1, 0, 7);
  if ($telemetryPackets != null || sizeof($telemetryPackets) == 1)
  {
    for ($x = 1; $x <= 5; ++$x)
    {
      $converted = universalDataUnitConvert(round($telemetryPackets[0]->getValue($x), 2), $telemetryPackets[0]->getValueUnit($x));
      $response['data']['values'][$x] = array('name' => $telemetryPackets[0]->getValueParameterName($x), 'value' => $converted['value'] . ' ' . $converted['unit']);
    }

    if ($telemetryPackets[0]->bits !== null)
    {
      for ($x = 1; $x <= 8; ++$x)
      {
        $response['data']['bits'][$x] = array('name' => $telemetryPackets[0]->getBitParameterName($x), 'value' => $telemetryPackets[0]->getBitLabel($x));
      }
    }
  }
  else
  {
    $response['status'] = 'error';
    $response['message'] = 'No telemetry data found.';
  }
}


function getTelemetryValues($station, &$response)
{
  $maxDays = 100;
  $start = $_GET['start'] ?? time()-864000;
  $end = $_GET['end'] ?? time();

  $rows = 5000;
  $offset = 0;

  $start_time = microtime(true);

  $telemetryPackets = PacketTelemetryRepository::getInstance()->getLatestObjectListByStationId($station->id, $rows, $offset, $maxDays, 'desc', $start, $end);
  $latestPacketTelemetry = (count($telemetryPackets) > 0 ? $telemetryPackets[0] : new PacketTelemetry(null));

  $response['data']['dbtime'] = round(microtime(true) - $start_time, 4);
  $response['status'] = 'success';
  $response['message'] = '';

  // Field labels
  for ($x = 1; $x <= 5; $x++)
  {
    $response['data']['labels'][$x] = $telemetryPackets[0]->getValueParameterName($x);
  }

  foreach ($telemetryPackets as $packetTelemetry)
  {
    $data = array('ts' => $packetTelemetry->wxRawTimestamp != null ? $packetTelemetry->wxRawTimestamp*1000 : $packetTelemetry->timestamp*1000);
    for ($x = 1; $x <= 5; $x++)
    {
      $converted = universalDataUnitConvert(round($packetTelemetry->getValue($x), 2), $packetTelemetry->getValueUnit($x));
      if ($packetTelemetry->{"val$x"}  !== null)
        $data[$x] = $converted['value'];
      else
        $data[$x] = '-';
    }

    $response['data']['values'][] = $data;
  }
}


function getTelemetryBits($station, &$response)
{
  $maxDays = 10;
  $start = $_GET['start'] ?? time()-864000;
  $end = $_GET['end'] ?? time();

  $rows = 2000;
  $offset = 0;

  $start_time = microtime(true);

  $telemetryPackets = PacketTelemetryRepository::getInstance()->getLatestObjectListByStationId($station->id, $rows, $offset, $maxDays, 'desc', $start, $end);
  $latestPacketTelemetry = (count($telemetryPackets) > 0 ? $telemetryPackets[0] : new PacketTelemetry(null));

  $response['data']['dbtime'] = round(microtime(true) - $start_time, 4);
  $response['status'] = 'success';
  $response['message'] = '';

  // Field labels
  for ($x = 1; $x <= 8; $x++)
  {
    $response['data']['labels'][$x] = $telemetryPackets[0]->getBitParameterName($x);
  }

  foreach ($telemetryPackets as $packetTelemetry)
  {
    if ($packetTelemetry->bits !== null)
    {
      $data = array('ts' => $packetTelemetry->wxRawTimestamp != null ? $packetTelemetry->wxRawTimestamp*1000 : $packetTelemetry->timestamp*1000);
      for ($x = 1; $x <= 8; $x++)
      {
        $data[$x] = $packetTelemetry->getBitLabel($x);
      }
      $response['data']['bits'][] = $data;
    }
  }
}


header('Content-type: application/json');
echo json_encode($response);
