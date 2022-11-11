<?php

class PacketWeatherRepository extends ModelRepository
{

    private static $_singletonInstance = null;

    public function __construct()
    {
        parent::__construct('PacketWeather');
    }

    /**
     * Returnes an initiated PacketWeatherRepository
     *
     * @return PacketWeatherRepository
     */
    public static function getInstance()
    {
        if (self::$_singletonInstance === null) {
            self::$_singletonInstance = new PacketWeatherRepository();
        }

        return self::$_singletonInstance;
    }

    /**
     * Get object by id
     *
     * @param  int $id
     * @param  int $timestamp
     * @return PacketWeather
     */
    public function getObjectById($id, $timestamp)
    {
        if (!isInt($id) || !isInt($timestamp)) {
            return new PacketWeather(0);
        }
        return $this->getObjectFromSql('select * from packet_weather where id = ? and timestamp = ?', [$id, $timestamp]);
    }

    /**
     * Get object by packet id
     *
     * @param  int $id
     * @param  int $timestamp
     * @return PacketWeather
     */
    public function getObjectByPacketId($id, $timestamp)
    {
        if (!isInt($id) || !isInt($timestamp)) {
            return new PacketWeather(0);
        }
        return $this->getObjectFromSql('select * from packet_weather where packet_id = ? and timestamp = ?', [$id, $timestamp]);
    }

    /**
     * Get latest object list by station id (useful for creating a chart)
     *
     * @param  int   $stationId
     * @param  int   $endTimestamp
     * @param  int   $hours
     * @param  array $columns
     * @return array
     */
    public function getLatestDataListByStationId($stationId, $endTimestamp, $hours, $columns)
    {
        if (!isInt($stationId) || !isInt($endTimestamp) || !isInt($hours)) {
            return [];
        }
        $minTimestamp = $endTimestamp - (60*60*$hours);

        $sql = 'select ' . implode(',', $columns) . ' from packet_weather
            where station_id = ?
                and timestamp >= ?
                and timestamp <= ?
            order by timestamp';
        $arg = [$stationId, $minTimestamp, $endTimestamp];

        $pdo = PDOConnection::getInstance();
        $stmt = $pdo->prepareAndExec($sql, $arg);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get latest object list by station id
     *
     * @param  int $stationId
     * @param  int $limit
     * @param  int $offset
     * @param  int $maxDays
     * @return array
     */
    public function getLatestObjectListByStationIdAndLimit($stationId, $limit, $offset, $maxDays = 7, $startAt=null, $endAt=null)
    {
        if (!isInt($stationId) || !isInt($limit) || !isInt($offset) || !isInt($maxDays)) {
            return [];
        }
        $startTime = $startAt ?? (time() - 24*60*60*$maxDays);
        $endTime = $endAt ?? time();
        return $this->getObjectListFromSql(
            'select * from packet_weather
            where station_id = ?
                and timestamp > ?
                and timestamp < ?
                and (humidity is not null
                    or pressure is not null
                    or rain_1h is not null
                    or rain_24h is not null
                    or rain_since_midnight is not null
                    or temperature is not null
                    or wind_direction is not null
                    or wind_gust is not null
                    or wind_speed is not null
                    or luminosity is not null
                    or snow is not null)
            order by timestamp ' . ($startAt == null ? 'desc' : 'asc') . ' limit ? offset ?', [$stationId, $startTime, $endTime, $limit, $offset]
        );
    }

    /**
     * Get latest number of packets by station id
     *
     * @param  int $stationId
     * @param  int $maxDays
     * @return int
     */
    public function getLatestNumberOfPacketsByStationIdAndLimit($stationId, $maxDays = 7, $startAt=null, $endAt=null)
    {
        if (!isInt($stationId) || !isInt($maxDays)) {
            return 0;
        }
        $startTime = $startAt ?? (time() - 24*60*60*$maxDays);
        $endTime = $endAt ?? time();
        $sql = 'select count(*) c from packet_weather
            where station_id = ?
                and timestamp > ?
                and timestamp < ?
                and (humidity is not null
                    or pressure is not null
                    or rain_1h is not null
                    or rain_24h is not null
                    or rain_since_midnight is not null
                    or temperature is not null
                    or wind_direction is not null
                    or wind_gust is not null
                    or wind_speed is not null
                    or luminosity is not null
                    or snow is not null)';
        $parameters = [$stationId, $startTime, $endTime];

        $pdo = PDOConnection::getInstance();
        $stmt = $pdo->prepareAndExec($sql, $parameters);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sum = 0;
        foreach($rows as $row) {
            $sum += $row['c'];
        }

        return $sum;
    }

    /**
     * Get weather almanac by station id for a particular period of time
     *
     * @param  int $stationId
     * @param  int $startTime
     * @return array
     */
    public function getAlmanac($stationId, $startTime)
    {
      if (!isInt($stationId) || !isInt($startTime)) {
          return null;
      }

      $sql = 'select
                max(temperature) as high_temperature, min(temperature) as low_temperature, avg(temperature) as average_temperature,
                max(rain_since_midnight) as rainfall,
                max(pressure) as high_pressure, min(pressure) as low_pressure,
                max(wind_speed) as wind_speed, max(wind_gust) as wind_gust
              from packet_weather
          where station_id = ?
              and timestamp > ?
              and timestamp < ?
              and (humidity is not null
                  or pressure is not null
                  or rain_1h is not null
                  or rain_24h is not null
                  or rain_since_midnight is not null
                  or temperature is not null
                  or wind_direction is not null
                  or wind_gust is not null
                  or wind_speed is not null
                  or luminosity is not null
                  or snow is not null)';

      $endTime = $startTime + 86400;
      $parameters = [$stationId, $startTime, $endTime];

      $pdo = PDOConnection::getInstance();
      $stmt = $pdo->prepareAndExec($sql, $parameters);
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
