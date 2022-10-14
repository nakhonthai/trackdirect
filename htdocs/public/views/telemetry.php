<?php require dirname(__DIR__) . "../../includes/bootstrap.php"; ?>

<?php $station = StationRepository::getInstance()->getObjectById($_GET['id'] ?? null); ?>
<?php if ($station->isExistingObject()) : ?>
    <?php
        $maxDays = 10;
        if (!isAllowedToShowOlderData()) {
            $maxDays = 1;
        }
        $format = $_GET['format'] ?? 'current';

        $start = $_GET['start'] ?? time()-864000;
        $end = $_GET['end'] ?? time();

        $page = $_GET['page'] ?? 1;
        $rows = $_GET['rows'] ?? 25;
        $offset = ($page - 1) * $rows;

        $start_time = microtime(true);
        if ($format == 'table') {
          $telemetryPackets = PacketTelemetryRepository::getInstance()->getLatestObjectListByStationId($station->id, $rows, $offset, $maxDays, 'asc', $start, $end);
          $latestPacketTelemetry = (count($telemetryPackets) > 0 ? $telemetryPackets[0] : new PacketTelemetry(null));
          $count = PacketTelemetryRepository::getInstance()->getLatestNumberOfPacketsByStationId($station->id, $maxDays, $start, $end);
          $pages = ceil($count / $rows);
        } else {
          $telemetryPackets = PacketTelemetryRepository::getInstance()->getLatestObjectListByStationId($station->id, 1, 0, $maxDays);
          $latestPacketTelemetry = (count($telemetryPackets) > 0 ? $telemetryPackets[0] : new PacketTelemetry(null));
          $count = 1;
          $pages = 0;
        }
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
                  <span style="float:left;">Displaying <?php echo $offset+1; ?> - <?php echo ($offset+$rows < $count ? $offset+$rows : $count); ?> of <?php echo $count ?> telemetry records. Data retrieved in <?php echo round($dbtime, 3) ?> seconds.</span>
                <?php else: ?>
                  <span style="float:left;">Displaying latest telemetry as of <span id="latest-timestamp" class="telemetrytime" style="font-weight:bold;"><?php echo ($telemetryPackets[0]->wxRawTimestamp != null?$telemetryPackets[0]->wxRawTimestamp:$telemetryPackets[0]->timestamp); ?></span>. Data retrieved in <?php echo round($dbtime, 3) ?> seconds.</span>
                <?php endif; ?>
            </div>
            <?php if ($format == 'current'): ?><span style="float:right;"><img src="/public/images/dotColor3.svg" style="height:24px;vertical-align:middle;" id="live-img" /><span id="live-status" style="vertical-align:middle;">Waiting for connection...</span></span><?php endif; ?>

            <?php if ($format != 'current'): ?>
              <form id="telemhistory-form" style="float:right;line-height: 28px">
                Show
                <select id="telemetry-rows" class="pagination-rows">
                    <option <?php echo ($rows == 25 ? 'selected' : ''); ?> value="25">25</option>
                    <option <?php echo ($rows == 50 ? 'selected' : ''); ?> value="50">50</option>
                    <option <?php echo ($rows == 100 ? 'selected' : ''); ?> value="100">100</option>
                    <option <?php echo ($rows == 200 ? 'selected' : ''); ?> value="200">200</option>
                    <option <?php echo ($rows == 300 ? 'selected' : ''); ?> value="300">300</option>
                </select>
                rows of
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
                <div class="datagrid datagrid-telemetry1" style="max-width:1000px;">
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

            <?php if ($pages > 1 && $format == 'table'): ?>
                <div class="pagination">
                  <a class="tdlink" href="/views/telemetry.php?id=<?php echo $station->id; ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ?? 0; ?>&category=<?php echo ($_GET['category'] ?? 1); ?>&format=<?php echo $format; ?>&start=<?php echo $start; ?>&end=<?php echo $end; ?>&rows=<?php echo $rows; ?>&page=1"><<</a>
                  <?php for($i = max(1, $page - 3); $i <= min($pages, $page + 3); $i++) : ?>
                  <a href="/views/telemetry.php?id=<?php echo $station->id; ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ?? 0; ?>&category=<?php echo ($_GET['category'] ?? 1); ?>&format=<?php echo $format; ?>&start=<?php echo $start; ?>&end=<?php echo $end; ?>&rows=<?php echo $rows; ?>&page=<?php echo $i; ?>" <?php echo ($i == $page ? 'class="tdlink active"': 'class="tdlink"')?>><?php echo $i ?></a>
                  <?php endfor; ?>
                  <a class="tdlink" href="/views/telemetry.php?id=<?php echo $station->id; ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ?? 0; ?>&category=<?php echo ($_GET['category'] ?? 1); ?>&format=<?php echo $format; ?>&start=<?php echo $start; ?>&end=<?php echo $end; ?>&rows=<?php echo $rows; ?>&page=<?php echo $pages; ?>">>></a>
                </div>
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
              <div class="datagrid datagrid-telemetry1" style="max-width:1000px;">
                  <table>
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

                      <?php foreach ($telemetryPackets as $packetTelemetry) : ?>

                          <tr>
                              <td class="telemetrytime">
                                  <?php echo ($packetTelemetry->wxRawTimestamp != null?$packetTelemetry->wxRawTimestamp:$packetTelemetry->timestamp); ?>
                              </td>
                            <?php for ($x = 1; $x <= 5; $x++): ?>
                              <?php $converted = universalDataUnitConvert(round($packetTelemetry->getValue($x), 2), $packetTelemetry->getValueUnit($x)); ?>
                              <td>
                                  <?php if ($packetTelemetry->{"val$x"}  !== null) : ?>
                                      <?php echo $converted['value']; ?> <?php echo htmlspecialchars($converted['unit']); ?>
                                  <?php else : ?>
                                      -
                                  <?php endif; ?>
                              </td>
                            <?php endfor; ?>
                          </tr>

                      <?php endforeach; ?>
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
                  <div class="datagrid datagrid-telemetry2" style="max-width:1000px;">
                      <table>
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
                          <?php foreach ($telemetryPackets as $i => $packetTelemetry) : ?>
                              <?php if ($packetTelemetry->bits !== null && $i >= 2 ) : ?>
                              <tr>
                                  <td class="telemetrytime">
                                      <?php echo $packetTelemetry->timestamp; ?>
                                  </td>
                                  <td>
                                      <div class="<?php echo ($packetTelemetry->getBit(1) == 1?'telemetry-biton':'telemetry-bitoff'); ?>">
                                          <?php echo htmlspecialchars($packetTelemetry->getBitLabel(1)); ?>
                                      </div>
                                  </td>
                                  <td>
                                      <div class="<?php echo ($packetTelemetry->getBit(2) == 1?'telemetry-biton':'telemetry-bitoff'); ?>">
                                          <?php echo htmlspecialchars($packetTelemetry->getBitLabel(2)); ?>
                                      </div>
                                  </td>
                                  <td>
                                      <div class="<?php echo ($packetTelemetry->getBit(3) == 1?'telemetry-biton':'telemetry-bitoff'); ?>">
                                          <?php echo htmlspecialchars($packetTelemetry->getBitLabel(3)); ?>
                                      </div>
                                  </td>
                                  <td>
                                      <div class="<?php echo ($packetTelemetry->getBit(4) == 1?'telemetry-biton':'telemetry-bitoff'); ?>">
                                          <?php echo htmlspecialchars($packetTelemetry->getBitLabel(4)); ?>
                                      </div>
                                  </td>
                                  <td>
                                      <div class="<?php echo ($packetTelemetry->getBit(5) == 1?'telemetry-biton':'telemetry-bitoff'); ?>">
                                          <?php echo htmlspecialchars($packetTelemetry->getBitLabel(5)); ?>
                                      </div>
                                  </td>
                                  <td>
                                      <div class="<?php echo ($packetTelemetry->getBit(6) == 1?'telemetry-biton':'telemetry-bitoff'); ?>">
                                          <?php echo htmlspecialchars($packetTelemetry->getBitLabel(6)); ?>
                                      </div>
                                  </td>
                                  <td>
                                      <div class="<?php echo ($packetTelemetry->getBit(7) == 1?'telemetry-biton':'telemetry-bitoff'); ?>">
                                          <?php echo htmlspecialchars($packetTelemetry->getBitLabel(7)); ?>
                                      </div>
                                  </td>
                                  <td>
                                      <div class="<?php echo ($packetTelemetry->getBit(8) == 1?'telemetry-biton':'telemetry-bitoff'); ?>">
                                          <?php echo htmlspecialchars($packetTelemetry->getBitLabel(8)); ?>
                                      </div>
                                  </td>
                              </tr>
                              <?php endif; ?>
                          <?php endforeach; ?>
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
                loadView("/views/telemetry.php?id=<?php echo $station->id ?>&format=<?php echo $format; ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ;?>&category=" + $('#telemetry-category').val() + "&rows=" + $('#telemetry-rows').val() + "&page=1");
            });

            $('#telemetry-rows').change(function () {
                loadView("/views/telemetry.php?id=<?php echo $station->id ?>&format=<?php echo $format; ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ;?>&category=" + $('#telemetry-category').val() + "&rows=" + $('#telemetry-rows').val() + "&page=1");
            });

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

        });
    </script>
<?php endif; ?>
