<?php
/**
 * @package Tawk.to Integration
 * @author Tawk.to
 * @copyright (C) 2014- Tawk.to
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
?>

<?php echo $header; ?>

<div id="content">
   <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <div class="box">
    <div class="heading">
	      <h1><img src="view/image/tawkto/tawky.png" alt="tawky" style="height : 20px;" /> <?php echo $heading_title; ?></h1>
	 </div>
</div>
    <div class="content" style="position: relative;min-height: 330px;">
    	<div id="loader" style="position: absolute; top : 50%; left : 50%; margin-top : -35px; margin-left: -35px;">
			<img src="view/image/tawkto/loader.gif" alt="" />
		</div>
		<iframe
			id="tawkIframe"
			src=""
			style="min-height: 330px; width : 100%; border: none; display: none">
		</iframe>
    </div>
  </div>
</div>

<script type="text/javascript">
	var currentHost = window.location.protocol + '//' + window.location.host,
		url = '<?php echo $iframe_url ?>&parentDomain=' + currentHost,
		baseUrl = '<?php echo $base_url ?>',
		storeHierarchy = <?php echo json_encode($hierarchy) ?>;

	jQuery('#tawkIframe').attr('src', url);
	jQuery('#tawkIframe').load(function() {
		$('#loader').hide();
		$(this).show();
	});
	var iframe = jQuery('#tawk_widget_customization')[0];

	window.addEventListener('message', function(e) {

		if(e.origin === baseUrl) {

			if(e.data.action === 'setWidget') {
				setWidget(e);
			}

			if(e.data.action === 'removeWidget') {
				removeWidget(e);
			}

			if(e.data.action === 'getIdValues') {
				e.source.postMessage({action: 'idValues', values : storeHierarchy}, baseUrl);
			}
		}
	});

	function setWidget(e) {
		jQuery.post('<?php echo $this->url->link('module/tawkto/setwidget', '', 'SSL') . '&token=' . $this->session->data['token'] ?>', {
			pageId   : e.data.pageId,
			widgetId : e.data.widgetId,
			id       : e.data.id
		}, function(r) {
			if(r.success) {
				e.source.postMessage({action: 'setDone'}, baseUrl);
			} else {
				e.source.postMessage({action: 'setFail'}, baseUrl);
			}
		});
	}

	function removeWidget(e) {
		jQuery.post('<?php echo $this->url->link('module/tawkto/removewidget', '', 'SSL') . '&token=' . $this->session->data['token'] ?>', {
			id : e.data.id
		}, function(r) {
			if(r.success) {
				e.source.postMessage({action: 'removeDone'}, baseUrl);
			} else {
				e.source.postMessage({action: 'removeFail'}, baseUrl);
			}

		});
	}
</script>

<?php echo $footer; ?>