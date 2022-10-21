<?php require "../includes/bootstrap.php"; ?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <title><?php echo getWebsiteConfig('title'); ?></title>

    <!-- Mobile meta -->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"/>
    <meta name="apple-mobile-web-app-capable" content="yes"/>
    <meta name="mobile-web-app-capable" content="yes">

    <!-- JS libs used by this website (not a dependency for the track direct js lib) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mobile-detect/1.4.5/mobile-detect.min.js" integrity="sha512-1vJtouuOb2tPm+Jh7EnT2VeiCoWv0d7UQ8SGl/2CoOU+bkxhxSX4gDjmdjmbX4OjbsbCBN+Gytj4RGrjV3BLkQ==" crossorigin="anonymous"></script>
    <script type="text/javascript" src="//www.gstatic.com/charts/loader.js"></script>

    <!-- Stylesheets used by this website (not a dependency for the track direct js lib) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Track Direct js dependencies -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.1/jquery.min.js" integrity="sha512-aVKKRRi/Q/YV+4mjoKBsE4x3H+BkegoM/em46NNlCqNTmUYADjBbeNefNxYV7giUp0VxICtqdrbqU7iVaeZNXA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment-with-locales.min.js" integrity="sha512-42PE0rd+wZ2hNXftlM78BSehIGzezNeQuzihiBCvUEB3CVxHvsShF86wBWwQORNxNINlBPuq7rG4WWhNiTVHFg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/autolinker/3.15.0/Autolinker.min.js" referrerpolicy="no-referrer"></script>
    <script src="/js/convex-hull.js" crossorigin="anonymous"></script>

    <!-- JQuery UI -->
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

    <!-- JQuery Datatables -->
    <script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.12.1/r-2.3.0/datatables.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.12.1/r-2.3.0/datatables.min.css"/> 

    <!-- Graphing dependencies -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js" integrity="sha512-ElRFoEQdI5Ht6kZvyzXhYG9NqjtkmlkfYk0wr6wHxU9JEHakS7UJZNeml5ALk+8IKlU6jDgMabC3vkumRokgJA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chartjs-adapter-moment/1.0.0/chartjs-adapter-moment.min.js" integrity="sha512-oh5t+CdSBsaVVAvxcZKy3XJdP7ZbYUBSRCXDTVn0ODewMDDNnELsrG9eDm8rVZAQg7RsDD/8K3MjPAFB13o6eA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <!-- Map api javascripts and related dependencies -->
    <?php $mapapi = $_GET['mapapi'] ?? 'leaflet'; ?>
    <?php if ($mapapi == 'google') : ?>
      <?php if (getWebsiteConfig('google_key') != null) : ?>
          <script type="text/javascript" src="//maps.googleapis.com/maps/api/js?key=<?php echo getWebsiteConfig('google_key'); ?>&libraries=visualization,geometry"></script>
      <?php else : ?>
          <script type="text/javascript" src="//maps.googleapis.com/maps/api/js?libraries=visualization,geometry"></script>
      <?php endif; ?>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/OverlappingMarkerSpiderfier/1.0.3/oms.min.js" integrity="sha512-/3oZy+rGpR6XGen3u37AEGv+inHpohYcJupz421+PcvNWHq2ujx0s1QcVYEiSHVt/SkHPHOlMFn5WDBb/YbE+g==" crossorigin="anonymous"></script>

    <?php elseif ($mapapi == 'leaflet' || $mapapi == 'leaflet-vector'): ?>
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.8.0/leaflet.min.css" integrity="sha512-oIQ0EBio8LJupRpgmDsIsvm0Fsr6c3XNHLB7at5xb+Cf6eQuCX9xuX8XXGRIcokNgdqL1ms7nqbQ6ryXMGxXpg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
      <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.8.0/leaflet.min.js" integrity="sha512-TL+GX2RsOUlTndpkgHVnSQ9r6zldqHzfyECrdabkpucdFroZ3/HAhMmP2WYaPjsJCoot+0McmdPOLjmmicG9qg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

      <?php if ($mapapi == 'leaflet-vector'): ?>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/mapbox-gl/1.13.1/mapbox-gl.min.css" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/mapbox-gl/1.13.1/mapbox-gl.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/mapbox-gl-leaflet/0.0.15/leaflet-mapbox-gl.min.js"></script>
      <?php endif; ?>

      <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet-providers/1.13.0/leaflet-providers.min.js" integrity="sha512-5EYsvqNbFZ8HX60keFbe56Wr0Mq5J1RrA0KdVcfGDhnjnzIRsDrT/S3cxdzpVN2NGxAB9omgqnlh4/06TvWCMw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.heat/0.2.0/leaflet-heat.js" integrity="sha512-KhIBJeCI4oTEeqOmRi2gDJ7m+JARImhUYgXWiOTIp9qqySpFUAJs09erGKem4E5IPuxxSTjavuurvBitBmwE0w==" crossorigin="anonymous"></script>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/OverlappingMarkerSpiderfier-Leaflet/0.2.6/oms.min.js" integrity="sha512-V8RRDnS4BZXrat3GIpnWx+XNYBHQGdK6nKOzMpX4R0hz9SPWt7fltGmmyGzUkVFZUQODO1rE+SWYJJkw3SYMhg==" crossorigin="anonymous"></script>
    <?php endif; ?>

    <!-- Track Direct jslib -->
    <script type="text/javascript" src="/js/trackdirect.min.js"></script>
    <script type="text/javascript" src="/js/main.js"></script>
    <link rel="stylesheet" href="/css/main.css">

    <script>
      const dbstart = moment("<?php echo getWebsiteConfig('database_start_date') ?>");

      // Start everything!!!
      $(document).ready(function() {
        google.charts.load('current', {'packages':['corechart', 'timeline']});

        var options = {};
        options['isMobile'] = false;
        options['useImperialUnit'] = <?php echo (isImperialUnitUser() ? 'true': 'false'); ?>;
        options['coverageDataUrl'] = '/data/coverage.php';
        options['coveragePercentile'] = <?php echo (getWebsiteConfig('coverage_percentile') ?? "95"); ?>;
        options['defaultTimeLength'] = 60; // In minutes

        var md = new MobileDetect(window.navigator.userAgent);
        if (md.mobile() !== null) {
            options['isMobile'] = true;
        }

        options['time'] =       "<?php echo $_GET['time'] ?? '' ?>";        // How many minutes of history to show
        options['center'] =     "<?php echo $_GET['center'] ?? '' ?>";      // Position to center on (for example "46.52108,14.63379")
        options['zoom'] =       "<?php echo $_GET['zoom'] ?? '' ?>";        // Zoom level
        options['timetravel'] = "<?php echo $_GET['timetravel'] ?? '' ?>";  // Unix timestamp to travel to
        options['maptype'] =    "<?php echo $_GET['maptype'] ?? '' ?>";     // May be "roadmap", "terrain" or "satellite"
        options['mid'] =        "<?php echo $_GET['mid'] ?? '' ?>";         // Render map from "Google My Maps" (requires https)

        options['filters'] = {};
        options['filters']['sid'] = "<?php echo $_GET['sid'] ?? '' ?>";         // Station id to filter on
        options['filters']['sname'] = "<?php echo $_GET['sname'] ?? '' ?>";     // Station name to filter on
        options['filters']['sidlist'] = "<?php echo $_GET['sidlist'] ?? '' ?>";     // Station id list to filter on (colon separated)
        options['filters']['snamelist'] = "<?php echo $_GET['snamelist'] ?? '' ?>"; // Station name list to filter on (colon separated)

        // Tell jslib which html element to use to show connection status and mouse cordinates
        options['statusContainerElementId'] = 'status-container';
        options['cordinatesContainerElementId'] = 'cordinates-container';

        // Use this setting so enlarge some symbols (for example airplanes when using OGN as data source)
        //options['symbolsToScale'] = [[88,47],[94,null]];

        // Set this setting to false if you want to stop animations
        options['animate'] = true;

        // Use Stockholm as default position (will be used if we fail to fetch location from ip-location service)
        options['defaultLatitude'] = '59.30928';
        options['defaultLongitude'] = '18.08830';

        // Tip: request position from some ip->location service (https://freegeoip.app/json and https://ipapi.co/json is two examples)
        $.getJSON('https://ipapi.co/json', function(data) {
          if (data.latitude && data.longitude) {
            options['defaultLatitude'] = data.latitude;
            options['defaultLongitude'] = data.longitude;
          }
        }).fail(function() {
          console.log('Failed to fetch location, using default location');
        }).always(function() {
          <?php if ($mapapi == 'leaflet-vector') : ?>
            options['mapboxGLStyle'] = "https://api.maptiler.com/maps/bright/style.json?optimize=true&key=<?php echo getWebsiteConfig('maptiler_key'); ?>";
            options['mapboxGLAttribution'] = 'Map &copy; <a href="https://www.maptiler.com">MapTiler</a>, OpenStreetMap contributors';
          <?php endif; ?>

          <?php if ($mapapi == 'leaflet') : ?>
            // We are using Leaflet -- read about leaflet-providers and select your favorite maps
            // https://leaflet-extras.github.io/leaflet-providers/preview/

            // Make sure to read the license requirements for each provider before launching a public website
            // https://wiki.openstreetmap.org/wiki/Tile_servers

            // Many providers require a map api key or similar, the following is an example for HERE
            L.TileLayer.Provider.providers['HERE'].options['app_id'] = '<?php echo getWebsiteConfig('here_app_id'); ?>';
            L.TileLayer.Provider.providers['HERE'].options['app_code'] = '<?php echo getWebsiteConfig('here_app_code'); ?>';

            options['supportedMapTypes'] = {};
            options['supportedMapTypes']['roadmap'] = "<?php echo getWebsiteConfig('leaflet_raster_tile_roadmap'); ?>";
            options['supportedMapTypes']['terrain'] = "<?php echo getWebsiteConfig('leaflet_raster_tile_terrain'); ?>";
            options['supportedMapTypes']['satellite'] = "<?php echo getWebsiteConfig('leaflet_raster_tile_satellite'); ?>";
          <?php endif; ?>

          // host is used to create url to /heatmaps and /images
          options['host'] = "<?php echo $_SERVER['HTTP_HOST']; ?>";

          var supportsWebSockets = 'WebSocket' in window || 'MozWebSocket' in window;
          if (supportsWebSockets) {
            <?php if (getWebsiteConfig('websocket_url') != null) : ?>
              var wsServerUrl = "<?php echo getWebsiteConfig('websocket_url'); ?>";
            <?php else : ?>
              var wsServerUrl = 'ws://<?php echo $_SERVER['HTTP_HOST']; ?>:9000/ws';
            <?php endif; ?>
            var mapElementId = 'map-container';

            trackdirect.init(wsServerUrl, mapElementId, options);

            trackdirect.addListener("trackdirect-init-done", function () {
              trackdirect._websocket.addListener("server-timestamp-response", function (data) {
               $('#svrclock').text(moment(new Date(1000 * data.timestamp)).format('LTS'));
               liveData.init();
              });
            });
          } else {
              alert('This service require HTML 5 features to be able to feed you APRS data in real-time. Please upgrade your browser.');
          }
        });
      });
    </script>
  </head>
  <body>
      <div class="topnav" id="tdTopnav">
          <a  style="background-color: #af7a4c; color: white;"
              href=""
              onclick="
                  if (location.protocol != 'https:') {
                      trackdirect.setCenter(); // Will go to default position
                  } else {
                      trackdirect.setMapLocationByGeoLocation(
                          function(errorMsg) {
                              var msg = 'We failed to determine your current location by using HTML 5 Geolocation functionality';
                              if (typeof errorMsg !== 'undefined' && errorMsg != '') {
                                  msg += ' (' + errorMsg + ')';
                              }
                              msg += '.';
                              alert(msg);
                          },
                          function() {},
                          5000
                      );
                  }
                  return false;"
              title="Go to my current position">
              My position
          </a>

          <div class="dropdown">
              <button class="dropbtn">Tail length
                  <i class="fa fa-caret-down"></i>
              </button>
              <div class="dropdown-content" id="tdTopnavTimelength">
                  <a href="javascript:void(0);" onclick="trackdirect.setTimeLength(10); $('#tdTopnavTimelength>a').removeClass('dropdown-content-checkbox-active'); $(this).addClass('dropdown-content-checkbox-active');" class="dropdown-content-checkbox">10 minutes</a>
                  <a href="javascript:void(0);" onclick="trackdirect.setTimeLength(30); $('#tdTopnavTimelength>a').removeClass('dropdown-content-checkbox-active'); $(this).addClass('dropdown-content-checkbox-active');" class="dropdown-content-checkbox">30 minutes</a>
                  <a href="javascript:void(0);" id="tdTopnavTimelengthDefault" onclick="trackdirect.setTimeLength(60); $('#tdTopnavTimelength>a').removeClass('dropdown-content-checkbox-active'); $(this).addClass('dropdown-content-checkbox-active');" class="dropdown-content-checkbox">1 hour</a>
                  <a href="javascript:void(0);" onclick="trackdirect.setTimeLength(180); $('#tdTopnavTimelength>a').removeClass('dropdown-content-checkbox-active'); $(this).addClass('dropdown-content-checkbox-active');" class="dropdown-content-checkbox">3 hours</a>
                  <a href="javascript:void(0);" onclick="trackdirect.setTimeLength(360); $('#tdTopnavTimelength>a').removeClass('dropdown-content-checkbox-active'); $(this).addClass('dropdown-content-checkbox-active');" class="dropdown-content-checkbox">6 hours</a>
                  <a href="javascript:void(0);" onclick="trackdirect.setTimeLength(720); $('#tdTopnavTimelength>a').removeClass('dropdown-content-checkbox-active'); $(this).addClass('dropdown-content-checkbox-active');" class="dropdown-content-checkbox dropdown-content-checkbox-only-filtering dropdown-content-checkbox-hidden">12 hours</a>
                  <a href="javascript:void(0);" onclick="trackdirect.setTimeLength(1080); $('#tdTopnavTimelength>a').removeClass('dropdown-content-checkbox-active'); $(this).addClass('dropdown-content-checkbox-active');" class="dropdown-content-checkbox dropdown-content-checkbox-only-filtering dropdown-content-checkbox-hidden">18 hours</a>
                  <a href="javascript:void(0);" onclick="trackdirect.setTimeLength(1440); $('#tdTopnavTimelength>a').removeClass('dropdown-content-checkbox-active'); $(this).addClass('dropdown-content-checkbox-active');" class="dropdown-content-checkbox dropdown-content-checkbox-only-filtering dropdown-content-checkbox-hidden">24 hours</a>
              </div>
          </div>

          <div class="dropdown">
              <button class="dropbtn">Map API
                  <i class="fa fa-caret-down"></i>
              </button>
              <div class="dropdown-content">
                  <?php if (getWebsiteConfig('google_key') != null) : ?>
                      <a href="/?mapapi=google" title="Switch to Google Maps" <?= ($mapapi=="google"?"class='dropdown-content-checkbox dropdown-content-checkbox-active'":"class='dropdown-content-checkbox'") ?>>Google Maps API</a>
                  <?php endif; ?>
                  <a href="/?mapapi=leaflet" title="Switch to Leaflet with raster tiles" <?= ($mapapi=="leaflet"?"class='dropdown-content-checkbox  dropdown-content-checkbox-active'":"class='dropdown-content-checkbox'") ?>>Leaflet - Raster Tiles</a>
                  <?php if (getWebsiteConfig('maptiler_key') != null) : ?>
                      <a href="/?mapapi=leaflet-vector" title="Switch to Leaflet with vector tiles" <?= ($mapapi=="leaflet-vector"?"class='dropdown-content-checkbox dropdown-content-checkbox-active'":"class='dropdown-content-checkbox'") ?>>Leaflet - Vector Tiles</a>
                  <?php endif; ?>
              </div>
          </div>

          <?php if ($mapapi != 'leaflet-vector') : ?>
          <div class="dropdown">
              <button class="dropbtn">Map Type
                  <i class="fa fa-caret-down"></i>
              </button>
              <div class="dropdown-content" id="tdTopnavMapType">
                  <a href="javascript:void(0);" onclick="trackdirect.setMapType('roadmap'); $('#tdTopnavMapType>a').removeClass('dropdown-content-checkbox-active'); $(this).addClass('dropdown-content-checkbox-active');" class="dropdown-content-checkbox dropdown-content-checkbox-active">Roadmap</a>
                  <a href="javascript:void(0);" onclick="trackdirect.setMapType('terrain'); $('#tdTopnavMapType>a').removeClass('dropdown-content-checkbox-active'); $(this).addClass('dropdown-content-checkbox-active');" class="dropdown-content-checkbox">Terrain/Outdoors</a>
                  <?php if ($mapapi == 'google' || getWebsiteConfig('leaflet_raster_tile_satellite') != null) : ?>
                  <a href="javascript:void(0);" onclick="trackdirect.setMapType('satellite'); $('#tdTopnavMapType>a').removeClass('dropdown-content-checkbox-active'); $(this).addClass('dropdown-content-checkbox-active');" class="dropdown-content-checkbox">Satellite</a>
                  <?php endif; ?>
              </div>
          </div>
          <?php endif; ?>

          <div class="dropdown">
              <button class="dropbtn">Settings
                  <i class="fa fa-caret-down"></i>
              </button>
	<div class="dropdown-content" id="tdTopnavSettings">
                  <a href="javascript:void(0);" onclick="trackdirect.toggleImperialUnits(); $(this).toggleClass('dropdown-content-checkbox-active');" class="dropdown-content-checkbox <?php echo (isImperialUnitUser()?'dropdown-content-checkbox-active':''); ?>" title="Switch to imperial units">Use imperial units</a>
                  <a href="javascript:void(0);" onclick="trackdirect.toggleStationaryPositions(); $(this).toggleClass('dropdown-content-checkbox-active');" class="dropdown-content-checkbox" title="Hide stations that is not moving">Hide not moving stations</a>

                  <a href="javascript:void(0);" onclick="trackdirect.toggleInternetPositions(); $(this).toggleClass('dropdown-content-checkbox-active');" class="dropdown-content-checkbox" title="Hide stations that sends packet using TCP/UDP">Hide Internet stations</a>
                  <a href="javascript:void(0);" onclick="trackdirect.toggleCwopPositions(); $(this).toggleClass('dropdown-content-checkbox-active');" class="dropdown-content-checkbox" title="Hide CWOP weather stations">Hide CWOP stations</a>
                  <a href="javascript:void(0);" onclick="trackdirect.toggleOgnPositions(); $(this).toggleClass('dropdown-content-checkbox-active');" class="dropdown-content-checkbox" title="Hide OGN stations">Hide OGN stations</a>
                  <a href="javascript:void(0);" onclick="trackdirect.toggleCbAprsPositions(); $(this).toggleClass('dropdown-content-checkbox-active');" class="dropdown-content-checkbox" title="Hide CBAPRS stations">Hide CBAPRS stations</a>

                  <a href="javascript:void(0);" onclick="trackdirect.toggleOgflymPositions(); $(this).toggleClass('dropdown-content-checkbox-active');" class="dropdown-content-checkbox" title="Hide model airplanes (OGFLYM)">Hide model airplanes (OGFLYM)</a>
                  <a href="javascript:void(0);" onclick="trackdirect.toggleUnknownPositions(); $(this).toggleClass('dropdown-content-checkbox-active');" class="dropdown-content-checkbox" title="Hide unknown aircrafts">Hide unknown aircrafts</a>
              </div>
          </div>

          <div class="dropdown">
              <button class="dropbtn">Other
                  <i class="fa fa-caret-down"></i>
              </button>
              <div class="dropdown-content">

                  <a href="/views/search.php"
		class="tdlink"
                      onclick="$(this).attr('href', '/views/search.php?imperialUnits=' + (trackdirect.isImperialUnits()?'1':'0'))"
                      title="Search for a station/vehicle here!">
                      Station search
                  </a>

                  <a href="/views/latest.php"
		class="tdlink"
                      onclick="$(this).attr('href', '/views/latest.php?imperialUnits=' + (trackdirect.isImperialUnits()?'1':'0'))"
                      title="List latest heard stations!">
                      Latest heard
                  </a>

                  <a href="javascript:void(0);"
                      onclick="$('#modal-timetravel').show();"
                      title="Select date and time to show what the map looked like then">
                      Travel in time
                  </a>

                  <a class="triple-notselected" href="#" onclick="trackdirect.togglePHGCircles(); return false;" title="Show PHG cirlces, first click will show half PGH circles and second click will show full PHG circles.">
                      Toggle PHG circles
                  </a>
              </div>
          </div>

          <div class="dropdown">
              <button class="dropbtn">About
                  <i class="fa fa-caret-down"></i>
              </button>
              <div class="dropdown-content">
                <a href="/views/about.php"
                    class="tdlink"
                    title="More about this website!">
                    About
                </a>
                <a href="/views/faq.php"
                    class="tdlink"
                    title="Frequently asked questions.">
                    FAQ
                </a>
                <a href="/views/site_statistics.php"
                    class="tdlink"
                    title="Site database information!">
                    Statistics
                </a>
                <?php if (getWebsiteConfig('aprs_is_status_url')): ?>
                <a href="/views/server_health.php?server=aprs"
                    class="tdlink"
                    title="APRS-IS (aprsc) Server Status">
                    APRS-IS Server Status
                </a>
                <?php endif; ?>
                <?php if (getWebsiteConfig('cwop_is_status_url')): ?>
                <a href="/views/server_health.php?server=cwop"
                    class="tdlink"
                    title="CWOP (aprsc) Server Status">
                    CWOP-IS Server Status
                </a>
              <?php endif; ?>
              <?php if (getWebsiteConfig('ogn_is_status_url')): ?>
              <a href="/views/server_health.php?server=ogn"
                  class="tdlink"
                  title="OGN (aprsc) Server Status">
                  OGN-IS Server Status
              </a>
              <?php endif; ?>
              <?php if (getWebsiteConfig('cbaprs_is_status_url')): ?>
              <a href="/views/server_health.php?server=cbaprs"
                  class="tdlink"
                  title="CBAPRS (aprsc) Server Status">
                  CBAPRS-IS Server Status
              </a>
              <?php endif; ?>
              </div>
          </div>
          <div class="dropdown">
            <form method="get" id="hdr-search-form" action="">
                <input type="hidden" name="seconds" id="hdr-search-form-seconds" value="0" />
                <input type="text" style="width: 130px;padding-left:10px;margin-top:8px;margin-left:10px;height:20px;text-transform:uppercase;" id="hdr-search-form-q" autocomplete="off" spellcheck="false" autocorrect="off" name="q" placeholder="Callsign search..." title="Search for a station/vehicle here!">
                <input type="submit" value="Go" style="margin-top:8px;line-height:0px;padding-left:6px;padding-right:6px" />
            </form>
          </div>

          <a class="tdlink" id="svrclock" style="float:right">00:00:00</a>

          <a href="javascript:void(0);" class="icon" onclick="toggleTopNav()">&#9776;</a>
      </div>

      <div id="map-container"></div>

      <div id="footer">&copy; 2022 <?php echo getWebsiteConfig('owner_name'); ?>.   Based on <a target="_blank" href="https://www.aprsdirect.com">APRS Track Direct</a></div>

      <div id="right-container">
          <div id="right-container-info">
              <div id="status-container"></div>
              <div id="cordinates-container"></div>
          </div>

          <div id="right-container-filtered">
              <div id="right-container-filtered-content"></div>
              <a href="#" onclick="trackdirect.filterOnStationId([]); return false;">reset</a>
          </div>

          <div id="right-container-timetravel">
              <img src="/images/clock.png" />Time Travel Active
              <div id="right-container-timetravel-content"></div>
              <a href="#" onclick="trackdirect.setTimeTravelTimestamp(0); $('#right-container-timetravel').hide(); return false;">reset</a>
          </div>
      </div>

      <div id="td-modal" class="modal">
          <?php $view = getView($_GET['view']); ?>
          <?php if (!$view): ?><script>document.getElementById('td-modal').style.display = 'none';</script><?php endif; ?>
          <div class="modal-long-content">
              <div class="modal-content-header">
                  <span class="modal-close" id="td-modal-close">&times;</span>
                  <span class="modal-title" id="td-modal-title"><?php echo getWebsiteConfig('title'); ?></h2>
              </div>
              <div class="modal-content-body">
                  <div id="td-modal-content">
                      <?php if ($view) : ?>
                          <?php include($view); ?>
		                  <?php else: ?>
                          <div id="td-modal-content-nojs">
                            <noscript>
                              <h2>This service requires Javascript to be enabled.</h2>
                              <p>For more information on this website please visit our <a href="/views/about.php" title="More about this website!">About</a> page.</p>
                            </noscript>
                          </div>
                      <?php endif; ?>
                  </div>
              </div>
          </div>
      </div>

      <div id="modal-timetravel" class="modal">
          <div class="modal-content">
              <div class="modal-content-header">
                  <span class="modal-close" onclick="$('#modal-timetravel').hide();">&times;</span>
                  <span class="modal-title">Travel in time</h2>
              </div>
              <div class="modal-content-body" style="margin: 0px 20px 20px 20px;">
                  <?php if (!isAllowedToShowOlderData()) : ?>
                      <div style="text-align: center;">
                          <p style="max-width: 800px; display: inline-block; color: red;">
                              The time travel feature that allows you to see the map as it looked like an earlier date is disabled on this website.
                          </p>
                      </div>
                  <?php else : ?>
                      <p>Select date and time to show map data for (enter time for your locale time zone). The regular time length select box can still be used to select how old data that should be shown (relative to selected date and time).</p>
                      <p>*Note that the heatmap will still based on data from the latest hour (not the selected date and time).</p>
                      <p>Date and time:</p>
                      <form id="timetravel-form">
                         <input type="text" id="timetravel-date" class="form-control" placeholder="Select a start date" readonly>
                          <select id="timetravel-time" class="form-control" style="height:38px;">
                              <option value="0" selected>Select Time</option>
                          </select>
                          <input type="submit" value="Ok" />
                      </form>
                  <?php endif; ?>
              </div>
          </div>
      </div>
  </body>
</html>
