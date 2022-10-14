<?php require dirname(__DIR__) . "../../includes/bootstrap.php"; ?>

<?php $station = StationRepository::getInstance()->getObjectById($_GET['id'] ?? null); ?>
<?php if ($station->isExistingObject()) : ?>
<?php
  $page = $_GET['page'] ?? 1;
  $rows = $_GET['rows'] ?? 25;
  $offset = ($page - 1) * $rows;

  $start = $_GET['start'] ?? time()-86400;
  $end = $_GET['end'] ?? time();

  $start_time = microtime(true);
  if (($_GET['category'] ?? 1) == 2) {
      $packets = PacketRepository::getInstance()->getObjectListWithRawBySenderStationId($station->id, $rows, $offset, $start, $end);
      $count = PacketRepository::getInstance()->getNumberOfPacketsWithRawBySenderStationId($station->id, $start, $end);
  } else {
      $packets = PacketRepository::getInstance()->getObjectListWithRawByStationId($station->id, $rows, $offset, $start, $end);
      $count = PacketRepository::getInstance()->getNumberOfPacketsWithRawByStationId($station->id, $start, $end);
  }
  $dbtime = microtime(true) - $start_time;

  $pages = ceil($count / $rows);
?>

    <title><?php echo $station->name; ?> Raw Packets</title>
    <div class="modal-inner-content">
        <div class="modal-inner-content-menu">
            <a class="tdlink" title="Overview" href="/views/overview.php?id=<?php echo $station->id ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ?? 0; ?>">Overview</a>
            <a class="tdlink" title="Statistics" href="/views/statistics.php?id=<?php echo $station->id ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ?? 0; ?>">Statistics</a>
            <a class="tdlink" title="Trail Chart" href="/views/trail.php?id=<?php echo $station->id ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ?? 0; ?>">Trail Chart</a>
            <a class="tdlink" title="Weather" href="/views/weather.php?id=<?php echo $station->id ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ?? 0; ?>">Weather</a>
            <a class="tdlink" title="Telemetry" href="/views/telemetry.php?id=<?php echo $station->id ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ?? 0; ?>">Telemetry</a>
            <span>Raw Packets</span>
            <a class="tdlink" title="Live Feed" href="/views/live.php?id=<?php echo $station->id ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ?? 0; ?>">Live Feed</a>
            <a class="tdlink" title="Messages &amp; Bulletins" href="/views/messages.php?id=<?php echo $station->id ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ?? 0; ?>">Messages &amp; Bulletins</a>
        </div>

        <div class="horizontal-line">&nbsp;</div>

        <p>
          <?php if ($count): ?>A total of <?php echo $count ?> recevied packets have been found for station/object <b><?php echo $station->name; ?></b> <?php if ($start < (time()-86400)): ?>between <span class="packetts" style="font-weight:bold;"><?php echo $start;?></span> and <span class="packetts" style="font-weight:bold;"><?php echo $end; ?></span><?php else: ?>from the past 24 hours<?php endif;?>.
          <?php else: ?>If no packets are shown the station/object has not sent any packets within the past 24 hours.<?php endif; ?>
          This took <?php echo round($dbtime, 3) ?> seconds to find in our database.
        </p>

        <?php if ($station->sourceId == 5) : ?>
            <p>
                We do not save raw packets for aircrafts that do not exists in the <a target="_blank" href="http://wiki.glidernet.org/ddb">OGN Devices DataBase</a>. We will only display information that can be used to identify an aircraft if the aircraft device details exists in the OGN Devices DataBase, and if the setting "I don't want this device to be identified" is deactivated.
            </p>
        <?php else : ?>
            <p>
                If you compare the raw packets with similar data from other websites it may differ (especially the path), the reason is that we are not collecting packets from the same APRS-IS servers. Each APRS-IS server performes duplicate filtering, and which packet that is considered to be a duplicate may differ depending on which APRS-IS server you receive your data from.
            </p>
        <?php endif; ?>



        <div style="clear:both;"></div>

        <div id="titlebar">
          <form id="raw-form" style="float:right;line-height: 28px">
            Show
            <select id="raw-rows" sclass="pagination-rows">
                <option <?php echo ($rows == 25 ? 'selected' : ''); ?> value="25">25</option>
                <option <?php echo ($rows == 50 ? 'selected' : ''); ?> value="50">50</option>
                <option <?php echo ($rows == 100 ? 'selected' : ''); ?> value="100">100</option>
                <option <?php echo ($rows == 200 ? 'selected' : ''); ?> value="200">200</option>
                <option <?php echo ($rows == 300 ? 'selected' : ''); ?> value="300">300</option>
            </select>
              rows of
              <select id="raw-type">
                  <option <?php echo (($_GET['type'] ?? 1) == 1 ? 'selected' : ''); ?> value="1">Raw</option>
                  <option <?php echo (($_GET['type'] ?? 1) == 2 ? 'selected' : ''); ?> value="2">Decoded</option>
              </select>
              packets
              <?php if ($station->stationTypeId == 1) : ?>
                  <select id="raw-category">
                      <option <?php echo (($_GET['category'] ?? 1) == 1 ? 'selected' : ''); ?> value="1">regarding <?php echo $station->name; ?></option>
                      <option <?php echo (($_GET['category'] ?? 1) == 2 ? 'selected' : ''); ?> value="2">sent by <?php echo $station->name; ?></option>
                  </select>
              <?php endif; ?>
              <?php if (intval(getDatabaseConfig('days_to_save_packet_data')) > 1): ?>
                from <input type="text" id="start-date" class="form-control" style="height:.5em;width:8.5em" readonly />
                to <input type="text" id="end-date" class="form-control" style="height:.5em;width:8.5em" readonly />
              <input type="submit" value="Go" style="line-height:0px;height:16px;width:3em;padding: 12px 0px;" />
            <?php endif; ?>
          </form>
          <script>
            $("#raw-form").submit(function(e) {
              var timecut = '';
            <?php if (intval(getDatabaseConfig('days_to_save_packet_data')) > 1): ?>
              if ($('#start-date').val() != "") {
                var startat = moment($('#start-date').val(), 'YYYY-MM-DD HH:mm').unix();
                var endat = moment($('#end-date').val(), 'YYYY-MM-DD HH:mm').endOf('day').unix();
                timecut = '&start='+startat+'&end='+endat;
              }
            <?php endif; ?>
              loadView('raw.php?id=<?php echo $_GET['id'] ;?>&category='+$('#raw-category').val()+'&type='+$('#raw-type').val()+'&rows='+$('#raw-rows').val()+'&imperialUnits=<?php echo $_GET['imperialUnits'] ;?>'+timecut);
              e.preventDefault();
              return false;
            });
            $(document).ready(function(){
            <?php if (intval(getDatabaseConfig('days_to_save_packet_data')) > 1): ?>
              $("#start-date, #end-date").datepicker({
                  showOtherMonths: true,
                  selectOtherMonths: true,
                  minDate: -(<?php echo getDatabaseConfig('days_to_save_packet_data') ?>),
                  maxDate: '0',
                  dateFormat: 'yy-mm-dd',
                  showButtonPanel: true,
                  onSelect: function(selectedDate, dpObj) {
                    if (dpObj.id == 'start-date') {
                      $("#end-date").datepicker("option", "minDate", selectedDate);
                      if ($('#end-date').val() == "") $('#end-date').val($("#start-date").val());
                    }
                    else if (dpObj.id == 'end-date') {
                      $("#start-date").datepicker("option", "maxDate", selectedDate);
                      if ($('#start-date').val() == "") $('#start-date').val($("#end-date").val());
                    }
                  }
              });
              <?php if ($start < (time()-86400)): ?>
                $("#start-date").datepicker('setDate', new Date(1000 * <?php echo $start; ?>));
                $("#end-date").datepicker('setDate', new Date(1000 * <?php echo $end; ?>));
              <?php endif; ?>
            <?php endif; ?>
            });
          </script>

          <div style="float:left;line-height: 28px">
                <span style="float:left;">Displaying <?php echo $offset+1; ?> - <?php echo ($offset+$rows < $count ? $offset+$rows : $count); ?> of <?php echo $count ?> packets.
          </div>
          <?php if ($pages > 1): ?>
              <form style="float:left;line-height: 28px;padding-left:30px;">
                <?php if ($page > 1): ?><a class="tdlink" href="/views/raw.php?id=<?php echo $station->id; ?>&category=<?php echo ($_GET['category'] ?? 1) ?>&type=<?php echo ($_GET['type'] ?? 1); ?>&start=<?php echo $start; ?>&end=<?php echo $end; ?>&rows=<?php echo $rows; ?>&page=<?php echo $page-1;?>"><b>&lt;</b></a><?php endif; ?>
                Page <select id="raw-page">
                <?php for($i = 1; $i <= $pages; $i++): ?>
                  <option value="<?php echo $i; ?>" <?php if ($page == $i) echo ' selected="selected"'; ?>><?php echo $i; ?></option>
                <?php endfor; ?>
                </select>
                <?php if ($page < $pages): ?><a class="tdlink" href="/views/raw.php?id=<?php echo $station->id; ?>&category=<?php echo ($_GET['category'] ?? 1) ?>&type=<?php echo ($_GET['type'] ?? 1); ?>&start=<?php echo $start; ?>&end=<?php echo $end; ?>&rows=<?php echo $rows; ?>&page=<?php echo $page + 1; ?>"><b>&gt;</b></a><?php endif; ?>
              </form>
              <script>
                $("#raw-page").change(function() {
                  loadView('raw.php?id=<?php echo $_GET['id'] ;?>&category=<?php echo ($_GET['category'] ?? 1) ?>&type=<?php echo ($_GET['type'] ?? 1); ?>&rows=<?php echo $rows; ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ;?>&start=<?php echo $start; ?>&end=<?php echo $end; ?>&page='+$("#raw-page").val());
                });
                </script>
          <?php endif; ?>

          <div style="clear:both;"></div>
        </div>

        <div id="raw-content-output">
            <?php foreach (array_slice($packets, 0, $rows) as $packet) : ?>
                <?php if (($_GET['type'] ?? 1) == 1) : ?>
                    <p>
                        <span class="raw-packet-timestamp"><?php echo $packet->timestamp; ?></span>:

                        <?php if (in_array($packet->mapId, Array(3, 6))) : ?>
                        <span class="raw-packet-error parsepkt">
                        <?php else : ?>
                        <span class="parsepkt">
                        <?php endif; ?>
                            <?php echo str_replace_first(htmlspecialchars($station->name . '>'), htmlspecialchars($station->name) . '&gt;', htmlspecialchars($packet->raw)); ?>
                            <?php if ($packet->mapId == 3) : ?>
                            &nbsp;<b>[Duplicate]</b>
                            <?php elseif ($packet->mapId == 6) : ?>
                            &nbsp;<b>[Received in wrong order]</b>
                            <?php endif; ?>

                        </span>
                    </p>
                <?php elseif (($_GET['type'] ?? 1) == 2) : ?>
                    <div class="decoded">
                        <div class="datagrid">
                            <table>
                                <thead>
                                    <tr>
                                        <th colspan="2">
                                            <?php if (in_array($packet->mapId, Array(3, 6))) : ?>
                                            <span class="raw-packet-error">
                                            <?php else : ?>
                                            <span>
                                            <?php endif; ?>
                                                <span class="raw-packet-timestamp"><?php echo $packet->timestamp; ?></span>

                                                <?php if ($packet->mapId == 3) : ?>
                                                &nbsp;<b>[Duplicate]</b>
                                                <?php elseif ($packet->mapId == 6) : ?>
                                                &nbsp;<b>[Received in wrong order]</b>
                                                <?php endif; ?>
                                            </span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Raw</td>
                                        <td class="parsepkt">
                                            <?php echo str_replace_first(htmlspecialchars($station->name . '>'), htmlspecialchars($station->name) . '&gt;', htmlspecialchars($packet->raw)); ?>
                                        </td>
                                    </tr>

                                    <tr><td>Packet type</td><td><?php echo $packet->getPacketTypeName(); ?></td></tr>

                                    <?php if ($packet->getStationObject()->stationTypeId == 2) : ?>
                                        <tr><td>Object/Item name</td><td><?php echo htmlspecialchars($packet->getStationObject()->name); ?></td></tr>
                                    <?php else : ?>
                                        <tr><td>Callsign</td><td><?php echo htmlspecialchars($packet->getStationObject()->name); ?></td></tr>
                                    <?php endif; ?>

                                    <?php if ($packet->getStationObject()->name != $packet->getSenderObject()->name) : ?>
                                        <tr><td>Sender</td><td><?php echo htmlspecialchars($packet->getSenderObject()->name); ?></td></tr>
                                    <?php endif; ?>

                                    <tr><td>Path</td><td><?php echo htmlspecialchars($packet->rawPath); ?></td></tr>

                                    <?php if ($packet->reportedTimestamp != null) : ?>
                                        <tr><td>Reported time</td><td><?php echo $packet->reportedTimestamp; ?> - <span class="raw-packet-timestamp"><?php echo $packet->reportedTimestamp; ?></span></td></tr>
                                    <?php endif; ?>

                                    <?php if ($packet->latitude != null && $packet->longitude != null) : ?>
                                        <tr><td>Latitude</td><td><?php echo round($packet->latitude, 5); ?></td></tr>
                                        <tr><td>Longitude</td><td><?php echo round($packet->longitude, 5); ?></td></tr>
                                    <?php endif; ?>

                                    <?php if ($packet->symbol != null && $packet->symbolTable != null) : ?>
                                        <tr><td>Symbol</td><td><?php echo htmlspecialchars($packet->symbol); ?></td></tr>
                                        <tr><td>Symbol table</td><td><?php echo htmlspecialchars($packet->symbolTable); ?></td></tr>
                                    <?php endif; ?>

                                    <?php if ($packet->speed != null) : ?>
                                        <?php if (isImperialUnitUser()) : ?>
                                            <tr><td>Speed</td><td><?php echo convertKilometerToMile($packet->speed); ?> mph</td></tr>
                                        <?php else : ?>
                                            <tr><td>Speed</td><td><?php echo $packet->speed; ?> km/h</td></tr>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php if ($packet->course != null) : ?>
                                        <tr><td>Course</td><td><?php echo $packet->course; ?>°</td></tr>
                                    <?php endif; ?>

                                    <?php if ($packet->altitude != null) : ?>
                                        <?php if (isImperialUnitUser()) : ?>
                                            <tr><td>Altitude</td><td><?php echo convertMeterToFeet($packet->altitude); ?> ft</td></tr>
                                        <?php else : ?>
                                            <tr><td>Altitude</td><td><?php echo $packet->altitude; ?> m</td></tr>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php if ($packet->comment != null) : ?>
                                        <?php if ($packet->packetTypeId == 10) : ?>
                                            <tr><td>Status</td><td><?php echo htmlspecialchars($packet->comment); ?></td></tr>
                                        <?php elseif ($packet->packetTypeId == 7) : ?>
                                            <tr><td>Beacon</td><td><?php echo htmlspecialchars($packet->comment); ?></td></tr>
                                        <?php else : ?>
                                            <tr><td>Comment</td><td><?php echo htmlspecialchars($packet->comment); ?></td></tr>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php if ($packet->posambiguity == 1) : ?>
                                        <tr><td>Posambiguity</td><td>Yes</td></tr>
                                    <?php endif; ?>

                                    <?php if ($packet->phg != null) : ?>
                                        <?php if (isImperialUnitUser()) : ?>
                                            <tr><td>PHG</td><td><?php echo $packet->phg; ?> (Calculated range: <?php echo round(convertKilometerToMile($packet->getPHGRange()/1000),2); ?> miles)</td></tr>
                                        <?php else : ?>
                                            <tr><td>PHG</td><td><?php echo $packet->phg; ?> (Calculated range: <?php echo round($packet->getPHGRange()/1000,2); ?> km)</td></tr>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php if ($packet->rng != null) : ?>
                                        <tr><td>RNG</td><td><?php echo $packet->rng; ?></td></tr>
                                    <?php endif; ?>

                                    <?php if ($station->latestWeatherPacketTimestamp !== null) : ?>
                                        <?php $weather = $packet->getPacketWeather(); ?>
                                        <?php if ($weather->isExistingObject()) : ?>
                                            <tr>
                                                <td>Weather</td>
                                                <td>
                                                    <table>
                                                        <tbody>
                                                            <?php if ($weather->wxRawTimestamp !== null) : ?>
                                                                <tr>
                                                                    <td>Time:</td><td><span class="raw-packet-timestamp"><?php echo $weather->wxRawTimestamp; ?></span></td>
                                                                </tr>
                                                            <?php endif; ?>

                                                            <?php if ($weather->temperature !== null) : ?>
                                                                <tr>
                                                                    <td>Temperature:</td>
                                                                    <?php if (isImperialUnitUser()) : ?>
                                                                        <td><?php echo round(convertCelciusToFahrenheit($weather->temperature), 2); ?>&deg; F</td>
                                                                    <?php else : ?>
                                                                        <td><?php echo round($weather->temperature, 2); ?>&deg; C</td>
                                                                    <?php endif; ?>
                                                                </tr>
                                                            <?php endif; ?>

                                                            <?php if ($weather->humidity !== null) : ?>
                                                                <tr>
                                                                    <td>Humidity:</td>
                                                                    <td><?php echo $weather->humidity; ?>%</td>
                                                                </tr>
                                                            <?php endif; ?>

                                                            <?php if ($weather->pressure !== null) : ?>
                                                                <tr>
                                                                    <td>Pressure:</td>
                                                                    <?php if (isImperialUnitUser()) : ?>
                                                                        <td><?php echo round(convertMbarToMmhg($weather->pressure),1); ?> mmHg</td>
                                                                    <?php else : ?>
                                                                        <td><?php echo round($weather->pressure,1); ?> hPa</td>
                                                                    <?php endif; ?>
                                                                </tr>
                                                            <?php endif; ?>

                                                            <?php if ($weather->rain_1h !== null) : ?>
                                                                <tr>
                                                                    <td>Rain latest hour:</td>
                                                                    <?php if (isImperialUnitUser()) : ?>
                                                                        <td><?php echo round(convertMmToInch($weather->rain_1h),2); ?> in</td>
                                                                    <?php else : ?>
                                                                        <td><?php echo round($weather->rain_1h,2); ?> mm</td>
                                                                    <?php endif; ?>
                                                                </tr>
                                                            <?php endif; ?>

                                                            <?php if ($weather->rain_24h !== null) : ?>
                                                                <tr>
                                                                    <td>Rain latest 24h hours:</td>
                                                                    <?php if (isImperialUnitUser()) : ?>
                                                                        <td><?php echo round(convertMmToInch($weather->rain_24h),2); ?> in</td>
                                                                    <?php else : ?>
                                                                        <td><?php echo round($weather->rain_24h,2); ?> mm</td>
                                                                    <?php endif; ?>
                                                                </tr>
                                                            <?php endif; ?>

                                                            <?php if ($weather->rain_since_midnight !== null) : ?>
                                                                <tr>
                                                                    <td>Rain since midnight:</td>
                                                                    <?php if (isImperialUnitUser()) : ?>
                                                                        <td><?php echo round(convertMmToInch($weather->rain_since_midnight),2); ?> in</td>
                                                                    <?php else : ?>
                                                                        <td><?php echo round($weather->rain_since_midnight,2); ?> mm</td>
                                                                    <?php endif; ?>
                                                                </tr>
                                                            <?php endif; ?>

                                                            <?php if (isImperialUnitUser()) : ?>
                                                                <?php if ($weather->wind_speed !== null && $weather->wind_speed > 0) : ?>
                                                                    <tr>
                                                                        <td>Wind Speed:</td>
                                                                        <td><?php echo round(convertMpsToMph($weather->wind_speed), 2); ?> mph, <?php echo $weather->wind_direction; ?>&deg;</td>
                                                                    </tr>
                                                                <?php elseif($weather->wind_speed !== null) : ?>
                                                                    <tr>
                                                                        <td>Wind Speed:</td>
                                                                        <td><?php echo round(convertMpsToMph($weather->wind_speed), 2); ?> mph</td>
                                                                    </tr>
                                                                <?php endif; ?>

                                                            <?php else : ?>
                                                                <?php if ($weather->wind_speed !== null && $weather->wind_speed > 0) : ?>
                                                                    <tr>
                                                                        <td>Wind Speed:</td>
                                                                        <td><?php echo round($weather->wind_speed, 2); ?> m/s, <?php echo $weather->wind_direction; ?>&deg;</td>
                                                                    </tr>
                                                                <?php elseif($weather->wind_speed !== null) : ?>
                                                                    <tr>
                                                                        <td>Wind Speed:</td>
                                                                        <td><?php echo round($weather->wind_speed, 2); ?> m/s</td>
                                                                    </tr>
                                                                <?php endif; ?>
                                                            <?php endif; ?>

                                                            <?php if ($weather->luminosity !== null) : ?>
                                                                <tr>
                                                                    <td>Luminosity:</td><td><?php echo round($weather->luminosity,0); ?> W/m&sup2;</td>
                                                                </tr>
                                                            <?php endif; ?>

                                                            <?php if ($weather->snow !== null) : ?>
                                                                <tr>
                                                                <?php if (isImperialUnitUser()) : ?>
                                                                    <td>Snow:</td><td><?php echo round(convertMmToInch($weather->snow), 0); ?> in</td>
                                                                <?php else : ?>
                                                                    <td>Snow:</td><td><?php echo round($weather->snow, 0); ?> mm</td>
                                                                <?php endif; ?>
                                                                </tr>
                                                            <?php endif; ?>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>

                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php if ($station->latestTelemetryPacketTimestamp !== null) : ?>
                                        <?php $telemetry = $packet->getPacketTelemetry(); ?>
                                        <?php if ($telemetry->isExistingObject()) : ?>
                                            <tr>
                                                <td>Telemetry Analog Values</td>
                                                <td>
                                                    <table>
                                                        <tbody>
                                                            <?php for ($i = 1; $i<=5; $i++) : ?>
                                                                <?php if ($telemetry->isValueSet($i)) : ?>
                                                                    <tr>
                                                                        <td><?php echo htmlspecialchars($telemetry->getValueParameterName($i)); ?>:</td>
                                                                        <td><?php echo round($telemetry->getValue($i), 2); ?> <?php echo htmlspecialchars($telemetry->getValueUnit($i)); ?></td>
                                                                    </tr>
                                                                <?php endif; ?>
                                                            <?php endfor; ?>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                            <?php if ($telemetry->bits !== null) : ?>
                                                <tr>
                                                    <td>Telemetry Bit Values</td>
                                                    <td>
                                                        <table>
                                                            <tbody>
                                                                <?php for ($i = 1; $i<=8; $i++) : ?>
                                                                    <tr>
                                                                        <td><?php echo htmlspecialchars($telemetry->getBitParameterName($i)); ?>:</td>
                                                                        <td><?php echo $telemetry->getBit($i); ?></td>
                                                                    </tr>
                                                                <?php endfor; ?>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        <?php endif; ?>


                                        <?php if ($packet->packetTypeId == 7 && strstr($packet->raw, ':UNIT.')) : ?>
                                            <?php $pos = strpos($packet->raw, ':UNIT.'); ?>
                                            <tr>
                                                <td>Telemetry UNIT</td>
                                                <td>
                                                    <?php echo htmlspecialchars(substr($packet->raw, $pos + 6)); ?>
                                                </td>
                                            </tr>
                                        <?php endif; ?>

                                        <?php if ($packet->packetTypeId == 7 && strstr($packet->raw, ':BITS.')) : ?>
                                            <?php $pos = strpos($packet->raw, ':BITS.'); ?>
                                            <tr>
                                                <td>Telemetry BITS</td>
                                                <td>
                                                    <?php echo htmlspecialchars(substr($packet->raw, $pos + 6)); ?>
                                                </td>
                                            </tr>
                                        <?php endif; ?>

                                        <?php if ($packet->packetTypeId == 7 && strstr($packet->raw, ':EQNS.')) : ?>
                                            <?php $pos = strpos($packet->raw, ':EQNS.'); ?>
                                            <tr>
                                                <td>Telemetry EQNS</td>
                                                <td>
                                                    <?php echo htmlspecialchars(substr($packet->raw, $pos + 6)); ?>
                                                </td>
                                            </tr>
                                        <?php endif; ?>

                                        <?php if ($packet->packetTypeId == 7 && strstr($packet->raw, ':PARM.')) : ?>
                                            <?php $pos = strpos($packet->raw, ':PARM.'); ?>
                                            <tr>
                                                <td>Telemetry PARM</td>
                                                <td>
                                                    <?php echo htmlspecialchars(substr($packet->raw, $pos + 6)); ?>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php if ($packet->getPacketOgn()->isExistingObject()) : ?>
                                        <?php if ($packet->getPacketOgn()->ognSignalToNoiseRatio !== null) : ?>
                                            <tr>
                                                <td>Signal to Noise Ratio</td>
                                                <td>
                                                    <?php echo $packet->getPacketOgn()->ognSignalToNoiseRatio; ?> dB
                                                </td>
                                            </tr>
                                        <?php endif;?>

                                        <?php if ($packet->getPacketOgn()->ognBitErrorsCorrected !== null) : ?>
                                            <tr>
                                                <td>Bits corrected</td>
                                                <td>
                                                    <?php echo $packet->getPacketOgn()->ognBitErrorsCorrected; ?>
                                                </td>
                                            </tr>
                                        <?php endif;?>

                                        <?php if ($packet->getPacketOgn()->ognFrequencyOffset !== null) : ?>
                                            <tr>
                                                <td>Frequency Offset</td>
                                                <td>
                                                    <?php echo $packet->getPacketOgn()->ognFrequencyOffset; ?> kHz
                                                </td>
                                            </tr>
                                        <?php endif;?>

                                        <?php if ($packet->getPacketOgn()->ognClimbRate !== null) : ?>
                                            <tr>
                                                <td>Climb Rate</td>
                                                <td>
                                                    <?php echo $packet->getPacketOgn()->ognClimbRate; ?> fpm
                                                </td>
                                            </tr>
                                        <?php endif;?>

                                        <?php if ($packet->getPacketOgn()->ognTurnRate !== null) : ?>
                                            <tr>
                                                <td>Turn Rate</td>
                                                <td>
                                                    <?php echo $packet->getPacketOgn()->ognTurnRate; ?> fpm
                                                </td>
                                            </tr>
                                        <?php endif;?>
                                    <?php endif;?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <?php if (count($packets) == 0) : ?>
        <p>
            <b><i>No raw packets found.</i></b>
        </p>
        <?php endif; ?>
    </div>

    <script>
        $(document).ready(function() {
            var locale = window.navigator.userLanguage || window.navigator.language;
            moment.locale(locale);

            $('.raw-packet-timestamp,.packetts').each(function() {
                if ($(this).html().trim() != '' && !isNaN($(this).html().trim())) {
                    $(this).html(moment(new Date(1000 * $(this).html())).format('L LTSZ'));
                }
            });

            $('#raw-category,#raw-type,#raw-rows').change(function () {
              $("#raw-form").submit();
            });

            $('.parsepkt').each(function(){
              const packet = $(this).text();
              const p1 = packet.split(">");
              const p2 = p1[1].split(":");
              const p3 = p2[0].split(",");
              p2.shift();

              p3.forEach(function(v, k, p3) {
                if (k==0) return;
                if (v.indexOf('WIDE') == -1 && v.indexOf('RELAY') == -1 && v.indexOf('TRACE') == -1 && v.indexOf('qA') == -1 && v.indexOf('TCP') == -1 && v.indexOf('T2') == -1 && v.indexOf('CWOP') == -1 && v.indexOf('APRSFI') == -1) {
                  p3[k] = '<b><a onclick="javascript:loadView(this.href);return false;" href="overview.php?c='+encodeURI(v.replace('*', ''))+'&imperialUnits=<?php echo $_GET['imperialUnits'] ?? 0; ?>">'+v+'</a></b>';
                }
              });
              $(this).html('<b><a onclick="javascript:loadView(this.href);return false;" href="overview.php?id=<?php echo $station->id ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ?? 0; ?>">'+p1[0]+'</a></b>&gt;'+p3.join(',')+':' +p2.join(':'));
            });

            if (window.trackdirect) {
                <?php if ($station->latestConfirmedLatitude != null && $station->latestConfirmedLongitude != null) : ?>
                    window.trackdirect.addListener("map-created", function() {
                        if (!window.trackdirect.focusOnStation(<?php echo $station->id ?>, true)) {
                            window.trackdirect.setCenter(<?php echo $station->latestConfirmedLatitude ?>, <?php echo $station->latestConfirmedLongitude ?>);
                        }
                    });
                <?php endif; ?>
            }
        });
    </script>
<?php endif; ?>
