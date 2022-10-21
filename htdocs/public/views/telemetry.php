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
        $format = $_GET['format'] ?? 'current';
        $start = $_GET['start'] ?? time()-864000;
        $end = $_GET['end'] ?? time();

        $start_time = microtime(true);

        $telemetryPackets = PacketTelemetryRepository::getInstance()->getLatestObjectListByStationId($station->id, 1, 0, 10);
        $latestPacketTelemetry = (count($telemetryPackets) > 0 ? $telemetryPackets[0] : new PacketTelemetry(null));
        $dbtime = microtime(true) - $start_time;

        $titles = array('current' => 'Current Readings', 'graph' => 'Telemetry Graphs', 'table' => 'Telemetry Data');
    ?>

    <title><?php echo $station->name; ?> <?php echo $titles[$format]; ?></title>
    <div class="modal-inner-content">
        <div class="modal-inner-content-menu">
            <a class="tdlink" title="Overview" href="/views/overview.php?id=<?php echo $station->id ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ?? 0; ?>">Overview</a>
            <a class="tdlink" title="Statistics" href="/views/statistics.php?id=<?php echo $station->id ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ?? 0; ?>">Statistics</a>
            <a class="tdlink" title="Trail Chart" href="/views/trail.php?id=<?php echo $station->id ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ?? 0; ?>">Trail Chart</a>
            <a class="tdlink" title="Weather" href="/views/weather.php?id=<?php echo $station->id ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ?? 0; ?>">Weather</a>
            <span>Telemetry</span>
            <a class="tdlink" title="Raw Packets" href="/views/raw.php?id=<?php echo $station->id ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ?? 0; ?>">Raw Packets</a>
            <a class="tdlink" title="Live Feed" href="/views/live.php?id=<?php echo $station->id ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ?? 0; ?>">Live Feed</a>
            <a class="tdlink" title="Messages &amp; Bulletins" href="/views/messages.php?id=<?php echo $station->id ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ?? 0; ?>">Messages &amp; Bulletins</a>
        </div>

        <div class="horizontal-line" style="margin:0">&nbsp;</div>

        <div class="modal-inner-content-menu" style="margin-left:25px;">
            <?php if ($format != 'current'): ?><a class="tdlink" href="/views/telemetry.php?id=<?php echo $station->id; ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ;?>&category=<?php echo ($_GET['category'] ?? 1); ?>&format=current"><?php echo $titles['current']; ?></a><?php else: ?><span><?php echo $titles['current']; ?></span><?php endif; ?>
            <?php if ($format != 'table'): ?><a class="tdlink" href="/views/telemetry.php?id=<?php echo $station->id; ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ;?>&category=<?php echo ($_GET['category'] ?? 1); ?>&format=table"><?php echo $titles['table']; ?></a><?php else: ?><span><?php echo $titles['table']; ?></span><?php endif; ?>
            <?php if ($format != 'graph'): ?><a class="tdlink" href="/views/telemetry.php?id=<?php echo $station->id; ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ;?>&category=<?php echo ($_GET['category'] ?? 1); ?>&format=graph"><?php echo $titles['graph']; ?></a><?php else: ?><span><?php echo $titles['graph']; ?></span><?php endif; ?>
        </div>
        <div class="horizontal-line">&nbsp;</div>

        <?php if (count($telemetryPackets) > 0) : ?>

            <p>This is the latest recevied telemetry packets stored in our database for station/object <?php echo $station->name; ?>. If no data is shown the sender has not sent any telemetry packets within <?php if ($format == 'current'): ?>the past <?php echo $maxDays; ?> day(s)<?php else: ?> the specified period<?php endif; ?>.</p>
            <p>Telemetry packets is used to share measurements like repeteater parameters, battery voltage, radiation readings (or any other measurements).</p>

            <div style="float:left;line-height: 28px">
                <?php if ($format == 'graph'): ?>
                  <?php $lastEntry = end($telemetryPackets); reset($telemetryPackets); ?>
                  <span style-="float:left;">Displaying data from <span id="oldest-timestamp" style="font-weight:bold;"></span> to <span id="latest-timestamp" style="font-weight:bold;"></b></span>.  <span id="records"></span> (max 1000)</span>
                <?php elseif ($format == 'table'): ?>
                  <span style="float:left;">Telemetry records retrieved in <span id="dbtime">....</span> seconds.</span>
                <?php else: ?>
                  <span style="float:left;">Displaying latest telemetry as of <span id="latest-timestamp" class="telemetrytime" style="font-weight:bold;"><?php echo ($telemetryPackets[0]->wxRawTimestamp != null?$telemetryPackets[0]->wxRawTimestamp:$telemetryPackets[0]->timestamp); ?></span>. Data retrieved in <?php echo round($dbtime, 3) ?> seconds.</span>
                <?php endif; ?>
            </div>
            <?php if ($format == 'current'): ?><span style="float:right;"><img src="/public/images/dotColor3.svg" style="height:24px;vertical-align:middle;" id="live-img" /><span id="live-status" style="vertical-align:middle;">Waiting for connection...</span></span><?php endif; ?>

            <?php if ($format != 'current'): ?>
              <form id="telemhistory-form" style="float:right;line-height: 28px">
                Show
                <select id="telemetry-category">
                    <option <?php echo (($_GET['category'] ?? 1) == 1 ? 'selected' : ''); ?> value="1">Telemetry Values</option>
                    <option <?php echo (($_GET['category'] ?? 1) == 2 ? 'selected' : ''); ?> value="2">Telemetry Bits</option>
                </select>
                from <input type="text" id="start-date" class="form-control" style="height:.5em;width:8.5em" readonly />
                to <input type="text" id="end-date" class="form-control" style="height:.5em;width:8.5em" readonly />
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
                $("#telemhistory-form").submit(function(e) {
                  if ($('#start-date').val() != '0') {
                    var startat = moment($('#start-date').val(), 'YYYY-MM-DD HH:mm').unix();
                    var endat = moment($('#end-date').val(), 'YYYY-MM-DD HH:mm').endOf('day').unix();
                    loadView('/views/telemetry.php?id=<?php echo $station->id; ?>&format=<?php echo $format; ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ;?>&category=<?php echo ($_GET['category'] ?? 1); ?>&start='+startat+'&end='+endat);
                  }
                  e.preventDefault();
                  return false;
                });
              </script>
            <?php endif; ?>

            <div style="clear:both;"></div>

            <?php if ($format == 'current'): ?>
              <div class="datagrid datagrid-telemetry1" style="max-width:1000px;">
                  <table style="width:100%;max-width:1000px;">
                      <thead>
                          <tr>
                              <th colspan="2" style="width:100%;background:#dddddd;padding:2px;font-weight:bold;"><?php echo $station->name; ?> Current Telemetry</td>
                          </tr>
                      </thead>
                      <tbody>
                        <?php for ($x = 1; $x <= 5; $x++): ?>
                          <tr>
                              <td id="telem-<?php echo $x; ?>-name" width="20%"><?php echo htmlspecialchars($latestPacketTelemetry->getValueParameterName($x)); ?>:</td>
                              <td id="telem-<?php echo $x; ?>-value">
                                <?php if ($latestPacketTelemetry->{"val$x"} !== null): ?>
                                  <?php $converted = universalDataUnitConvert(round($latestPacketTelemetry->getValue($x), 2), $latestPacketTelemetry->getValueUnit($x)); ?>
                                  <?php echo $converted['value']; ?> <?php echo htmlspecialchars($converted['unit']); ?>
                                <?php else : ?>
                                    -
                                <?php endif; ?>
                              </td>
                          </tr>
                        <?php endfor; ?>
                      </tbody>
                  </table>
              </div>

              <br />

              <?php if ($latestPacketTelemetry->bits !== null): ?>
                <div class="datagrid" style="max-width:1000px;">
                  <table style="width:100%;max-width:1000px;">
                      <thead>
                          <tr>
                              <th colspan="2" style="width:100%;background:#dddddd;padding:2px;font-weight:bold;"><?php echo $station->name; ?> Current Bits</td>
                          </tr>
                      </thead>
                      <tbody>
                        <?php for ($x = 1; $x < 9; ++$x): ?>
                          <tr>
                              <td id="bits-<?php echo $x; ?>-name" width="20%"><?php echo htmlspecialchars($latestPacketTelemetry->getBitParameterName($x)); ?>:</td>
                              <td id="bits-<?php echo $x; ?>-value"><?php echo htmlspecialchars($latestPacketTelemetry->getBitLabel($x)); ?></td>
                          </tr>
                        <?php endfor; ?>
                      </tbody>
                  </table>
                </div>
              <?php endif; ?>
            <?php endif; ?>

            <?php if (($_GET['category'] ?? 1) == 1) : ?>
              <?php if ($format == 'graph'): ?>
                <?php for ($graphIdx = 1; $graphIdx < 6; $graphIdx++) : ?>
                  <?php
                    if (
                        ($graphIdx == 1 && $telemetryPackets[0]->val1 === null) ||
                        ($graphIdx == 2 && $telemetryPackets[0]->val2 === null) ||
                        ($graphIdx == 3 && $telemetryPackets[0]->val3 === null) ||
                        ($graphIdx == 4 && $telemetryPackets[0]->val4 === null) ||
                        ($graphIdx == 5 && $telemetryPackets[0]->val5 === null)
                      ) {
                      continue;
                    }
                  ?>
                  <div style="width:100%;background:#dddddd;padding:2px;font-weight:bold;"><?php echo $station->name; ?> [<?php echo htmlspecialchars($latestPacketTelemetry->getValueParameterName($graphIdx)); ?>]</div>
                  <canvas id="graph_<?php echo $graphIdx; ?>" height="80"></canvas>
                  <div style="height:20px;"></div>
                <?php endfor; ?>
                <script type="text/javascript">
                  initGraph(5);
                  $(document).ready(function() {
                    for (let i = 1; i < 6; i++) {
                      if (window['chart_'+i] != null) {
                        $.getJSON('/data/graph.php?id=<?php echo $station->id ?>&type=telemetry&start=<?php echo $start; ?>&end=<?php echo $end; ?>&index=' + i).done(function(response) {
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
              <div class="datagrid datagrid-telemetry1" >
                  <table id="telem-value-table" style="width:100%">
                      <thead>
                          <tr>
                              <th>Time</th>
                              <th><?php echo htmlspecialchars($latestPacketTelemetry->getValueParameterName(1)); ?>*</th>
                              <th><?php echo htmlspecialchars($latestPacketTelemetry->getValueParameterName(2)); ?>*</th>
                              <th><?php echo htmlspecialchars($latestPacketTelemetry->getValueParameterName(3)); ?>*</th>
                              <th><?php echo htmlspecialchars($latestPacketTelemetry->getValueParameterName(4)); ?>*</th>
                              <th><?php echo htmlspecialchars($latestPacketTelemetry->getValueParameterName(5)); ?>*</th>
                          </tr>
                      </thead>
                      <tbody>
                      </tbody>
                  </table>
              </div>

              <div class="telemetry-subtable">
                  <div>
                      <div>
                          *Used Equation Coefficients:
                      </div>
                      <div>
                          <?php echo htmlspecialchars($latestPacketTelemetry->getValueParameterName(1)); ?>: <?php echo implode(', ', $latestPacketTelemetry->getEqnsValue(1)); ?>
                      </div>
                      <div>
                          <?php echo htmlspecialchars($latestPacketTelemetry->getValueParameterName(2)); ?>: <?php echo implode(', ', $latestPacketTelemetry->getEqnsValue(2)); ?>
                      </div>
                      <div>
                          <?php echo htmlspecialchars($latestPacketTelemetry->getValueParameterName(3)); ?>: <?php echo implode(', ', $latestPacketTelemetry->getEqnsValue(3)); ?>
                      </div>
                      <div>
                          <?php echo htmlspecialchars($latestPacketTelemetry->getValueParameterName(4)); ?>: <?php echo implode(', ', $latestPacketTelemetry->getEqnsValue(4)); ?>
                      </div>
                      <div>
                          <?php echo htmlspecialchars($latestPacketTelemetry->getValueParameterName(5)); ?>: <?php echo implode(', ', $latestPacketTelemetry->getEqnsValue(5)); ?>
                      </div>
                  </div>
              </div>
              <?php endif; ?>
            <?php endif; ?>

              <?php if (($_GET['category'] ?? 1) == 2) : ?>

                <?php if ($format == 'graph'): ?>
                  <?php for ($graphIdx = 1; $graphIdx < 9; $graphIdx++) : ?>
                    <div style="width:100%;background:#dddddd;padding:2px;font-weight:bold;"><?php echo $station->name; ?> [<?php echo htmlspecialchars($latestPacketTelemetry->getBitParameterName($graphIdx)); ?>]</div>
                    <canvas id="graph_<?php echo $graphIdx; ?>" height="80"></canvas>
                    <div style="height:20px;"></div>
                  <?php endfor; ?>
                  <script type="text/javascript">
                  initGraph(9);
                    $(document).ready(function() {
                      for (let i = 1; i < 9; i++) {
                        if (window['chart_'+i] != null) {
                          $.getJSON('/data/graph.php?id=<?php echo $station->id ?>&type=telemetrybits&start=<?php echo $start; ?>&end=<?php echo $end; ?>&index=' + i).done(function(response) {
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
                  <div class="datagrid datagrid-telemetry2">
                      <table id="telem-bit-table" style="width:100%;">
                          <thead>
                              <tr>
                                  <th>Time</th>
                                  <th><?php echo htmlspecialchars($latestPacketTelemetry->getBitParameterName(1)); ?>*</th>
                                  <th><?php echo htmlspecialchars($latestPacketTelemetry->getBitParameterName(2)); ?>*</th>
                                  <th><?php echo htmlspecialchars($latestPacketTelemetry->getBitParameterName(3)); ?>*</th>
                                  <th><?php echo htmlspecialchars($latestPacketTelemetry->getBitParameterName(4)); ?>*</th>
                                  <th><?php echo htmlspecialchars($latestPacketTelemetry->getBitParameterName(5)); ?>*</th>
                                  <th><?php echo htmlspecialchars($latestPacketTelemetry->getBitParameterName(6)); ?>*</th>
                                  <th><?php echo htmlspecialchars($latestPacketTelemetry->getBitParameterName(7)); ?>*</th>
                                  <th><?php echo htmlspecialchars($latestPacketTelemetry->getBitParameterName(8)); ?>*</th>
                              </tr>
                          </thead>
                          <tbody>
                          </tbody>
                      </table>
                  </div>

                  <div class="telemetry-subtable">
                      <div>
                          <div>
                              *Used Bit Sense:
                          </div>
                          <div>
                              <?php echo $latestPacketTelemetry->getBitSense(1); ?>
                              <?php echo $latestPacketTelemetry->getBitSense(2); ?>
                              <?php echo $latestPacketTelemetry->getBitSense(3); ?>
                              <?php echo $latestPacketTelemetry->getBitSense(4); ?>
                              <?php echo $latestPacketTelemetry->getBitSense(5); ?>
                              <?php echo $latestPacketTelemetry->getBitSense(6); ?>
                              <?php echo $latestPacketTelemetry->getBitSense(7); ?>
                              <?php echo $latestPacketTelemetry->getBitSense(8); ?>
                          </div>
                      </div>
                  </div>
                <?php endif; ?>
          <?php endif; ?>
        <?php endif; ?>

        <?php if (count($telemetryPackets) > 0) : ?>
            <br/>
            <ul>
                <li>The parameter names for the analog channels will be Value1, Value2, Value3 (up to Value5) if station has not sent a PARAM-packet that specifies the parameter names for each analog channel.</li>
                <li>Each analog value is a decimal number between 000 and 255 (according to APRS specifications). The receiver use the telemetry equation coefficientsto to restore the original sensor values. If no EQNS-packet with equation coefficients is sent we will show the values as is (this corresponds to the equation coefficients a=0, b=1 and c=0).<br/>The sent equation coefficients is used in the equation: a * value<sup>2</sup> + b * value + c.</li>
                <li>The units for the analog values will not be shown if station has not sent a UNIT-packet specifying what unit's to use.</li>
                <li>The parameter names for the digital bits will be Bit1, Bit2, Bit3 (up to Bit8) if station has not sent a PARAM-packet that specifies the parameter names for each digital bit.</li>
                <li>All bit labels will be named "On" if station has not sent a UNIT-packet that specifies the label of each bit.</li>
                <li>A bit is considered to be <b>On</b> when the bit is 1 if station has not sent a BITS-packet that specifies another "Bit sense" (a BITS-packet specify the state of the bits that match the BIT labels)</li>
            </ul>
        <?php endif; ?>

        <?php if (count($telemetryPackets) == 0) : ?>
            <p><i><b>No recent telemetry values.</b></i></p>
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

            $('.telemetrytime').each(function() {
                if ($(this).html().trim() != '' && !isNaN($(this).html().trim())) {
                    $(this).html(moment(new Date(1000 * $(this).html())).format('L LTSZ'));
                }
            });

            $('#telemetry-category').change(function () {
                loadView("/views/telemetry.php?id=<?php echo $station->id ?>&format=<?php echo $format; ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ;?>&category=" + $('#telemetry-category').val());
            });

            <?php if ($format=='table' && ($_GET['category'] ?? 1) == 1): ?>
              $('#telem-value-table').DataTable( {
                ajax: {
                  url: '/data/data.php?module=telemetry&command=getTelemetryValues&id=<?php echo $station->id ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ;?><?php if (isset($_GET['start'])): ?>&start=<?php echo $_GET['start'];?>&end=<?php echo $_GET['end'];?><?php endif;?>',
                  dataSrc: function (json) {
                    $("#dbtime").text(json.data.dbtime);
                    return json.data.values;
                  }
                },
                columns: [
                  { data: 'ts',
                    render: DataTable.render.datetime(),
                    width: '10em' },
                  { data: '1',
                    render: function(data) {
                        return data + ' <?php echo universalDataUnitConvert(round($latestPacketTelemetry->getValue(1), 2), $latestPacketTelemetry->getValueUnit(1))['unit']; ?>';
                      },
                    width: '3em' },
                  { data: '2',
                    render: function(data) {
                        return data + ' <?php echo universalDataUnitConvert(round($latestPacketTelemetry->getValue(2), 2), $latestPacketTelemetry->getValueUnit(2))['unit']; ?>';
                      },
                    width: '3em' },
                  { data: '3',
                    render: function(data) {
                      return data + ' <?php echo universalDataUnitConvert(round($latestPacketTelemetry->getValue(3), 2), $latestPacketTelemetry->getValueUnit(3))['unit']; ?>';
                      },
                    width: '3em' },
                  { data: '4',
                    render: function(data) {
                      return data + ' <?php echo universalDataUnitConvert(round($latestPacketTelemetry->getValue(4), 2), $latestPacketTelemetry->getValueUnit(4))['unit']; ?>';
                      },
                    width: '3em' },
                  { data: '5',
                    render: function(data) {
                      return data + ' <?php echo universalDataUnitConvert(round($latestPacketTelemetry->getValue(5), 2), $latestPacketTelemetry->getValueUnit(5))['unit']; ?>';
                      },
                    width: '3em' }
                ],
                 order: [[0, 'desc']],
              });
            <?php endif; ?>

            <?php if ($format=='table' && ($_GET['category'] ?? 1) == 2): ?>
              $('#telem-bit-table').DataTable( {
                ajax: {
                  url: '/data/data.php?module=telemetry&command=getTelemetryBits&id=<?php echo $station->id ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ;?><?php if (isset($_GET['start'])): ?>&start=<?php echo $_GET['start'];?>&end=<?php echo $_GET['end'];?><?php endif;?>',
                  dataSrc: function (json) {
                    $("#dbtime").text(json.data.dbtime);
                    return json.data.bits;
                  }
                },
                columns: [
                  { data: 'ts',
                    render: DataTable.render.datetime(),
                    width: '10em' },
                  { data: '1' },
                  { data: '2' },
                  { data: '3' },
                  { data: '4' },
                  { data: '5' },
                  { data: '6' },
                  { data: '7' },
                  { data: '8' }
                ],
                order: [[0, 'desc']],
              });
            <?php endif; ?>
            $("input[type=search]").css('padding', '1px');

            if (window.trackdirect) {
                <?php if ($station->latestConfirmedLatitude != null && $station->latestConfirmedLongitude != null) : ?>
                    window.trackdirect.addListener("map-created", function() {
                        if (!window.trackdirect.focusOnStation(<?php echo $station->id ?>, true)) {
                            window.trackdirect.setCenter(<?php echo $station->latestConfirmedLatitude ?>, <?php echo $station->latestConfirmedLongitude ?>);
                        }
                    });
                <?php endif; ?>
                window.trackdirect.addListener("trackdirect-init-done", function () {
                  window.liveData.start("<?php echo $station->name;?>", <?php echo $station->latestPacketTimestamp; ?>, 'telemcurrent');
                });
            }

            quikLink();
        });
    </script>
<?php endif; ?>
