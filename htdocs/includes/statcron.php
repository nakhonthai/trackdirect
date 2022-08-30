<?php

require dirname(__FILE__) . "/bootstrap.php";

$response = [];

// Database
$pdo = PDOConnection::getInstance();

// response
$response['stat_timestamp'] = time();

// Total stations
$sql = "SELECT SUM(n_live_tup) FROM pg_stat_all_tables WHERE relname = 'station';";
$stmt = $pdo->prepareAndExec($sql);
$response['system_stations'] = intval($stmt->fetchColumn());

// Total packets
$sql = "SELECT SUM(n_live_tup) FROM pg_stat_all_tables WHERE relname LIKE 'packet20______';";
$stmt = $pdo->prepareAndExec($sql);
$response['system_packets'] = intval($stmt->fetchColumn());

// Total OGN packets
$sql = "SELECT SUM(n_live_tup) FROM pg_stat_all_tables WHERE relname LIKE 'packet20_______ogn'";
$stmt = $pdo->prepareAndExec($sql);
$response['system_ognpackets'] = intval($stmt->fetchColumn());

// Total PATH packets
$sql = "SELECT SUM(n_live_tup) FROM pg_stat_all_tables WHERE relname LIKE 'packet20_______path'";
$stmt = $pdo->prepareAndExec($sql);
$response['system_pathpackets'] = intval($stmt->fetchColumn());

// Total TELEMETRY packets
$sql = "SELECT SUM(n_live_tup) FROM pg_stat_all_tables WHERE relname LIKE 'packet20_______telemetry'";
$stmt = $pdo->prepareAndExec($sql);
$response['system_telemetrypackets'] = intval($stmt->fetchColumn());

// Total WEATHER packets
$sql = "SELECT SUM(n_live_tup) FROM pg_stat_all_tables WHERE relname LIKE 'packet20_______weather'";
$stmt = $pdo->prepareAndExec($sql);
$response['system_weatherpackets'] = intval($stmt->fetchColumn());



// Total Telemetry Bits packets
$sql = "SELECT n_live_tup FROM pg_stat_all_tables WHERE relname = 'station_telemetry_bits';";
$stmt = $pdo->prepareAndExec($sql);
$response['system_telemetry_bits'] = intval($stmt->fetchColumn());

// Total Telemetry EQNS packets
$sql = "SELECT n_live_tup FROM pg_stat_all_tables WHERE relname = 'station_telemetry_eqns';";
$stmt = $pdo->prepareAndExec($sql);
$response['system_telemetry_eqns'] = intval($stmt->fetchColumn());

// Total Telemetry PARAM packets
$sql = "SELECT n_live_tup FROM pg_stat_all_tables WHERE relname = 'station_telemetry_param';";
$stmt = $pdo->prepareAndExec($sql);
$response['system_telemetry_param'] = intval($stmt->fetchColumn());

// Total Telemetry UNIT packets
$sql = "SELECT n_live_tup FROM pg_stat_all_tables WHERE relname = 'station_telemetry_unit';";
$stmt = $pdo->prepareAndExec($sql);
$response['system_telemetry_unit'] = intval($stmt->fetchColumn());

// Total Telemetry UNIT packets
$sql = "SELECT n_live_tup FROM pg_stat_all_tables WHERE relname = 'sender';";
$stmt = $pdo->prepareAndExec($sql);
$response['system_senders'] = intval($stmt->fetchColumn());


// Save the entry into the statistics table
$sql = "INSERT INTO statistics (stat_timestamp, stations, packet, ogn, path, telemetry, weather, telemetry_bits, telemetry_eqns, telemetry_param, telemetry_unit, senders) VALUES (:stat_timestamp, :system_stations, :system_packets, :system_ognpackets, :system_pathpackets, :system_telemetrypackets, :system_weatherpackets, :system_telemetry_bits, :system_telemetry_eqns, :system_telemetry_param, :system_telemetry_unit, :system_senders);";
$stmt = $pdo->prepareAndExec($sql, $response);
