<?php
  require dirname(__DIR__) . "../../includes/bootstrap.php";

  $server = $_GET['server'] ?? '';
  $status_url = '';
  if ($server != '') {
    $status_url = getWebsiteConfig("${server}_is_status_url");
  }
?>
<script>
$(document).ready(function() {
  function resize_iframe() {
    var height = $('.modal-content-body').height();
    $('#statuspanel').css('height', height - 95);
  }
  resize_iframe();

  $(window).resize(function() {
    resize_iframe();
  });
});
</script>
<title>About / <?php echo strtoupper($server); ?> Server Health</title>
<div class="modal-inner-content modal-inner-content-about" style="padding-bottom: 30px;">
    <div class="modal-inner-content-menu">
        <a href="/views/about.php" class="tdlink" title="More about this website!">About</a>
        <a href="/views/faq.php" class="tdlink" title="Frequently asked questions">FAQ</a>
        <a href="/views/site_statistics.php" class="tdlink" title="Website and server statistics!">Statistics</a>
        <?php if (getWebsiteConfig('aprs_is_status_url') && $server != 'aprs'): ?><a href="/views/server_health.php?server=aprs" class="tdlink" title="APRS Server Health">APRS Server Health</a><?php else: ?><span>APRS Server Health</span><?php endif; ?>
        <?php if (getWebsiteConfig('cwop_is_status_url') && $server != 'cwop'): ?><a href="/views/server_health.php?server=cwop" class="tdlink" title="CWOP Server Health">CWOP Server Health</a><?php else: ?><span>CWOP Server Health</span><?php endif; ?>
    </div>
    <div class="horizontal-line">&nbsp;</div>
    This site hosts a dedicated <?php echo strtoupper($server); ?>-IS server. This server status page can be viewed directly with this <a href="<?php echo $status_url; ?>" target=-"_blank">link</a>.
    <iframe src="<?php echo $status_url; ?>" style="width:100%;height:100%" id="statuspanel"?>
</div>
