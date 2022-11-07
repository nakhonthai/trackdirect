jQuery(document).ready(function ($) {
  $('#td-modal-content-nojs').remove();
  if ($('#td-modal-content').text().trim() == '') {
    $('#td-modal').hide();
  } else {
    var title = $('#td-modal-content title').text();
    if (title != '') {
      $("#td-modal-title").text(title);
    }
  }

  $('#hdr-search-form').bind('submit',function(e) {
    var q = $('#hdr-search-form-q').val();
    $('#hdr-search-form-q').val('');
    var seconds = $('#hdr-search-form-seconds').val();
    loadView('/views/search.php?imperialUnits=' + (trackdirect.isImperialUnits() ? 1 : 0 ) + '&q=' + q + '&seconds=' + seconds);
    e.preventDefault();
  });

  $('#timetravel-form').bind('submit',function(e) {
    if ($('#timetravel-date').val() != '0' && $('#timetravel-time').val() != '0') {
        trackdirect.setTimeLength(60, false);
        var ts = moment($('#timetravel-date').val() + ' ' + $('#timetravel-time').val(), 'YYYY-MM-DD HH:mm');
        trackdirect.setTimeTravelTimestamp(ts.unix());
        $('#right-container-timetravel-content').html('Showing ' + ts.format('L LTS'));
        $('#right-container-timetravel').show();
    } else {
        trackdirect.setTimeTravelTimestamp(0, true);
        $('#right-container-timetravel').hide();
    }
    $('#modal-timetravel').hide();
    e.preventDefault();
  });

  const curtime = moment();
  var duration = moment.duration(curtime.diff(dbstart));
  var dbdays = Math.floor(duration.asDays());
  $("#timetravel-date").datepicker({
     showOtherMonths: true,
     selectOtherMonths: true,
     minDate: -(dbdays),
     maxDate: '0',
     dateFormat: 'yy-mm-dd'
  });
  for (x=0; x<24; x++) {
    $("#timetravel-time").append(new Option((x < 10 ? '0':'')+x+':00', (x < 10 ? '0':'')+x+':00'));
  }
});

function wxGaugeParams(id) {
  $('#'+id).attr('data-width', '220');
  $('#'+id).attr('data-height', '220');
  if (id != 'wind-direction-gauge') $('#'+id).attr('data-ticks-angle','225');
  if (id != 'wind-direction-gauge') $('#'+id).attr('data-start-angle','67.5');
  $('#'+id).attr('data-color-major-ticks','#ddd');
  $('#'+id).attr('data-color-minor-ticks','#ddd');
  $('#'+id).attr('data-color-title','#eee');
  $('#'+id).attr('data-color-units','#ccc');
  $('#'+id).attr('data-color-numbers','#eee');
  $('#'+id).attr('data-color-plate','#222');
  $('#'+id).attr('data-border-shadow-width','0');
  $('#'+id).attr('data-borders','true');
  $('#'+id).attr('data-needle-type','arrow');
  $('#'+id).attr('data-needle-width','2');
  $('#'+id).attr('data-needle-circle-size','7');
  $('#'+id).attr('data-needle-circle-outer','true');
  $('#'+id).attr('data-needle-circle-inner','false');
  $('#'+id).attr('data-animation-duration','1500');
  $('#'+id).attr('data-animation-rule','linear');
  $('#'+id).attr('data-animate-on-init','true');
  $('#'+id).attr('data-color-border-outer','#333');
  $('#'+id).attr('data-color-border-outer-end','#111');
  $('#'+id).attr('data-color-border-middle','#222');
  $('#'+id).attr('data-color-border-middle-end','#111');
  $('#'+id).attr('data-color-border-inner','#111');
  $('#'+id).attr('data-color-border-inner-end','#333');
  $('#'+id).attr('data-color-needle-shadow-down','#333');
  $('#'+id).attr('data-color-needle-circle-outer','#333');
  $('#'+id).attr('data-color-needle-circle-outer-end','#111');
  $('#'+id).attr('data-color-needle-circle-inner','#111');
  $('#'+id).attr('data-color-needle-circle-inner-end','#222');
  $('#'+id).attr('data-value-box-border-radius','0');
  $('#'+id).attr('data-color-value-box-rect','#222');
  $('#'+id).attr('data-color-value-box-rect-end','#333');
}

function rainGaugeParams(id) {
  $('#'+id).attr('data-width', '120');
  $('#'+id).attr('data-height', '240');
  $('#'+id).attr('data-min-value', '0');
  $('#'+id).attr('data-stroke-ticks', 'true');
  $('#'+id).attr('data-color-major-ticks','#ddd');
  $('#'+id).attr('data-color-minor-ticks','#ddd');
  $('#'+id).attr('data-color-title','#eee');
  $('#'+id).attr('data-color-units','#ccc');
  $('#'+id).attr('data-color-numbers','#eee');
  $('#'+id).attr('data-color-plate','#222');
  $('#'+id).attr('data-border-shadow-width','0');
  $('#'+id).attr('data-borders','true');
  $('#'+id).attr('data-needle-type','arrow');
  $('#'+id).attr('data-needle-width','2');
  $('#'+id).attr('data-animation-duration','1500');
  $('#'+id).attr('data-animation-rule','linear');
  $('#'+id).attr('data-animate-on-init','true');
  $('#'+id).attr('data-color-border-outer','#333');
  $('#'+id).attr('data-color-border-outer-end','#111');
  $('#'+id).attr('data-color-border-middle','#222');
  $('#'+id).attr('data-color-border-middle-end','#111');
  $('#'+id).attr('data-color-border-inner','#111');
  $('#'+id).attr('data-color-border-inner-end','#333');
  $('#'+id).attr('data-tick-side','left');
  $('#'+id).attr('data-number-side','left');
  $('#'+id).attr('data-needle-side','left');
  $('#'+id).attr('data-bar-stroke-width','7');
  $('#'+id).attr('data-bar-begin-circle','false');
  $('#'+id).attr('data-color-bar','#ddd');
  $('#'+id).attr('data-color-bar-progress','#02cc20');
}

function loadOverviewData(id) {
  $.getJSON('/data/overview.php?id='+id+'&type=pf').done(function(response) {
    if (response.packet_frequency != null) $("#packet_frequency").html('<span>' +response.packet_frequency+'s</span> <span>('+response.packet_frequency_count+' packets)</span>');
    else $("#packet_frequency").html('<span>No recent packets found</span>');
    if (response.packet_frequency_count != null) $("#total_packets").html('<span>' +response.packet_frequency_count+'</span>');
    else $("#total_packets").html('<span>N/A</span>');
  });
}


var liveData = {
  _stationName: null,
  _initComplete: false,

  _activeModule: null,
  _activeCallback: null,

  _lastPacketTimestamp: 0,

  _timerInterval: null,
  _spinnerInterval: null,

  /**
   * Initialize Live Data
   */
  init: function () {
    if (!this._initComplete) {
      window.trackdirect._websocket.addListener("aprs-packet", (packetData) => {
        if (this._stationName == null || this._activeCallback == null) return;

        var packet = new trackdirect.models.Packet(packetData);
        if (packet.station_name != this._stationName || packet.timestamp <= this._lastPacketTimestamp) return;

        this._activeCallback(packet);
        this._lastPacketTimestamp = packet.timestamp;
      });
      this._initComplete = true;
    }
  },

  /**
   * Start Live Data Updates
   * @param {string} stationName
   * @param {string} moduleName
   */
  start: function (stationName, lastPacketTimestamp, moduleName) {
    this._stationName = stationName;
    this._lastPacketTimestamp = lastPacketTimestamp;

    if (this._timerInterval != null) {
      clearInterval(this._timerInterval);
      this._timerInterval = null;
    }
    this._stopSpinner();

    if (moduleName == 'overview') this.overviewStart();
    if (moduleName == 'wxcurrent') this.weatherCurrent();
    if (moduleName == 'livefeed') this.liveFeed();

    if (window.trackdirect.getTimeTravelTimestamp()==null) {
      $("#live-img").attr("src", "/public/images/dotColor4.svg");
      $("#live-status").text("Connected to server, live updates enabled.");
    } else {
      $("#live-img").attr("src", "/public/images/dotColor2.svg");
      $("#live-status").text("Time travel active, live updates unavailable.");
    }
  },

  /**
   * Stop Live Data Updates
   */
  stop: function () {
    this._activeModule = null;
    this._stationName = null;
    if (this._timerInterval != null) {
      clearInterval(this._timerInterval);
      this._timerInterval = null;
    }
    this._stopSpinner();
  },

  /**
   * Start updating the text progress element
   */
  _startSpinner: function () {
    var spinPos = 0;
    this._spinnerInterval = setInterval(function(){
      const s = '|/-\\';
      $('#spinner').text(s.charAt(spinPos));
      if (spinPos++ == 3) spinPos = 0;
    }, 500);
  },

  /**
   * Stop updating the text progress element
   */
  _stopSpinner: function () {
    if (this._spinnerInterval != null) {
      clearInterval(this._spinnerInterval);
      this._spinnerInterval = null;
    }
  },

  /**
   * Start live Updates for overview.php
   */
  overviewStart: function () {
    this._activeModule = "overview";
    this._timerInterval = setInterval(function(){
      if (this._lastPacketTimestamp > 0) {
          $('#latest-timestamp-age').html(moment(new Date(1000 * this._lastPacketTimestamp)).locale('en').fromNow());
      }
    }, 5000);

    this._activeCallback = function(packet) {
      if (this._inOverview == false) return;
      $("#packet_type_name, #latest-timestamp, #latest-timestamp-age, #raw_path, #latest-packet-comment").fadeOut(250, function(){
        $("#packet_type_name").text(packet.getPacketTypeName()+' Packet');
        $("#latest-timestamp").text(moment(new Date(1000 * packet.timestamp)).format('L LTSZ'));
        $('#latest-timestamp-age').html(moment(new Date(1000 * packet.timestamp)).locale('en').fromNow());
        $("#raw_path").text(packet.raw_path);
        $("#latest-packet-comment").text(packet.comment);
      }).fadeIn(250);
      if (packet.packet_type_id == 1) {
        $("#overview-content-latest-position, #position-timestamp").fadeOut(250, function(){
          $("#overview-content-latest-position").text(Math.round(packet.latitude * 100000)/100000 + ', ' + Math.round(packet.longitude * 100000)/100000);
          $("#position-timestamp").text("(Received in latest packet)");
        }).fadeIn(250);
        if (packet.course != null) $("#latest_course").fadeOut(250, function(){ $("#latest_course").html(packet.course + " &deg;").fadeIn(250);});
        if (packet.speed != null) $("#latest_speed").fadeOut(250, function(){ $("#latest_speed").html(trackdirect.isImperialUnits() ?  Math.round(trackdirect.services.imperialConverter.convertKilometerToMile(packet.speed)*100)/100 + " mph" : Math.round(packet.speed*100)/100 + " km/h").fadeIn(250);});
        if (packet.altitude != null) $("#latest_altitude").fadeOut(250, function(){ $("#latest_altitude").html(trackdirect.isImperialUnits() ?  Math.round(trackdirect.services.imperialConverter.convertMeterToFeet(packet.altitude)*100)/100 + " ft" : Math.round(packet.altitude*100)/100 + " m").fadeIn(250);});
      }
      if (packet.packet_type_id == 3) {
        $("#weather-timestamp, #latest-wx-comment").fadeOut(250, function(){
          $("#weather-timestamp").text(moment(new Date(1000 * packet.timestamp)).format('L LTSZ'));
          $("#latest-wx-comment").text(packet.comment);
        }).fadeIn(250);
      }

    };
  },

  /**
   * Start live Updates for weather.php
   */
  weatherCurrent: function () {
    this._activeModule = "wxcurrent";
    this._activeCallback = function(packet) {
      if (packet.packet_type_id == 3) {
        $("#latest-timestamp").fadeOut(250, function(){
          $("#latest-timestamp").text(moment(new Date(1000 * packet.timestamp)).format('L LTS'));
        }).fadeIn(250);
        $.getJSON('/data/data.php?id=' + packet.station_id + '&imperialUnits='+(trackdirect.isImperialUnits() ? '1':'0')+'&module=weather&command=getLatestWeather').done(function(response) {
          $.each(response.data, function(key, val) {
              $("#"+key.replace('_', '-')+"-gauge").attr("data-value", val);
          });
        });
      }
    };
  },

  /**
   * Start live Updates for telemetry.php
   */
  telemetryCurrent: function () {
    this._activeModule = "telemcurrent";
    this._activeCallback = function(packet) {
      if (packet.packet_type_id == 6) {
        $("#latest-timestamp").fadeOut(250, function(){
          $("#latest-timestamp").text(moment(new Date(1000 * packet.timestamp)).format('L LTS'));
        }).fadeIn(250);
        $.getJSON('/data/data.php?id=' + packet.station_id + '&imperialUnits='+(trackdirect.isImperialUnits() ? '1':'0')+'&module=telemetry&command=getLatestTelemetry').done(function(response) {
          $.each(response.data.values, function(key, val) {
              $('#telem-' + key + '-name').text(val.name);
              $('#telem-' + key + '-value').text(val.value);
          });
          $.each(response.data.bits, function(key, val) {
              $('#bits-' + key + '-name').text(val.name);
              $('#bits-' + key + '-value').text(val.value);
          });
        });
      }
    };
  },

  /**
   * Start live Updates for live.php
   */
  liveFeed: function () {
    this._activeModule = "livefeed";

    this._activeCallback = function(packet) {
      var html = '<div class="raw-hidden"><span class="raw-packet-timestamp">' + moment(new Date(1000 * packet.timestamp)).format('L LTSZ') + '</span>: ';
      if (packet.map_id == 3 || packet.map_id == 6) html += '<span class="raw-packet-error parsepkt">';
      else html += '<span class="parsepkt">';

      if (packet.raw != null) {
        const packet_raw = packet.raw;
        const p1 = packet_raw.split(">");
        const p2 = p1[1].split(":");
        const p3 = p2[0].split(",");
        p2.shift();
        p3.forEach(function(v, k, p3) {
          if (k==0) return;
          if (v.indexOf('WIDE') == -1 && v.indexOf('RELAY') == -1 && v.indexOf('TRACE') == -1 && v.indexOf('qA') == -1 && v.indexOf('TCP') == -1 && v.indexOf('T2') == -1 && v.indexOf('CWOP') == -1 && v.indexOf('APRSFI') == -1) {
            p3[k] = '<b><a class="tdlink" onclick="javascript:loadView(this.href);return false;" href="overview.php?c='+encodeURI(v.replace('*', ''))+'&imperialUnits='+(trackdirect.isImperialUnits() ? '1':'0')+'">'+v+'</a></b>';
          }
        });
        html += '<b><a class="tdlink" onclick="javascript:loadView(this.href);return false;" href="overview.php?id=' + packet.station_id + '&imperialUnits='+(trackdirect.isImperialUnits() ? '1':'0')+'">'+p1[0]+'</a></b>&gt;'+p3.join(',')+':' +p2.join(':');

        if (packet.map_id == 3) html += '&nbsp;<b>[Duplicate]</b>';
        if (packet.map_id == 6) html += '&nbsp;<b>[Received in wrong order]</b>';
        html += ' (Type: ' + packet.getPacketTypeName() + ')</span></div>';
      } else {
        html += ' ' + packet.station_name + '&gt;' + packet.raw_path;
      }

      $("#live-content").prepend(html);
      $(".raw-hidden").fadeIn(250);
    };
    $("#output-status").html('Listening for packets to/from ' + this._stationName + '... [<span id="spinner"></span>]');
    this._startSpinner();
  }
};


function quikLink() {
  $("#quiklink, #quikcopy").click(copyClip);
}

function copyClip() {
  $("#quiklink, #quikcopy").off("click");
  $("#quiklink").select();
  document.execCommand("copy");
  $("#quikcopy").after("<span id='cpy'>copied!</span>");
  $("#cpy").delay(500).fadeOut(1000, function(){
    this.remove();
    $("#quiklink, #quikcopy").click(copyClip);
  });
}


function initGraph(cnt) {
  for (let i = 1; i <= cnt; i++) {
    window['ctx_'+i] = document.getElementById('graph_'+i);
    if (window['ctx_'+i] == null) continue;
    window['chart_'+i] = new Chart(window['ctx_'+i], {
      type: 'line',
      data: {
          datasets: [{
              label: "",
              data: [],
              radius: 0,
              borderWidth: 1
          }]
      },
      options: {
        responsive: true,
        interaction: {
          mode: 'index',
          intersect: false,
        },
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
}
// Init local time presentation
jQuery(document).ready(function ($) {
  var locale = window.navigator.userLanguage || window.navigator.language;
  moment.locale(locale);
});

// Switch between regular topnav and topnav adapted for mobile
function toggleTopNav() {
  var x = document.getElementById("tdTopnav");
  if (x.className === "topnav") {
    x.className += " responsive";
  } else {
    x.className = "topnav";
  }
}

// If an external website shows map in iframe, hide all menu's
if (!inIframe()) {
  $("#tdTopnav").hide();
}

// Set correct time length option to active
jQuery(document).ready(function ($) {
  $("#tdTopnavTimelengthDefault").addClass("dropdown-content-checkbox-active");
});

// Open all internal url's in dialog
function loadView(url) {
  var view = url.split('/').pop().split("?")[0];
  if (view != '') {
    var requestUrl = '/views/' + url.split('/').pop();
    $("#td-modal-content").html('<img src="/images/spinner.gif" style="max-width: 100%; max-height: 100px; margin-top: 40px; margin-left: auto; margin-right: auto; display: block;"/>');
    $("#td-modal-title").text('');
    $("#td-modal").show();
    $("#td-modal-content").load(requestUrl, {'modal': true},
      function() {
        history.replaceState(null, "", requestUrl);
        var title = $('#td-modal-content title').text();
        $("#td-modal-title").text(title);
        $("#td-modal-content .tdlink").unbind('click').bind('click', function(e) {
          loadView(this.href);
          e.preventDefault();
        });
      }
    );
  }
}
jQuery(document).ready(function ($) {
  $(".tdlink").bind('click', function(e) {
    loadView(this.href);
    e.preventDefault();
  });
});

// Handle dialog close
jQuery(document).ready(function ($) {
  $("#td-modal-close").bind('click', function(e) {
    $('#td-modal').hide();
    liveData.stop();
    history.replaceState(null, "", "/");
  });
});

// Open station dialog if user clicked on station name
jQuery(document).ready(function ($) {
  trackdirect.addListener("station-name-clicked", function (data) {
    if (trackdirect.isImperialUnits()) {
      loadView("/views/overview.php?id=" + data.station_id + "&imperialUnits=1");
    } else {
      loadView("/views/overview.php?id=" + data.station_id + "&imperialUnits=0");
    }
  });
});

// Update url when user moves map
jQuery(document).ready(function ($) {
  var newUrlTimeoutId = null;
  trackdirect.addListener("position-request-sent", function (data) {
    if (newUrlTimeoutId !== null) {
      clearTimeout(newUrlTimeoutId);
    }

    newUrlTimeoutId = window.setTimeout(function () {
      if ($("#td-modal").is(":hidden")) {
        var url = window.location.href.split('/').pop();
        var newLat = Math.round(data.center.lat * 10000) / 10000;
        var newLng = Math.round(data.center.lng * 10000) / 10000;
        var newZoom = data.zoom;

        if (!url.includes("center=")) {
          if (!url.includes("?")) {
            url += "?center=" + newLat + "," + newLng;
          } else {
            url += "&center=" + newLat + "," + newLng;
          }
        } else {
          url = url.replace(/center=[^&]*/i, "center=" + newLat + "," + newLng);
        }

        if (!url.includes("zoom=")) {
          if (!url.includes("?")) {
            url += "?zoom=" + newZoom;
          } else {
            url += "&zoom=" + newZoom;
          }
        } else {
          url = url.replace(/zoom=[^&]*/i, "zoom=" + newZoom);
        }

        history.replaceState(null, "", url);
      }
    }, 1000);
  });
});

// Handle filter response
jQuery(document).ready(function ($) {
  trackdirect.addListener("filter-changed", function (packets) {
    if (packets.length == 0) {
      // We are not filtering any more.
      $("#right-container-filtered").hide();

      // Time travel is stopped when filtering is stopped
      //$("#right-container-timetravel").hide();

      // Reset tail length to default when filtering is stopped
      $("#tdTopnavTimelength>a").removeClass("dropdown-content-checkbox-active");
      $("#tdTopnavTimelengthDefault").addClass("dropdown-content-checkbox-active");
      $(".dropdown-content-checkbox-only-filtering").addClass("dropdown-content-checkbox-hidden");
    } else {
      var counts = {};
      for (var i = 0; i < packets.length; i++) {
        // Note that if related is set to 1, it is included since it is related to the station we are filtering on
        if (packets[i].related == 0) {
          counts[packets[i]["station_name"]] =
            1 + (counts[packets[i]["station_name"]] || 0);
        }
      }
      $("#right-container-filtered-content").html(
        "Filtering on " + Object.keys(counts).length + " station(s)"
      );
      $("#right-container-filtered").show();
      $(".dropdown-content-checkbox-only-filtering").removeClass("dropdown-content-checkbox-hidden");
    }
  });
});
