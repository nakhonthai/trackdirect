<?php require dirname(__DIR__) . "../../includes/bootstrap.php"; ?>

<?php $station = StationRepository::getInstance()->getObjectById($_GET['id'] ?? null); ?>
<?php if ($station->isExistingObject()) : ?>

    <?php
        $maxDays = 10;
        if (!isAllowedToShowOlderData()) {
            $maxDays = 1;
        }

        $page = $_GET['page'] ?? 1;
        $rows = $_GET['rows'] ?? 25;
        $offset = ($page - 1) * $rows;
        $show = $_GET['show'] ?? 'message';

        $start_time = microtime(true);
        if ($show == 'message') {
          $packets = PacketRepository::getInstance()->getMessageObjectListByStationIdAndCall($station->id, $station->name, $rows, $offset, $maxDays);
          $count = PacketRepository::getInstance()->getNumberOfMessagesByStationIdAndCall($station->id, $station->name, $maxDays);
        } else if ($show == 'bulletin') {
          $packets = PacketRepository::getInstance()->getBulletinObjectListByStationId($station->id, $rows, $offset, $maxDays);
          $count = PacketRepository::getInstance()->getNumberOfBulletinsByStationId($station->id, $maxDays);
        }
        $dbtime = microtime(true) - $start_time;

        $pages = ceil($count / $rows);
        $titles = array('message' => 'Messages', 'bulletin' => 'Bulletins');
    ?>

    <title><?php echo $station->name; ?> Messages &amp; Bulletins</title>
    <div class="modal-inner-content">
        <div class="modal-inner-content-menu">
            <a class="tdlink" title="Overview" href="/views/overview.php?id=<?php echo $station->id ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ?? 0; ?>">Overview</a>
            <a class="tdlink" title="Statistics" href="/views/statistics.php?id=<?php echo $station->id ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ?? 0; ?>">Statistics</a>
            <a class="tdlink" title="Trail Chart" href="/views/trail.php?id=<?php echo $station->id ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ?? 0; ?>">Trail Chart</a>
            <a class="tdlink" title="Weather" href="/views/weather.php?id=<?php echo $station->id ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ?? 0; ?>">Weather</a>
            <a class="tdlink" title="Telemetry" href="/views/telemetry.php?id=<?php echo $station->id ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ?? 0; ?>">Telemetry</a>
            <a class="tdlink" title="Raw packets" href="/views/raw.php?id=<?php echo $station->id ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ?? 0; ?>">Raw Packets</a>
            <span>Messages &amp; Bulletins</span>
        </div>

        <div class="horizontal-line" style="margin:0">&nbsp;</div>

        <div class="modal-inner-content-menu" style="margin-left:25px;">
            <?php if ($show != 'message'): ?><a class="tdlink" href="/views/messages.php?id=<?php echo $station->id; ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ;?>&show=message"><?php echo $titles['message']; ?></a><?php else: ?><span><?php echo $titles['message']; ?></span><?php endif; ?>
            <?php if ($show != 'bulletin'): ?><a class="tdlink" href="/views/messages.php?id=<?php echo $station->id; ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ;?>&show=bulletin"><?php echo $titles['bulletin']; ?></a><?php else: ?><span><?php echo $titles['bulletin']; ?></span><?php endif; ?>
        </div>

        <div class="horizontal-line">&nbsp;</div>

        <p>
          <?php if ($count): ?>A total of <?php echo $count ?> <?php echo $show; ?>s have been found for station/object <b><?php echo $station->name; ?></b> from the past <?php echo $maxDays; ?> day(s).
          <?php else: ?>If no packets are shown the station/object has not sent any packets within the past <?php echo $maxDays; ?> day(s).<?php endif; ?>
          <?php if ($count): ?>This data took <?php echo round($dbtime, 3) ?> seconds to find in our database.<?php endif; ?>
        </p>

        <?php if ($count > 0): ?>
          <div class="form-container">
            <select id="raw-rows" style="float:left; margin-right: 5px;" class="pagination-rows">
                <option <?php echo ($rows == 25 ? 'selected' : ''); ?> value="25">25 rows</option>
                <option <?php echo ($rows == 50 ? 'selected' : ''); ?> value="50">50 rows</option>
                <option <?php echo ($rows == 100 ? 'selected' : ''); ?> value="100">100 rows</option>
                <option <?php echo ($rows == 200 ? 'selected' : ''); ?> value="200">200 rows</option>
                <option <?php echo ($rows == 300 ? 'selected' : ''); ?> value="300">300 rows</option>
            </select>
          </div>
        <?php endif; ?>
        <?php if ($pages > 1): ?>
            <div class="pagination">
              <a class="tdlink" href="/views/messages.php?id=<?php echo $station->id; ?>&show=<?php echo $show; ?>&imperialUnits=<?php echo ($_GET['imperialUnits'] ?? 1); ?>&rows=<?php echo $rows; ?>&page=1"><<</a>
              <?php for($i = max(1, $page - 3); $i <= min($pages, $page + 3); $i++) : ?>
              <a href="/views/messages.php?id=<?php echo $station->id; ?>&show=<?php echo $show; ?>&imperialUnits=<?php echo ($_GET['imperialUnits'] ?? 1); ?>&rows=<?php echo $rows; ?>&page=<?php echo $i; ?>" <?php echo ($i == $page ? 'class="tdlink active"': 'class="tdlink"')?>><?php echo $i ?></a>
              <?php endfor; ?>
              <a class="tdlink" href="/views/messages.php?id=<?php echo $station->id; ?>&show=<?php echo $show; ?>&imperialUnits=<?php echo ($_GET['imperialUnits'] ?? 1); ?>&rows=<?php echo $rows; ?>&page=<?php echo $pages; ?>">>></a>
            </div>
        <?php endif; ?>


        <?php if ($show == 'message'): ?>
          <?php if ($count > 0): ?>
          <div class="datagrid datagrid-raw" style="max-width:1000px;">
            <table>
                <thead>
                  <tr>
                      <th>Time</th>
                      <th>From</th>
                      <th>To</th>
                      <th>Message</th>
                      <th>Path</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach (array_slice($packets, 0, $rows) as $packet) : ?>
                  <tr>
                      <td class="raw-packet-timestamp"><?php echo $packet->timestamp; ?></td>
                      <td><a href="overview.php?id=<?php echo $packet->station_id; ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ?? 0; ?>"><?php echo htmlspecialchars($packet->getStationObject()->name); ?></a></td>
                      <td><a href="overview.php?c=<?php echo urlencode($packet->to_call); ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ?? 0; ?>"><?php echo htmlspecialchars($packet->to_call); ?></a></td>
                      <td><?php echo htmlspecialchars($packet->comment); ?></td>
                      <td class="parsepkt"><?php echo htmlspecialchars($packet->rawPath); ?></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
            </table>
          </div>
          <?php else: ?>
            <p><i><b>No recent messages found.</b></i></p>
          <?php endif; ?>
        <?php endif; ?>

        <?php if ($show == 'bulletin'): ?>
          <?php if ($count > 0): ?>
          <div class="datagrid datagrid-raw" style="max-width:1000px;">
            <table>
                <thead>
                  <tr>
                      <th>Time</th>
                      <th>From</th>
                      <th>To Group</th>
                      <th>Message</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach (array_slice($packets, 0, $rows) as $packet) : ?>
                  <?php preg_match('/::(.*?):/i', $packet->raw, $matches); ?>
                  <tr>
                      <td class="raw-packet-timestamp"><?php echo $packet->timestamp; ?></td>
                      <td><a href="overview.php?id=<?php echo $packet->station_id; ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ?? 0; ?>"><?php echo htmlspecialchars($packet->getStationObject()->name); ?></a></td>
                      <td><?php echo htmlspecialchars($matches[1]); ?></td>
                      <td><?php echo htmlspecialchars($packet->comment); ?></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
            </table>
          </div>
          <?php else: ?>
            <p><i><b>No recent bulletins found.</b></i></p>
          <?php endif; ?>
      <?php endif; ?>

    <script>
        $(document).ready(function() {
            var locale = window.navigator.userLanguage || window.navigator.language;
            moment.locale(locale);

            $('.raw-packet-timestamp').each(function() {
                if ($(this).html().trim() != '' && !isNaN($(this).html().trim())) {
                    $(this).html(moment(new Date(1000 * $(this).html())).format('L LTSZ'));
                }
            });

            $('#raw-rows').change(function () {
                loadView("/views/messages.php?id=<?php echo $station->id ?>&show=<?php echo $show; ?>&imperialUnits=<?php echo ($_GET['imperialUnits'] ?? 1); ?>&rows=" + $('#raw-rows').val() + "&page=1");
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
                  p3[k] = '<b><a href="overview.php?c='+encodeURI(v.replace('*', ''))+'&imperialUnits=<?php echo $_GET['imperialUnits'] ?? 0; ?>">'+v+'</a></b>';
                }
              });
              $(this).html('<b><a href="overview.php?id=<?php echo $station->id ?>&imperialUnits=<?php echo $_GET['imperialUnits'] ?? 0; ?>">'+p1[0]+'</a></b>&gt;'+p3.join(',')+':' +p2.join(':'));
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
