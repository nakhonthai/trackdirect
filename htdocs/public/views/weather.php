<?php require dirname(__DIR__) . "../../includes/bootstrap.php"; ?>

<?php $station = StationRepository::getInstance()->getObjectById($_GET['id'] ?? null); ?>
<?php if ($station->isExistingObject()) : ?>
    <?php
        $maxDays = 10;
        if (!isAllowedToShowOlderData()) {
            $maxDays = 1;
        }
        $format = $_GET['format'] ?? 'current';
        $graphLabels = array('Time', 'Temperature', 'Humidity', 'Pressure', 'Rain (Last Hour)', 'Rain (Last 24 Hours)', 'Rain (Since Midnight)', 'Wind Speed', 'Wind Direction', 'Luminosity', 'Snow');
        $missingGraphs = [];

        if ($format != 'current') {
          $page = $_GET['page'] ?? 1;
          $rows = $_GET['rows'] ?? 25;
          $offset = ($page - 1) * $rows;
          $weatherPackets = PacketWeatherRepository::getInstance()->getLatestObjectListByStationIdAndLimit($station->id, $rows, $offset, $maxDays);
          $count = PacketWeatherRepository::getInstance()->getLatestNumberOfPacketsByStationIdAndLimit($station->id, $maxDays);
        } else {
          $weatherPackets = PacketWeatherRepository::getInstance()->getLatestObjectListByStationIdAndLimit($station->id, 1, 0, $maxDays);
          $count = 1;
        }
        $pages = ceil($count / $rows);

        $titles = array('current' => 'Current Conditions', 'graph' => 'Weather Graphs', 'table' => 'Table View');
    ?>

    <title><?php echo $station->name; ?> <?php echo $titles[$format]; ?></title>
    <div class="modal-inner-content">
        <div class="modal-inner-content-menu">
            <a class="tdlink" title="Overview" href="/views/overview.php?id=<?php echo $station->id ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ?? 0; ?>">Overview</a>
            <a class="tdlink" title="Statistics" href="/views/statistics.php?id=<?php echo $station->id ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ?? 0; ?>">Statistics</a>
            <a class="tdlink" title="Trail Chart" href="/views/trail.php?id=<?php echo $station->id ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ?? 0; ?>">Trail Chart</a>
            <span>Weather</span>
            <a class="tdlink" title="Telemetry" href="/views/telemetry.php?id=<?php echo $station->id ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ?? 0; ?>">Telemetry</a>
            <a class="tdlink" title="Raw packets" href="/views/raw.php?id=<?php echo $station->id ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ?? 0; ?>">Raw packets</a>
        </div>

        <div class="horizontal-line">&nbsp;</div>

        <?php if (count($weatherPackets) > 0) : ?>
            <?php if ($format == 'current'): ?><p>Here are the current (last reported) weather conditions for station/object <?php echo $station->name; ?>.  If nothing is displayed, no weather information has been provided within the past <?php echo $maxDays; ?> day(s).</p><?php endif; ?>
            <?php if ($format != 'current'): ?><p>This is the latest recevied weather packets stored in our database for station/object <?php echo $station->name; ?>. If no packets are shown the sender has not sent any weather packets the latest <?php echo $maxDays; ?> day(s).</p><?php endif; ?>

            <div class="form-container">
              <span style="float:right;">
                [ <?php if ($format != 'current'): ?><a class="tdlink" href="/views/weather.php?id=<?php echo $station->id; ?>&format=current"><?php echo $titles['current']; ?></a><?php else: ?><?php echo $titles['current']; ?><?php endif; ?> ]
                [ <?php if ($format != 'graph'): ?><a class="tdlink" href="/views/weather.php?id=<?php echo $station->id; ?>&format=graph"><?php echo $titles['graph']; ?></a><?php else: ?><?php echo $titles['graph']; ?><?php endif; ?> ]
                [ <?php if ($format != 'table'): ?><a class="tdlink" href="/views/weather.php?id=<?php echo $station->id; ?>&format=table"><?php echo $titles['table']; ?></a><?php else: ?><?php echo $titles['table']; ?><?php endif; ?> ]
              </span>

                <?php if ($format == 'table'): ?>
                  <select id="weather-rows" style="float:left; margin-right: 5px;" class="pagination-rows">
                      <option <?php echo ($rows == 25 ? 'selected' : ''); ?> value="25">25 rows</option>
                      <option <?php echo ($rows == 50 ? 'selected' : ''); ?> value="50">50 rows</option>
                      <option <?php echo ($rows == 100 ? 'selected' : ''); ?> value="100">100 rows</option>
                      <option <?php echo ($rows == 200 ? 'selected' : ''); ?> value="200">200 rows</option>
                      <option <?php echo ($rows == 300 ? 'selected' : ''); ?> value="300">300 rows</option>
                  </select>
                <?php else: ?>
                    <?php if ($format != 'current'): ?>
                      <span style-="float:left;">Displaying data from <span id="oldest-timestamp" style="font-weight:bold;"></span> to <span id="latest-timestamp" style="font-weight:bold;"></span>.  <span id="records"></span> (max 250)</span>
                    <?php else: ?>
                      <span style-="float:left;">Displaying current weather conditions as of <span id="latest-timestamp" style="font-weight:bold;"><?php echo ($weatherPackets[0]->wxRawTimestamp != null?$weatherPackets[0]->wxRawTimestamp:$weatherPackets[0]->timestamp); ?></span>.
                    <?php endif; ?>
                  <script type="text/javascript">
                          $('#oldest-timestamp, #latest-timestamp').each(function() {
                              if ($(this).html().trim() != '' && !isNaN($(this).html().trim())) {
                                  $(this).html(moment(new Date(1000 * $(this).html())).format('L LTS'));
                              }
                          });
                  </script>
                <?php endif; ?>
            </div>

            <?php if ($pages > 1 && $format == 'table'): ?>
                <div class="pagination">
                  <a class="tdlink" href="/views/weather.php?id=<?php echo $station->id; ?>&rows=<?php echo $rows; ?>&page=1"><<</a>
                  <?php for($i = max(1, $page - 3); $i <= min($pages, $page + 3); $i++) : ?>
                  <a href="/views/weather.php?id=<?php echo $station->id; ?>&rows=<?php echo $rows; ?>&page=<?php echo $i; ?>" <?php echo ($i == $page ? 'class="tdlink active"': 'class="tdlink"')?>><?php echo $i ?></a>
                  <?php endfor; ?>
                  <a class="tdlink" href="/views/weather.php?id=<?php echo $station->id; ?>&rows=<?php echo $rows; ?>&page=<?php echo $pages; ?>">>></a>
                </div>
            <?php endif; ?>

            <!-- Current (last reported) weather conditions) -->
            <?php if ($format == 'current'): ?>
              <script async src="//cdn.rawgit.com/Mikhus/canvas-gauges/gh-pages/download/2.1.7/all/gauge.min.js"></script>
              <?php function gaugeParams() { ?>
                data-width="220"
                data-height="220"
                data-ticks-angle="225"
                data-start-angle="67.5"
                data-color-major-ticks="#ddd"
                data-color-minor-ticks="#ddd"
                data-color-title="#eee"
                data-color-units="#ccc"
                data-color-numbers="#eee"
                data-color-plate="#222"
                data-border-shadow-width="0"
                data-borders="true"
                data-needle-type="arrow"
                data-needle-width="2"
                data-needle-circle-size="7"
                data-needle-circle-outer="true"
                data-needle-circle-inner="false"
                data-animation-duration="1500"
                data-animation-rule="linear"
                data-animate-on-init="true"
                data-color-border-outer="#333"
                data-color-border-outer-end="#111"
                data-color-border-middle="#222"
                data-color-border-middle-end="#111"
                data-color-border-inner="#111"
                data-color-border-inner-end="#333"
                data-color-needle-shadow-down="#333"
                data-color-needle-circle-outer="#333"
                data-color-needle-circle-outer-end="#111"
                data-color-needle-circle-inner="#111"
                data-color-needle-circle-inner-end="#222"
                data-value-box-border-radius="0"
                data-color-value-box-rect="#222"
                data-color-value-box-rect-end="#333"
              <?php } ?>

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
                  <?php gaugeParams(); ?>
                ></canvas>
                <script>
                jQuery.ready();
                </script>
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
                    <?php gaugeParams(); ?>
                ></canvas>
              <?php endif; ?>

              <?php if ($weatherPackets[0]->pressure !== null): ?>
                <!-- Pressure gauge -->
                <canvas data-type="radial-gauge" id="pressure-gauge" class="weather-gauge"
                  data-units="<?php echo isImperialUnitUser() ? 'inHg' : 'hPa'?>"
                  data-title="Pressure"
                  data-min-value="<?php echo isImperialUnitUser() ? '24' : '825'?>"
                  data-max-value="<?php echo isImperialUnitUser() ? '33' : '1100'?>"
                  data-major-ticks="<?php echo isImperialUnitUser() ? '24,26,26,27,28,29,30,31,32,33' : '825,850,875,900,925,950,975,1000,1025,1050,1075,1100'?>"
                  data-minor-ticks="2"
                  data-stroke-ticks="true"
                  data-highlights='[{"from": <?php echo isImperialUnitUser() ? '24' : '825'?>, "to": <?php echo isImperialUnitUser() ? '28' : '900'?>, "color": "rgba(213, 62, 62, .6)"},
                                    {"from": <?php echo isImperialUnitUser() ? '28' : '900'?>, "to": <?php echo isImperialUnitUser() ? '30' : '975'?>, "color": "rgba(255, 173, 10, .5)"},
                                    {"from": <?php echo isImperialUnitUser() ? '30' : '1000'?>, "to": <?php echo isImperialUnitUser() ? '33' : '1100'?>, "color": "rgba(0, 255, 0, .3)"} ]'
                  data-value="<?php echo isImperialUnitUser() ? round(convertMbarToInchHg($weatherPackets[0]->pressure), 1) : round($weatherPackets[0]->pressure, 1)?>"
                  <?php gaugeParams(); ?>
                ></canvas>
              <?php endif; ?>

              <?php if ($weatherPackets[0]->wind_speed !== null): ?>
                <!-- Wind speed gauge -->
                <canvas data-type="radial-gauge" id="wind-speed-gauge" class="weather-gauge"
                  data-units="°<?php echo isImperialUnitUser() ? 'mph' : 'm/s'?>"
                  data-title="Wind Speed"
                  data-min-value="0"
                  data-max-value="<?php echo isImperialUnitUser() ? '120' : '50'?>"
                  data-major-ticks="<?php echo isImperialUnitUser() ? '10,20,30,40,50,60,70,80,90,100,110,120' : '5,10,15,20,25,30,35,40,45,50'?>"
                  data-minor-ticks="2"
                  data-stroke-ticks="true"
                  data-highlights='[{"from": 0, "to": 30, "color": "rgba(128,128, 0, .3)"},
                                    {"from": 70, "to": 100, "color": "rgba(0, 255, 0, .3)"} ]'
                  data-value="<?php echo isImperialUnitUser() ? round(convertMpsToMph($packetWeather->wind_speed), 2) : round($packetWeather->wind_speed, 2)?>"
                  <?php gaugeParams(); ?>
                ></canvas>
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
                  data-value="<?php echo $weatherPackets[0]->wind_direction; ?>"
                  <?php gaugeParams(); ?>
                ></canvas>
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
                  <?php gaugeParams(); ?>
                ></canvas>
              <?php endif; ?>
              </div>

              <?php function rainGraph($title, $value) { ?>
                <!-- <?php echo $title ?> gauge -->
                <canvas data-type="linear-gauge" id="<?php echo strtolower(str_replace(' ', '', $title)); ?>-gauge" class="weather-gauge"
                  data-width="120"
                  data-height="240"
                  data-title="<?php echo $title ?>"
                  data-units="<?php echo isImperialUnitUser() ? 'in' : 'mm'?>"
                  data-min-value="0"
                  data-max-value="<?php echo isImperialUnitUser() ? '4' : '120'?>"
                  data-major-ticks="<?php echo isImperialUnitUser() ? '0,0.5,1,1.5,2,2.5,3,3.5,4' : '0,10,20,30,40,50,60,70,80,90,100,110,120'?>"
                  data-minor-ticks="<?php echo isImperialUnitUser() ? '0.25' : '5'?>"
                  data-stroke-ticks="true"
                  data-highlights='[{"from": <?php echo isImperialUnitUser() ? '0' : '0'?>, "to": <?php echo isImperialUnitUser() ? '1.5' : '50'?>, "color": "rgba(0, 255, 0, .3)"},
                                    {"from": <?php echo isImperialUnitUser() ? '1.5' : '50'?>, "to": <?php echo isImperialUnitUser() ? '3' : '90'?>, "color": "rgba(255, 173, 10, .5)"},
                                    {"from": <?php echo isImperialUnitUser() ? '3' : '90'?>, "to": <?php echo isImperialUnitUser() ? '4' : '120'?>, "color": "rgba(213, 62, 62, .6)"}]'
                  data-color-major-ticks="#ddd"
                  data-color-minor-ticks="#ddd"
                  data-color-title="#eee"
                  data-color-units="#ccc"
                  data-color-numbers="#eee"
                  data-color-plate="#222"
                  data-border-shadow-width="0"
                  data-borders="true"
                  data-needle-type="arrow"
                  data-needle-width="2"
                  data-animation-duration="1500"
                  data-animation-rule="linear"
                  data-animate-on-init="true"
                  data-color-border-outer="#333"
                  data-color-border-outer-end="#111"
                  data-color-border-middle="#222"
                  data-color-border-middle-end="#111"
                  data-color-border-inner="#111"
                  data-color-border-inner-end="#333"
                  data-tick-side="left"
                  data-number-side="left"
                  data-needle-side="left"
                  data-bar-stroke-width="7"
                  data-bar-begin-circle="false"
                  data-color-bar="#ddd"
                  data-color-bar-progress="#02cc20"
                  data-value="<?php echo $value ?>"
                  ></canvas>
              <?php } ?>
              <?php if ($weatherPackets[0]->rain_1h !== null || $weatherPackets[0]->rain_24h !== null || $weatherPackets[0]->rain_since_midnight !== null): ?>
                <div class="gauge-cluster">
                  <?php
                   if ($weatherPackets[0]->rain_1h !== null) rainGraph('Rain Last Hour', isImperialUnitUser() ? round(convertMmToInch($weatherPackets[0]->rain_1h), 2) : round($weatherPackets[0]->rain_1h, 2));
                   if ($weatherPackets[0]->rain_24h !== null) rainGraph('Rain Last 24 Hours', isImperialUnitUser() ? round(convertMmToInch($weatherPackets[0]->rain_24h), 2) : round($weatherPackets[0]->rain_24h, 2));
                   if ($weatherPackets[0]->rain_since_midnight !== null) rainGraph('Rain Since Midnight', isImperialUnitUser() ? round(convertMmToInch($weatherPackets[0]->rain_since_midnight), 2) : round($weatherPackets[0]->rain_since_midnight, 2));
                  ?>
                </div>
              <?php endif; ?>
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
                for (let i = 1; i < 11; i++) {
                  window['ctx_'+i] = document.getElementById('graph_'+i);
                  if (window['ctx_'+i] == null) continue;
                  window['chart_'+i] = new Chart(window['ctx_'+i], {
                    type: 'line',
                    data: {
                        datasets: [{
                            label: "",
                            data: [],
                            borderWidth: 1
                        }]
                    },
                    options: {
                      maintainAspectRatio: true,
                      scales: {
                        x: {
                          type: 'time',
                          time: {
                            unit: 'minute',
                            displayFormats: {
                                minute: 'MMM DD hh:mm a'
                            },
                            tooltipFormat: 'MMM DD hh:mm a'
                          },
                          title: {
                            display: false
                          },
                          ticks: {
                              autoSkip: true,
                              maxTicksLimit: 20
                          }
                        },
                        y: {
                          title: {
                            display: true,
                            text: 'value'
                          }
                        }
                      }
                    }
                  }); // End chart
                }
                $(document).ready(function() {
                  for (let i = 1; i < 11; i++) {
                    if (window['chart_'+i] != null) {
                      $.getJSON('/data/graph.php?id=<?php echo $station->id ?>&type=weather&index=' + i).done(function(response) {
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
              <div class="datagrid datagrid-weather" style="max-width:1000px;">
                  <table>
                      <thead>
                          <tr>
                              <th>Time</th>
                              <th>Temp.</th>
                              <th>Humidity</th>
                              <th>Pressure</th>
                              <th>Rain*</th>
                              <th>Wind**</th>
                              <th>Luminosity</th>
                              <th>Snow</th>
                          </tr>
                      </thead>
                      <tbody>
                      <?php foreach ($weatherPackets as $packetWeather) : ?>

                          <tr>
                              <td class="weathertime">
                                  <?php echo ($packetWeather->wxRawTimestamp != null?$packetWeather->wxRawTimestamp:$packetWeather->timestamp); ?>
                              </td>
                              <td>
                                  <?php if ($packetWeather->temperature !== null) : ?>
                                      <?php if (isImperialUnitUser()) : ?>
                                          <?php echo round(convertCelciusToFahrenheit($packetWeather->temperature), 2); ?>&deg; F
                                      <?php else : ?>
                                          <?php echo round($packetWeather->temperature, 2); ?>&deg; C
                                      <?php endif; ?>
                                  <?php else : ?>
                                      -
                                  <?php endif; ?>
                              </td>
                              <td>
                                  <?php if ($packetWeather->humidity !== null) : ?>
                                      <?php echo $packetWeather->humidity; ?>%
                                  <?php else : ?>
                                      -
                                  <?php endif; ?>
                              </td>
                              <td>
                                  <?php if ($packetWeather->pressure !== null) : ?>
                                      <?php if (isImperialUnitUser()) : ?>
                                          <?php echo round(convertMbarToMmhg($packetWeather->pressure),1); ?> mmHg
                                      <?php else : ?>
                                          <?php echo round($packetWeather->pressure,1); ?> hPa
                                      <?php endif; ?>

                                  <?php else : ?>
                                      -
                                  <?php endif; ?>
                              </td>

                              <?php if ($weatherPackets[0]->rain_1h !== null) : ?>
                                  <td title="<?php echo $packetWeather->getRainSummary(false, true, true); ?>">
                                      <?php if ($packetWeather->rain_1h !== null) : ?>
                                          <?php if (isImperialUnitUser()) : ?>
                                              <?php echo round(convertMmToInch($packetWeather->rain_1h), 2); ?> in
                                          <?php else : ?>
                                              <?php echo round($packetWeather->rain_1h, 2); ?> mm
                                          <?php endif; ?>
                                      <?php else : ?>
                                          -
                                      <?php endif; ?>
                                  </td>
                              <?php elseif ($weatherPackets[0]->rain_24h !== null) : ?>
                                  <td title="<?php echo $packetWeather->getRainSummary(true, false, true); ?>">
                                      <?php if ($packetWeather->rain_24h !== null) : ?>
                                          <?php if (isImperialUnitUser()) : ?>
                                              <?php echo round(convertMmToInch($packetWeather->rain_24h), 2); ?> in
                                          <?php else : ?>
                                              <?php echo round($packetWeather->rain_24h, 2); ?> mm
                                          <?php endif; ?>
                                      <?php else : ?>
                                          -
                                      <?php endif; ?>
                                  </td>
                              <?php else : ?>
                                  <td title="<?php echo $packetWeather->getRainSummary(true, true, false); ?>">
                                      <?php if ($packetWeather->rain_since_midnight !== null) : ?>
                                          <?php if (isImperialUnitUser()) : ?>
                                              <?php echo round(convertMmToInch($packetWeather->rain_since_midnight), 2); ?> in
                                          <?php else : ?>
                                              <?php echo round($packetWeather->rain_since_midnight, 2); ?> mm
                                          <?php endif; ?>
                                      <?php else : ?>
                                          -
                                      <?php endif; ?>
                                  </td>
                              <?php endif; ?>

                              <td title="Wind gust: <?php echo ($packetWeather->wind_gust !== null?round($packetWeather->wind_gust,2):'-'); ?> m/s">

                                  <?php if (isImperialUnitUser()) : ?>
                                      <?php if ($packetWeather->wind_speed !== null && $packetWeather->wind_speed > 0) : ?>
                                          <?php echo round(convertMpsToMph($packetWeather->wind_speed), 2); ?> mph, <?php echo $packetWeather->wind_direction; ?>&deg;
                                      <?php elseif($packetWeather->wind_speed !== null) : ?>
                                          <?php echo round(convertMpsToMph($packetWeather->wind_speed), 2); ?> mph
                                      <?php else : ?>
                                          -
                                      <?php endif; ?>

                                  <?php else : ?>
                                      <?php if ($packetWeather->wind_speed !== null && $packetWeather->wind_speed > 0) : ?>
                                          <?php echo round($packetWeather->wind_speed, 2); ?> m/s, <?php echo $packetWeather->wind_direction; ?>&deg;
                                      <?php elseif($packetWeather->wind_speed !== null) : ?>
                                          <?php echo round($packetWeather->wind_speed, 2); ?> m/s
                                      <?php else : ?>
                                          -
                                      <?php endif; ?>
                                  <?php endif; ?>
                              </td>

                              <td>
                                  <?php if ($packetWeather->luminosity !== null) : ?>
                                      <?php echo round($packetWeather->luminosity,0); ?> W/m&sup2;
                                  <?php else : ?>
                                      -
                                  <?php endif; ?>
                              </td>

                              <td>
                                  <?php if ($packetWeather->snow !== null) : ?>
                                      <?php if (isImperialUnitUser()) : ?>
                                          <?php echo round(convertMmToInch($packetWeather->snow), 0); ?> in
                                      <?php else : ?>
                                          <?php echo round($packetWeather->snow, 0); ?> mm
                                      <?php endif; ?>
                                  <?php else : ?>
                                      -
                                  <?php endif; ?>
                              </td>
                          </tr>

                      <?php endforeach; ?>
                      </tbody>
                  </table>
              </div>

            <p>
                <?php if ($weatherPackets[0]->rain_1h !== null) : ?>
                    * Rain latest hour (hover to see other rain measurements)<br/>
                <?php elseif ($weatherPackets[0]->rain_24h !== null) : ?>
                    * Rain latest 24 hours (hover to see other rain measurements)<br/>
                <?php else : ?>
                    * Rain since midnight (hover to see other rain measurements)<br/>
                <?php endif; ?>
                ** Current wind speed in m/s (hover to see current wind gust speed)
            </p>
          <?php endif; ?>

        <?php endif; ?>

        <?php if (count($weatherPackets) == 0) : ?>
            <p><i><b>No recent weather reports.</b></i></p>
        <?php endif; ?>

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
                loadView("/views/weather.php?id=<?php echo $station->id ?>&rows=" + $('#weather-rows').val() + "&page=1");
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
