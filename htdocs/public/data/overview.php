<?php

require dirname(__DIR__) . "../../includes/bootstrap.php";

$response = [];
$station = StationRepository::getInstance()->getObjectById($_GET['id'] ?? null);
if ($station->isExistingObject()) {
    $type = $_GET['type'] ?? '';

    if ($type == 'pf') {
      $packetFrequencyNumberOfPackets = null;
      $response['packet_frequency'] = $station->getPacketFrequency(null, $packetFrequencyNumberOfPackets);
      $response['total_packets'] = $station->getTotalPackets();
      $response['packet_frequency_count'] = $packetFrequencyNumberOfPackets;
    }
}

header('Content-type: application/json');
echo json_encode($response);
