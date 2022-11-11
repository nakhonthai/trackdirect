<?php require dirname(__DIR__) . "../../includes/bootstrap.php"; ?>

<?php
  if (isset($_GET['c'])) {
    $station = StationRepository::getInstance()->getObjectByName(strtoupper($_GET['c']) ?? null);
  } else {
    $station = StationRepository::getInstance()->getObjectById($_GET['id'] ?? null);
  }
?>
<?php if ($station->isExistingObject()) : ?>
    <?php
        $maxDays = 10;
        $format = $_GET['format'] ?? 'current';
        $start = $_GET['start'] ?? time()-864000;
        $end = $_GET['end'] ?? time();

        $graphLabels = array('Time', 'Temperature', 'Humidity', 'Pressure', 'Rain (Last Hour)', 'Rain (Last 24 Hours)', 'Rain (Since Midnight)', 'Wind Speed', 'Wind Direction', 'Luminosity', 'Snow');
        $missingGraphs = [];

        $start_time = microtime(true);
        $weatherPackets = PacketWeatherRepository::getInstance()->getLatestObjectListByStationIdAndLimit($station->id, 1, 0, $maxDays);
        $dbtime = microtime(true) - $start_time;

        $titles = array('current' => 'Current Conditions', 'almanac' => 'Almanac', 'graph' => 'Weather Graphs', 'table' => 'Weather Data');
    ?>

    <title><?php echo $station->name; ?> <?php echo $titles[$format]; ?></title>
    <div class="modal-inner-content">
        <div class="modal-inner-content-menu">
            <a class="tdlink" title="Overview" href="/views/overview.php?id=<?php echo $station->id ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ?? 0; ?>">Overview</a>
            <a class="tdlink" title="Statistics" href="/views/statistics.php?id=<?php echo $station->id ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ?? 0; ?>">Statistics</a>
            <a class="tdlink" title="Trail Chart" href="/views/trail.php?id=<?php echo $station->id ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ?? 0; ?>">Trail Chart</a>
            <span>Weather</span>
            <a class="tdlink" title="Telemetry" href="/views/telemetry.php?id=<?php echo $station->id ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ?? 0; ?>">Telemetry</a>
            <a class="tdlink" title="Raw Packets" href="/views/raw.php?id=<?php echo $station->id ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ?? 0; ?>">Raw Packets</a>
            <a class="tdlink" title="Live Feed" href="/views/live.php?id=<?php echo $station->id ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ?? 0; ?>">Live Feed</a>
            <a class="tdlink" title="Messages &amp; Bulletins" href="/views/messages.php?id=<?php echo $station->id ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ?? 0; ?>">Messages &amp; Bulletins</a>
        </div>

        <div class="horizontal-line" style="margin:0">&nbsp;</div>

        <div class="modal-inner-content-menu" style="margin-left:25px;">
            <?php if ($format != 'current'): ?><a class="tdlink" href="/views/weather.php?id=<?php echo $station->id; ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ;?>&format=current"><?php echo $titles['current']; ?></a><?php else: ?><span><?php echo $titles['current']; ?></span><?php endif; ?>
            <?php if ($format != 'almanac'): ?><a class="tdlink" href="/views/weather.php?id=<?php echo $station->id; ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ;?>&format=almanac"><?php echo $titles['almanac']; ?></a><?php else: ?><span><?php echo $titles['almanac']; ?></span><?php endif; ?>
            <?php if ($format != 'graph'): ?><a class="tdlink" href="/views/weather.php?id=<?php echo $station->id; ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ;?>&format=graph"><?php echo $titles['graph']; ?></a><?php else: ?><span><?php echo $titles['graph']; ?></span><?php endif; ?>
            <?php if ($format != 'table'): ?><a class="tdlink" href="/views/weather.php?id=<?php echo $station->id; ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ;?>&format=table"><?php echo $titles['table']; ?></a><?php else: ?><span><?php echo $titles['table']; ?></span><?php endif; ?>
        </div>

        <div class="horizontal-line">&nbsp;</div>

        <?php if (count($weatherPackets) > 0) : ?>
          <?php if ($format == 'current'): ?>
            <p>Here are the current (last reported) weather conditions for station/object <?php echo $station->name; ?>.  If nothing is displayed, no weather information has been provided within the past <?php echo $maxDays; ?> day(s).</p>
          <?php elseif ($format == 'almanac'): ?>
            <p>This is the current climate summary for station/object <?php echo $station->name; ?>.  Daily values are determined by calculating the stations local timezone. If no readings are displayed in any of the tables below it means there has not been enough weather information collected for that period.</p>
          <?php else: ?>
            <p>This is the latest recevied weather packets stored in our database for station/object <?php echo $station->name; ?>. If no graphs are shown the sender has not sent any weather packets during the specified time range.</p>
          <?php endif; ?>

            <div style="float:left;line-height: 28px;">
                    <?php if ($format == 'graph'): ?>
                      <span style="float:left;">Displaying data from <span id="oldest-timestamp" style="font-weight:bold;"></span> to <span id="latest-timestamp" style="font-weight:bold;"></span>.  <span id="records"></span> (max 1000)</span>
                    <?php elseif ($format == 'current'): ?>
                      <span style="">Current weather conditions reported as of <span id="latest-timestamp" style="font-weight:bold;"><?php echo ($weatherPackets[0]->wxRawTimestamp != null?$weatherPackets[0]->wxRawTimestamp:$weatherPackets[0]->timestamp); ?></span>.
                    <?php elseif ($format != 'almanac'): ?>
                      <span style="float:left;">Displaying data from <span id="oldest-timestamp" style="font-weight:bold;"><?php echo $start;?></span> to <span id="latest-timestamp" style="font-weight:bold;"><?php echo $end;?></span>. Data retrieved in <span id="dbtime">....</span> seconds..</span>
                    <?php endif; ?>
                  <script type="text/javascript">
                          $('#oldest-timestamp, #latest-timestamp').each(function() {
                              if ($(this).html().trim() != '' && !isNaN($(this).html().trim())) {
                                  $(this).html(moment(new Date(1000 * $(this).html())).format('L LTS'));
                              }
                          });
                  </script>

            </div>
            <?php if ($format == 'current'): ?><span style="float:right;"><img src="/public/images/dotColor3.svg" style="height:24px;vertical-align:middle;" id="live-img" /><span id="live-status" style="vertical-align:middle;">Waiting for connection...</span></span><?php endif; ?>

            <?php if ($format != 'current' && $format != 'almanac'): ?>
              <form id="wxhistory-form" style="float:right;line-height: 28px">
                Show data
                  from <input type="text" id="start-date" class="form-control" style="height:.5em;width:9em" readonly />
                to <input type="text" id="end-date" class="form-control" style="height:.5em;width:9em" readonly />
                <script>
                  var dbstartdate = moment("<?php echo getWebsiteConfig('database_start_date') ?>");
                  var timenow= moment();
                  var duration = moment.duration(timenow.diff(dbstartdate));
                  var dbdays = Math.floor(duration.asDays());
                  $(document).ready(function(){
                    $("#start-date, #end-date").datepicker({
                        showOtherMonths: true,
                        selectOtherMonths: true,
                        minDate: -(dbdays),
                        maxDate: '0',
                        dateFormat: 'yy-mm-dd',
                        showButtonPanel: true,
                        onSelect: function(selectedDate, dpObj) {
                          if (dpObj.id == 'start-date') $("#end-date").datepicker("option", "minDate", selectedDate);
                          else if (dpObj.id == 'end-date') $("#start-date").datepicker("option", "maxDate", selectedDate);
                        }
                    });

                    $("#start-date").datepicker('setDate', new Date(1000 * <?php echo $start; ?>));
                    $("#end-date").datepicker('setDate', new Date(1000 * <?php echo $end; ?>));
                  });
                </script>
                <input type="submit" value="Go" style="line-height:0px;height:16px;width:3em;padding: 12px 0px;" />
              </form>
              <script>
                $("#wxhistory-form").submit(function(e) {
                  if ($('#start-date').val() != '0') {
                    var startat = moment($('#start-date').val(), 'YYYY-MM-DD HH:mm').unix();
                    var endat = moment($('#end-date').val(), 'YYYY-MM-DD HH:mm').endOf('day').unix();
                    loadView('weather.php?id=<?php echo $_GET['id'] ;?>&format=<?php echo $_GET['format']; ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ;?>&start='+startat+'&end='+endat);
                  }
                  e.preventDefault();
                  return false;
                });
              </script>
            <?php endif; ?>

            <div style="clear:both;"></div>

            <!-- Current (last reported) weather conditions) -->
            <?php if ($format == 'current'): ?>
              <script async src="//cdn.rawgit.com/Mikhus/canvas-gauges/gh-pages/download/2.1.7/all/gauge.min.js"></script>

              <div class="gauge-cluster">
              <?php if ($weatherPackets[0]->temperature !== null): ?>
                <!-- Temperature gauge -->
                <canvas data-type="radial-gauge" id="temperature-gauge" class="weather-gauge"
                  data-units="°<?php echo isImperialUnitUser() ? 'F' : 'C'?>"
                  data-title="Temperature"
                  data-min-value="<?php echo isImperialUnitUser() ? '-20' : '-30'?>"
                  data-max-value="<?php echo isImperialUnitUser() ? '120' : '45'?>"
                  data-major-ticks="<?php echo isImperialUnitUser() ? '-20,-10,0,10,20,30,40,50,60,60,80,90,100,110,120' : '-30,-25,-20,-15,-10,-5,0,5,10,15,20,25,30,35,40,45'?>"
                  data-minor-ticks="2"
                  data-stroke-ticks="true"
                  data-highlights='[{"from": <?php echo isImperialUnitUser() ? '-20' : '-30'?>, "to": <?php echo isImperialUnitUser() ? '40' : '5'?>, "color": "rgba(0, 117, 255, .6)"},
                                    {"from": <?php echo isImperialUnitUser() ? '40' : '5'?>, "to": <?php echo isImperialUnitUser() ? '70' : '25'?>, "color": "rgba(0, 255, 0, .3)"},
                                    {"from": <?php echo isImperialUnitUser() ? '70' : '25'?>, "to": <?php echo isImperialUnitUser() ? '90' : '35'?>, "color": "rgba(255, 173, 10, .5)"},
                                    {"from": <?php echo isImperialUnitUser() ? '90' : '35'?>, "to": <?php echo isImperialUnitUser() ? '120' : '45'?>, "color": "rgba(213, 62, 62, .6)"} ]'
                  data-value="<?php echo isImperialUnitUser() ? round(convertCelciusToFahrenheit($weatherPackets[0]->temperature), 2) : round($weatherPackets[0]->temperature, 2)?>"
                ></canvas>
                <script>wxGaugeParams('temperature-gauge');</script>
              <?php endif; ?>

              <?php if ($weatherPackets[0]->humidity !== null): ?>
                <!-- Humidity gauge -->
                <canvas data-type="radial-gauge" id="humidity-gauge" class="weather-gauge"
                  data-units="%"
                  data-title="Humidity"
                  data-min-value="0"
                  data-max-value="100"
                  data-major-ticks="0, 10,20,30,40,50,60,70,80,90,100"
                  data-minor-ticks="2"
                  data-stroke-ticks="true"
                  data-highlights='[{"from": 0, "to": 40, "color": "rgba(213, 62, 62, .6)"},
                                    {"from": 40, "to": 70, "color": "rgba(255, 173, 10, .5)"},
                                    {"from": 70, "to": 100, "color": "rgba(0, 255, 0, .3)"} ]'
                  data-value="<?php echo $weatherPackets[0]->humidity; ?>"
                ></canvas>
                <script>wxGaugeParams('humidity-gauge');</script>
              <?php endif; ?>

              <?php if ($weatherPackets[0]->pressure !== null): ?>
                <!-- Pressure gauge -->
                <canvas data-type="radial-gauge" id="pressure-gauge" class="weather-gauge"
                  data-units="<?php echo isImperialUnitUser() ? 'inHg' : 'hPa'?>"
                  data-title="Pressure"
                  data-min-value="<?php echo isImperialUnitUser() ? '26' : '825'?>"
                  data-max-value="<?php echo isImperialUnitUser() ? '32' : '1100'?>"
                  data-major-ticks="<?php echo isImperialUnitUser() ? '26,27,28,29,30,31,32' : '825,850,875,900,925,950,975,1000,1025,1050,1075,1100'?>"
                  data-minor-ticks="2"
                  data-stroke-ticks="true"
                  data-highlights='[{"from": <?php echo isImperialUnitUser() ? '26' : '825'?>, "to": <?php echo isImperialUnitUser() ? '28' : '900'?>, "color": "rgba(213, 62, 62, .6)"},
                                    {"from": <?php echo isImperialUnitUser() ? '28' : '900'?>, "to": <?php echo isImperialUnitUser() ? '30' : '975'?>, "color": "rgba(255, 173, 10, .5)"},
                                    {"from": <?php echo isImperialUnitUser() ? '30' : '975'?>, "to": <?php echo isImperialUnitUser() ? '32' : '1100'?>, "color": "rgba(0, 255, 0, .3)"} ]'
                  data-value="<?php echo isImperialUnitUser() ? round(convertMbarToInchHg($weatherPackets[0]->pressure), 1) : round($weatherPackets[0]->pressure, 1)?>"
                ></canvas>
                <script>wxGaugeParams('pressure-gauge');</script>
              <?php endif; ?>

              <?php if ($weatherPackets[0]->wind_speed !== null): ?>
                <!-- Wind speed gauge -->
                <canvas data-type="radial-gauge" id="wind-speed-gauge" class="weather-gauge"
                  data-units="<?php echo isImperialUnitUser() ? 'mph' : 'm/s'?>"
                  data-title="Wind Speed"
                  data-min-value="0"
                  data-max-value="<?php echo isImperialUnitUser() ? '60' : '100'?>"
                  data-major-ticks="<?php echo isImperialUnitUser() ? '0,5,10,15,20,25,30,35,40,45,50,55,60' : '10,20,30,40,50,60,70,80,90,100'?>"
                  data-minor-ticks="5"
                  data-stroke-ticks="true"
                  data-highlights='[{"from": 0, "to": 35, "color": "rgba(0, 255, 0, .3)"},
                                    {"from": 35, "to": 50, "color": "rgba(255, 173, 10, .5)"},
                                    {"from": 50, "to": 60, "color": "rgba(213, 62, 62, .6)"} ]'
                  data-value="<?php echo isImperialUnitUser() ? round(convertMpsToMph($weatherPackets[0]->wind_speed), 2) : round($weatherPackets[0]->wind_speed, 2)?>"
                ></canvas>
                <script>wxGaugeParams('wind-speed-gauge');</script>
              <?php endif; ?>

              <?php if ($weatherPackets[0]->wind_direction !== null): ?>
                <!-- Wind direction gauge -->
                <canvas data-type="radial-gauge" id="wind-direction-gauge" class="weather-gauge"
                  data-units="W/m&sup2;"
                  data-title="Wind Direction"
                  data-min-value="0"
                  data-max-value="360"
                  data-major-ticks="N,NE,E,SE,S,SW,W,NW,N"
                  data-minor-ticks="22"
                  data-ticks-angle="360"
                  data-start-angle="180"
                  data-stroke-ticks="false"
                  data-highlights="false"
                  data-value-box="false"
                  data-value="<?php echo $weatherPackets[0]->wind_direction; ?>"
                ></canvas>
                <script>wxGaugeParams('wind-direction-gauge');</script>
              <?php endif; ?>

              <?php if ($weatherPackets[0]->luminosity !== null): ?>
                <!-- Luminosity gauge -->
                <canvas data-type="radial-gauge" id="luminosity-gauge" class="weather-gauge"
                  data-title="Luminosity"
                  data-min-value="0"
                  data-max-value="1200"
                  data-major-ticks="0,100,200,300,400,500,600,700,800,900,1000,1100,1200"
                  data-minor-ticks="2"
                  data-stroke-ticks="true"
                  data-highlights='[{"from": 0, "to": 100, "color": "#111111"},
                                    {"from": 100, "to": 300, "color": "#333333"},
                                    {"from": 300, "to": 600, "color": "#555555"},
                                    {"from": 600, "to": 900, "color": "#777777"},
                                    {"from": 900, "to": 1100, "color": "#aaaaaa"},
                                    {"from": 1100, "to": 1200, "color": "#cccccc"} ]'
                  data-value="<?php echo round($weatherPackets[0]->luminosity,0); ?>"
                ></canvas>
                <script>wxGaugeParams('luminosity-gauge');</script>
              <?php endif; ?>
              </div>

              <?php function rainGauge($id, $title, $value) { ?>
                <!-- <?php echo $title ?> gauge -->
                <canvas data-type="linear-gauge" id="<?php echo $id; ?>-gauge" class="weather-gauge"
                  data-title="<?php echo $title ?>"
                  data-units="<?php echo isImperialUnitUser() ? 'in' : 'mm'?>"
                  data-max-value="<?php echo isImperialUnitUser() ? '4' : '120'?>"
                  data-major-ticks="<?php echo isImperialUnitUser() ? '0,0.5,1,1.5,2,2.5,3,3.5,4' : '0,10,20,30,40,50,60,70,80,90,100,110,120'?>"
                  data-minor-ticks="<?php echo isImperialUnitUser() ? '0.25' : '5'?>"
                  data-highlights='[{"from": <?php echo isImperialUnitUser() ? '0' : '0'?>, "to": <?php echo isImperialUnitUser() ? '1.5' : '50'?>, "color": "rgba(0, 255, 0, .3)"},
                                    {"from": <?php echo isImperialUnitUser() ? '1.5' : '50'?>, "to": <?php echo isImperialUnitUser() ? '3' : '90'?>, "color": "rgba(255, 173, 10, .5)"},
                                    {"from": <?php echo isImperialUnitUser() ? '3' : '90'?>, "to": <?php echo isImperialUnitUser() ? '4' : '120'?>, "color": "rgba(213, 62, 62, .6)"}]'
                  data-value="<?php echo $value ?>"
                  ></canvas>
                  <script>rainGaugeParams('<?php echo $id; ?>-gauge')</script>
              <?php } ?>
              <?php if ($weatherPackets[0]->rain_1h !== null || $weatherPackets[0]->rain_24h !== null || $weatherPackets[0]->rain_since_midnight !== null): ?>
                <div class="gauge-cluster">
                  <?php
                   if ($weatherPackets[0]->rain_1h !== null) rainGauge('rain_1h', 'Rain Last Hour', isImperialUnitUser() ? round(convertMmToInch($weatherPackets[0]->rain_1h), 2) : round($weatherPackets[0]->rain_1h, 2));
                   if ($weatherPackets[0]->rain_24h !== null) rainGauge('rain_24h', 'Rain Last 24 Hours', isImperialUnitUser() ? round(convertMmToInch($weatherPackets[0]->rain_24h), 2) : round($weatherPackets[0]->rain_24h, 2));
                   if ($weatherPackets[0]->rain_since_midnight !== null) rainGauge('rain_since_midnight', 'Rain Since Midnight', isImperialUnitUser() ? round(convertMmToInch($weatherPackets[0]->rain_since_midnight), 2) : round($weatherPackets[0]->rain_since_midnight, 2));
                  ?>
                </div>
              <?php endif; ?>
            <?php endif; ?>


            <?php if ($format == 'almanac'): ?>
              <?php
                $tz = getNearestTimezone($station->latestConfirmedLatitude, $station->latestConfirmedLongitude);
                $date = new DateTime("today midnight", $tz);

                $almanac = array();
                for ($x = 0; $x < 8; $x++) {
                  $almanac[$x] = PacketWeatherRepository::getInstance()->getAlmanac($station->id, $date->getTimestamp() - (86400 * $x));
                }
                $almanac_today = $almanac[0];
                $almanac_yesterday = $almanac[1];
                $high_temperature = max(array_column($almanac, 'high_temperature'));
                $avg_max_temperature = max(array_column($almanac, 'average_temperature'));
                $avg_min_temperature = min(array_column($almanac, 'average_temperature'));
                $low_temperature = min(array_column($almanac, 'low_temperature'));
                $max_rain = max(array_column($almanac, 'rainfall'));
                $max_wind = max(array_column($almanac, 'wind_speed'));
                $max_gust = max(array_column($almanac, 'wind_gust'));
              ?>
              <div class="datagrid" style="width:100%;border:none">
                  <table style="width:35%;float:left;margin:20px 9% 20px 9%;border:1px solid black;">
                      <thead>
                        <tr>
                            <th colspan="2" style="padding:2px;font-weight:bold;">Today's Weather</td>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td style="width:60%">High Temperature</td>
                          <td><?php echo isImperialUnitUser() ? round(convertCelciusToFahrenheit($almanac_today['high_temperature']), 2) . '&deg; F' : round($almanac_today['high_temperature'], 2) . '&deg; C'; ?></td>
                        </tr>
                        <tr>
                          <td>Low Temperature</td>
                          <td><?php echo isImperialUnitUser() ? round(convertCelciusToFahrenheit($almanac_today['low_temperature']), 2) . '&deg; F' : round($almanac_today['low_temperature'], 2) . '&deg; C'; ?></td>
                        </tr>
                        <tr>
                          <td>Average Temperature</td>
                          <td><?php echo isImperialUnitUser() ? round(convertCelciusToFahrenheit($almanac_today['average_temperature']), 2) . '&deg; F' : round($almanac_today['average_temperature'], 2) . '&deg; C'; ?></td>
                        </tr>
                        <tr>
                          <td>Rain:</td>
                          <td><?php echo isImperialUnitUser() ? round(convertMmToInch($almanac_today['rainfall']), 2) . ' in' : round($almanac_today['rainfall'], 2) . ' mm'; ?></td>
                        </tr>
                        <tr>
                          <td>Wind (Gust):</td>
                          <td>
                            <?php echo isImperialUnitUser() ? round(convertMpsToMph($almanac_today['wind_speed']), 2) . ' mph' : round($almanac_today['wind_speed'], 2) . ' m/s'; ?>
                            (<?php echo isImperialUnitUser() ? round(convertMpsToMph($almanac_today['wind_gust']), 2) . ' mph' : round($almanac_today['wind_gust'], 2) . ' m/s'; ?>)
                          </td>
                        </tr>
                        <tr>
                          <td>Highest Pressure:</td>
                          <td><?php echo isImperialUnitUser() ? round(convertMbarToInchHg($almanac_today['high_pressure']), 1) . ' in' : round($almanac_today['high_pressure'], 1) . ' mm'; ?></td>
                        </tr>
                        <tr>
                          <td>Lowest Pressure</td>
                          <td><?php echo isImperialUnitUser() ? round(convertMbarToInchHg($almanac_today['low_pressure']), 1) . ' in' : round($almanac_today['low_pressure'], 1) . ' mm'; ?></td>
                        </tr>
                      </tbody>
                    </table>
                    <table style="width:35%;float:left;margin:20px;border:1px solid black;">
                        <thead>
                          <tr>
                              <th colspan="2" style="padding:2px;font-weight:bold;">Yesterday's Weather</td>
                          </tr>
                        </thead>
                        <tbody>
                          <tr>
                            <td style="width:60%">High Temperature</td>
                            <td><?php echo isImperialUnitUser() ? round(convertCelciusToFahrenheit($almanac_yesterday['high_temperature']), 2) . '&deg; F' : round($almanac_yesterday['high_temperature'], 2) . '&deg; C'; ?></td>
                          </tr>
                          <tr>
                            <td>Low Temperature</td>
                            <td><?php echo isImperialUnitUser() ? round(convertCelciusToFahrenheit($almanac_yesterday['low_temperature']), 2) . '&deg; F' : round($almanac_yesterday['low_temperature'], 2) . '&deg; C'; ?></td>
                          </tr>
                          <tr>
                            <td>Average Temperature</td>
                            <td><?php echo isImperialUnitUser() ? round(convertCelciusToFahrenheit($almanac_yesterday['average_temperature']), 2) . '&deg; F' : round($almanac_yesterday['average_temperature'], 2) . '&deg; C'; ?></td>
                          </tr>
                          <tr>
                            <td>Rain:</td>
                            <td><?php echo isImperialUnitUser() ? round(convertMmToInch($almanac_yesterday['rainfall']), 2) . ' in' : round($almanac_yesterday['rainfall'], 2) . ' mm'; ?></td>
                          </tr>
                          <tr>
                            <td>Wind (Gust):</td>
                            <td>
                              <?php echo isImperialUnitUser() ? round(convertMpsToMph($almanac_yesterday['wind_speed']), 2) . ' mph' : round($almanac_yesterday['wind_speed'], 2) . ' m/s'; ?>
                              (<?php echo isImperialUnitUser() ? round(convertMpsToMph($almanac_yesterday['wind_gust']), 2) . ' mph' : round($almanac_yesterday['wind_gust'], 2) . ' m/s'; ?>)
                            </td>
                          </tr>
                          <tr>
                            <td>Highest Pressure:</td>
                            <td><?php echo isImperialUnitUser() ? round(convertMbarToInchHg($almanac_yesterday['high_pressure']), 1) . ' in' : round($almanac_yesterday['high_pressure'], 1) . ' mm'; ?></td>
                          </tr>
                          <tr>
                            <td>Lowest Pressure</td>
                            <td><?php echo isImperialUnitUser() ? round(convertMbarToInchHg($almanac_yesterday['low_pressure']), 1) . ' in' : round($almanac_yesterday['low_pressure'], 1) . ' mm'; ?></td>
                          </tr>
                        </tbody>
                      </table>
                    <div style="clear:both"></div>
                      <table style="width:98%;margin:10px;border:1px solid black;">
                        <thead>
                          <tr>
                              <th style="padding:2px;font-weight:bold;">Weather for the Past Week</td>
                            <?php for ($x = 0; $x < 7; $x++): ?>
                              <th><?php if ($x == 0):?>Today<?php elseif ($x == 1):?>Yesterday<?php else:?><?php echo date('D M d', $date->getTimestamp()-(86400*$x));?><?php endif;?></th>
                            <?php endfor; ?>
                          </tr>
                        </thead>
                        <tbody>
                          <tr>
                            <td style="width:20%">High Temperature</td>
                          <?php for ($x = 0; $x < 7; $x++): ?>
                            <td<?php if ($almanac[$x]['high_temperature'] == $high_temperature):?> style="color:#FF0033"<?php endif;?>><?php echo isImperialUnitUser() ? round(convertCelciusToFahrenheit($almanac[$x]['high_temperature']), 2) . '&deg; F' : round($almanac[$x]['high_temperature'], 2) . '&deg; C'; ?></td>
                          <?php endfor; ?>
                          </tr>
                          <tr>
                            <td>Low Temperature</td>
                          <?php for ($x = 0; $x < 7; $x++): ?>
                            <td<?php if ($almanac[$x]['low_temperature'] == $low_temperature):?> style="color:#2200FF"<?php endif;?>><?php echo isImperialUnitUser() ? round(convertCelciusToFahrenheit($almanac[$x]['low_temperature']), 2) . '&deg; F' : round($almanac[$x]['low_temperature'], 2) . '&deg; C'; ?></td>
                          <?php endfor; ?>
                          </tr>
                          <tr>
                            <td>Average Temperature</td>
                          <?php for ($x = 0; $x < 7; $x++): ?>
                            <td<?php if ($almanac[$x]['average_temperature'] == $avg_max_temperature):?> style="color:#FF0033"<?php elseif ($almanac[$x]['average_temperature'] == $avg_min_temperature):?> style="color:#2200FF"<?php endif;?>><?php echo isImperialUnitUser() ? round(convertCelciusToFahrenheit($almanac[$x]['average_temperature']), 2) . '&deg; F' : round($almanac[$x]['average_temperature'], 2) . '&deg; C'; ?></td>
                          <?php endfor; ?>
                          </tr>
                          <tr>
                            <td>Rain:</td>
                          <?php for ($x = 0; $x < 7; $x++): ?>
                            <td<?php if ($almanac[$x]['rainfall'] == $max_rain):?> style="color:#11BB33"<?php endif;?>><?php echo isImperialUnitUser() ? round(convertMmToInch($almanac[$x]['rainfall']), 2) . ' in' : round($almanac[$x]['rainfall'], 2) . ' mm'; ?></td>
                          <?php endfor; ?>
                          </tr>
                          <tr>
                            <td>Wind:</td>
                              <?php for ($x = 0; $x < 7; $x++): ?>
                                <td<?php if ($almanac[$x]['wind_speed'] == $max_wind):?> style="color:#CA33FF"<?php endif;?>><?php echo isImperialUnitUser() ? round(convertMpsToMph($almanac[$x]['wind_speed']), 2) . ' mph' : round($almanac[$x]['wind_speed'], 2) . ' m/s'; ?></td>
                              <?php endfor; ?>
                          </tr>
                          <tr>
                            <td>Wind Gust:</td>
                              <?php for ($x = 0; $x < 7; $x++): ?>
                                <td<?php if ($almanac[$x]['wind_gust'] == $max_gust):?> style="color:#CA33FF"<?php endif;?>><?php echo isImperialUnitUser() ? round(convertMpsToMph($almanac[$x]['wind_gust']), 2) . ' mph' : round($almanac[$x]['wind_speed'], 2) . ' m/s'; ?></td>
                              <?php endfor; ?>
                          </tr>
                          <tr>
                            <td>Highest Pressure:</td>
                          <?php for ($x = 0; $x < 7; $x++): ?>
                            <td><?php echo isImperialUnitUser() ? round(convertMbarToInchHg($almanac[$x]['high_pressure']), 1) . ' in' : round($almanac[$x]['high_pressure'], 1) . ' mm'; ?></td>
                          <?php endfor; ?>
                          </tr>
                          <tr>
                            <td>Lowest Pressure</td>
                          <?php for ($x = 0; $x < 7; $x++): ?>
                            <td><?php echo isImperialUnitUser() ? round(convertMbarToInchHg($almanac[$x]['low_pressure']), 1) . ' in' : round($almanac[$x]['low_pressure'], 1) . ' mm'; ?></td>
                          <?php endfor; ?>
                          </tr>
                        </tbody>
                      </table>
                  </div>
            <?php endif; ?>


            <?php if ($format == 'graph'): ?>
              <?php for ($graphIdx = 1; $graphIdx < 11; $graphIdx++) : ?>
              <?php
                if (
                    ($graphIdx == 1 && $weatherPackets[0]->temperature === null) ||
                    ($graphIdx == 2 && $weatherPackets[0]->humidity === null) ||
                    ($graphIdx == 3 && $weatherPackets[0]->pressure === null) ||
                    ($graphIdx == 4 && $weatherPackets[0]->rain_1h === null) ||
                    ($graphIdx == 5 && $weatherPackets[0]->rain_24h === null) ||
                    ($graphIdx == 6 && $weatherPackets[0]->rain_since_midnight === null) ||
                    ($graphIdx == 7 && $weatherPackets[0]->wind_speed === null) ||
                    ($graphIdx == 8 && $weatherPackets[0]->wind_direction === null) ||
                    ($graphIdx == 9 && $weatherPackets[0]->luminosity === null) ||
                    ($graphIdx == 10 && $weatherPackets[0]->snow === null)
                  ) {
                    $missingGraphs[] = $graphIdx;
                    continue;
                  }
              ?>
                <div style="width:100%;background:#dddddd;padding:2px;font-weight:bold;"><?php echo $station->name; ?> [<?php echo $graphLabels[$graphIdx]; ?>]</div>
                <canvas id="graph_<?php echo $graphIdx; ?>" height="80"></canvas>
                <div style="height:20px;"></div>
              <?php endfor; ?>

              <?php if (count($missingGraphs)) : ?>
                <p>Station <b><?php echo $station->name; ?></b> does not, or has not provided the following data in the specificed period:</p>
                <ul>
              <?php
                  foreach ($missingGraphs as $graphId) {
                    echo '<li>'.$graphLabels[$graphId].'</li>';
                  }
              ?>
            </ul>
              <?php endif; ?>

              <script type="text/javascript">
                initGraph(10);
                $(document).ready(function() {
                  for (let i = 1; i < 11; i++) {
                    if (window['chart_'+i] != null) {
                      $.getJSON('/data/graph.php?id=<?php echo $station->id ?>&type=weather&start=<?php echo $start; ?>&end=<?php echo $end; ?>&index=' + i).done(function(response) {
                        $('#oldest-timestamp').text(response.oldest_timestamp);
                        $('#latest-timestamp').text(response.latest_timestamp);
                        $('#oldest-timestamp, #latest-timestamp').each(function() {
                          if ($(this).html().trim() != '' && !isNaN($(this).html().trim())) {
                            $(this).html(moment(new Date(1000 * $(this).html())).format('L LTS'));
                          }
                        });
                        $('#records').text(response.records + ' records found');

                        window['chart_'+i].data.datasets[0].data = response.data;
                        window['chart_'+i].data.datasets[0].label = response.label;
                        if (response.borderColor != null) window['chart_'+i].data.datasets[0].borderColor = response.borderColor;
                        if (response.borderColor != null) window['chart_'+i].data.datasets[0].backgroundColor = response.backgroundColor;
                        window['chart_'+i].update();
                      });
                    }
                  }
                });
              </script>
            <?php endif; ?>
            <?php if ($format == 'table'): ?>
              <div class="datagrid datagrid-weather">
                  <table id="weather-table" style="width:100%">
                      <thead>
                          <tr>
                              <th>Time</th>
                              <th>Temp.</th>
                              <th>Humidity</th>
                              <th>Pressure</th>
                              <th>Wind Speed</th>
                              <th>Wind Direction</th>
                              <th>Rain 1hr</th>
                              <th>Rain 24hr</th>
                              <th>Rain Midnight</th>
                              <th>Luminosity</th>
                              <th>Snow</th>
                          </tr>
                      </thead>
                      <tbody>
                      </tbody>
                  </table>
              </div>

          <?php endif; ?>

        <?php endif; ?>

        <?php if (count($weatherPackets) == 0) : ?>
            <p><i><b>No recent weather reports.</b></i></p>
        <?php endif; ?>

        <div class="quiklink">
          Link directly to this page: <input id="quiklink" type="text" value="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]"; ?>/station/<?php echo $station->name; ?>/<?php echo basename(__FILE__, '.php'); ?>/<?php echo $format; ?>/" readonly>
          <img id="quikcopy" src="/images/copy.svg"/>
        </div>

    </div>


    <script>
        $(document).ready(function() {
            var locale = window.navigator.userLanguage || window.navigator.language;
            moment.locale(locale);

            $('.weathertime').each(function() {
                if ($(this).html().trim() != '' && !isNaN($(this).html().trim())) {
                    $(this).html(moment(new Date(1000 * $(this).html())).format('L LTSZ'));
                }
            });

            $('#weather-rows').change(function () {
                loadView("/views/weather.php?id=<?php echo $station->id ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ;?>&format=table&start=<?php echo $start; ?>&end=<?php echo $end; ?>&rows=" + $('#weather-rows').val() + "&page=1");
            });

            <?php if ($format=='table'): ?>
              $('#weather-table').DataTable( {
                ajax: {
                  url: '/data/data.php?module=weather&command=getWeather&id=<?php echo $station->id ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ;?><?php if (isset($_GET['start'])): ?>&start=<?php echo $_GET['start'];?>&end=<?php echo $_GET['end'];?><?php endif;?>',
                  dataSrc: function (json) {
                    $("#dbtime").text(json.data.dbtime);
                    return json.data.readings;
                  }
                },
                columns: [
                  { data: 'ts',
                    render: DataTable.render.datetime(),
                    width: '10em' },
                  { data: '0',
                    render: function(data) {
                        return data + ' &deg;<?php echo isImperialUnitUser() ? 'F' : 'C'?>';
                      },
                    width: '2em' },
                  { data: '1',
                    render: function(data) {
                        return data + ' %';
                      },
                    width: '3em' },
                  { data: '2',
                    render: function(data) {
                        return data + ' <?php echo isImperialUnitUser() ? 'inHg' : 'hPa'?>';
                      },
                    width: '3em' },
                  { data: '3',
                    render: function(data) {
                        return data + ' <?php echo isImperialUnitUser() ? 'mph' : 'm/s'?>';
                      },
                    width: '3em' },
                  { data: '4',
                    render: function(data) {
                        return data + ' &deg;';
                      },
                    width: '3em' },
                  { data: '5',
                    render: function(data) {
                        return data + ' <?php echo isImperialUnitUser() ? 'in' : 'mm'?>';
                      },
                    width: '3em' },
                  { data: '6',
                    render: function(data) {
                        return data + ' <?php echo isImperialUnitUser() ? 'in' : 'mm'?>';
                      },
                    width: '3em' },
                  { data: '7',
                    render: function(data) {
                        return data + ' <?php echo isImperialUnitUser() ? 'in' : 'mm'?>';
                      },
                    width: '3em' },
                  { data: '8',
                    render: function(data) {
                        return data + ' W/m²';
                      },
                    width: '3em' },
                  { data: '9',
                    render: function(data) {
                        return data + ' <?php echo isImperialUnitUser() ? 'in' : 'mm'?>';
                      },
                    width: '3em' },
                ],
                order: [[0, 'desc']],
                responsive: true
              });
              $("input[type=search]").css('padding', '1px');
            <?php endif; ?>

            if (window.trackdirect) {
                <?php if ($station->latestConfirmedLatitude != null && $station->latestConfirmedLongitude != null) : ?>
                    window.trackdirect.addListener("map-created", function() {
                        if (!window.trackdirect.focusOnStation(<?php echo $station->id ?>, true)) {
                            window.trackdirect.setCenter(<?php echo $station->latestConfirmedLatitude ?>, <?php echo $station->latestConfirmedLongitude ?>);
                        }
                    });
                <?php endif; ?>
                window.trackdirect.addListener("trackdirect-init-done", function () {
                  window.liveData.start("<?php echo $station->name;?>", <?php echo $station->latestPacketTimestamp; ?>, 'wxcurrent');
                });
            }

            quikLink();
        });
    </script>
<?php endif; ?>
