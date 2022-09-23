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
        var ts = moment($('#timetravel-date').val() + ' ' + $('#timetravel-time').val(), 'YYYY-MM-DD HH:mm').unix();
        trackdirect.setTimeTravelTimestamp(ts);
        $('#right-container-timetravel-content').html('Showing ' + $('#timetravel-date').val() + ' ' + $('#timetravel-time').val());
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

function wxInitGraph() {
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
