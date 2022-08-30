<?php

require dirname(__DIR__) . "../../includes/bootstrap.php";

$response = [];

// Database
$pdo = PDOConnection::getInstance();

/* SITE STATS */

// Total stations
$sql = "SELECT SUM(n_live_tup)  FROM pg_stat_all_tables WHERE relname = 'station';";
$stmt = $pdo->prepareAndExec($sql);
$response['system_stations'] = number_format(intval($stmt->fetchColumn()));

// Total packets
$sql = "SELECT SUM(n_live_tup)  FROM pg_stat_all_tables WHERE relname LIKE 'packet20______';";
$stmt = $pdo->prepareAndExec($sql);
$response['system_packets'] = number_format(intval($stmt->fetchColumn()));

// Total OGN packets
$sql = "SELECT SUM(n_live_tup)  FROM pg_stat_all_tables WHERE relname LIKE 'packet20_______ogn'";
$stmt = $pdo->prepareAndExec($sql);
$response['system_ognpackets'] = number_format(intval($stmt->fetchColumn()));

// Total PATH packets
$sql = "SELECT SUM(n_live_tup)  FROM pg_stat_all_tables WHERE relname LIKE 'packet20_______path'";
$stmt = $pdo->prepareAndExec($sql);
$response['system_pathpackets'] = number_format(intval($stmt->fetchColumn()));

// Total TELEMETRY packets
$sql = "SELECT SUM(n_live_tup)  FROM pg_stat_all_tables WHERE relname LIKE 'packet20_______telemetry'";
$stmt = $pdo->prepareAndExec($sql);
$response['system_telemetrypackets'] = number_format(intval($stmt->fetchColumn()));

// Total WEATHER packets
$sql = "SELECT SUM(n_live_tup)  FROM pg_stat_all_tables WHERE relname LIKE 'packet20_______weather'";
$stmt = $pdo->prepareAndExec($sql);
$response['system_weatherpackets'] = number_format(intval($stmt->fetchColumn()));



// Total Telemetry Bits packets
$sql = "SELECT n_live_tup FROM pg_stat_all_tables WHERE relname = 'station_telemetry_bits';";
$stmt = $pdo->prepareAndExec($sql);
$response['system_telemetry_bits'] = number_format(intval($stmt->fetchColumn()));

// Total Telemetry EQNS packets
$sql = "SELECT n_live_tup FROM pg_stat_all_tables WHERE relname = 'station_telemetry_eqns';";
$stmt = $pdo->prepareAndExec($sql);
$response['system_telemetry_eqns'] = number_format(intval($stmt->fetchColumn()));

// Total Telemetry PARAM packets
$sql = "SELECT n_live_tup FROM pg_stat_all_tables WHERE relname = 'station_telemetry_param';";
$stmt = $pdo->prepareAndExec($sql);
$response['system_telemetry_param'] = number_format(intval($stmt->fetchColumn()));

// Total Telemetry UNIT packets
$sql = "SELECT n_live_tup FROM pg_stat_all_tables WHERE relname = 'station_telemetry_unit';";
$stmt = $pdo->prepareAndExec($sql);
$response['system_telemetry_unit'] = number_format(intval($stmt->fetchColumn()));

// Total Telemetry UNIT packets
$sql = "SELECT n_live_tup FROM pg_stat_all_tables WHERE relname = 'sender';";
$stmt = $pdo->prepareAndExec($sql);
$response['system_senders'] = number_format(intval($stmt->fetchColumn()));




/* TODAY STATS */
// Get todays tabke
$tabletoday = gmdate('Ymd');

// Total packets
$sql = "SELECT n_live_tup  FROM pg_stat_all_tables WHERE relname = 'packet${tabletoday}';";
$stmt = $pdo->prepareAndExec($sql);
$response['today_packets'] = number_format(intval($stmt->fetchColumn()));

// Total OGN packets
$sql = "SELECT n_live_tup  FROM pg_stat_all_tables WHERE relname = 'packet${tabletoday}_ogn'";
$stmt = $pdo->prepareAndExec($sql);
$response['today_ognpackets'] = number_format(intval($stmt->fetchColumn()));

// Total PATH packets
$sql = "SELECT n_live_tup  FROM pg_stat_all_tables WHERE relname = 'packet${tabletoday}_path'";
$stmt = $pdo->prepareAndExec($sql);
$response['today_pathpackets'] = number_format(intval($stmt->fetchColumn()));

// Total TELEMETRY packets
$sql = "SELECT n_live_tup  FROM pg_stat_all_tables WHERE relname = 'packet${tabletoday}_telemetry'";
$stmt = $pdo->prepareAndExec($sql);
$response['today_telemetrypackets'] = number_format(intval($stmt->fetchColumn()));

// Total WEATHER packets
$sql = "SELECT n_live_tup  FROM pg_stat_all_tables WHERE relname = 'packet${tabletoday}_weather'";
$stmt = $pdo->prepareAndExec($sql);
$response['today_weatherpackets'] = $response['system_weatherpackets'] = number_format(intval($stmt->fetchColumn()));


header('Content-type: application/json');
echo json_encode($response);
