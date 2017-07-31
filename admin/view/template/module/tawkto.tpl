<?php
/**
 * @package Tawk.to Integration
 * @author Tawk.to
 * @copyright (C) 2014- Tawk.to
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
?>

<?php echo $header; ?>
<link href="https://plugins.tawk.to/public/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<div id="content">
    <div class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) : ?>
            <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
        <?php endforeach; ?>
    </div>
    <div class="box">
        <div class="heading">
              <h1><img src="view/image/tawkto/tawky.png" alt="tawky" style="height : 20px;" /> <?php echo $heading_title; ?></h1>
         </div>
    </div>
    <div class="box">
        <?php if (!$same_user) : ?>
        <div id="widget_already_set" style="width: 100%;color: #3c763d; border-color: #d6e9c6; font-weight: bold; margin: 20px 0 0;" class="alert alert-warning">Notice: Widget already set by other user</div>
        <?php endif; ?>
    </div>
    <div class="content" style="position: relative;min-height: 310px;">
        <div id="loader" style="position: absolute; top : 50%; left : 50%; margin-top : -35px; margin-left: -35px;">
            <img src="view/image/tawkto/loader.gif" alt="" />
        </div>
        <iframe
            id="tawkIframe"
            src=""
            style="min-height: 310px; width : 100%; border: none; display: none">
        </iframe>
        <input type="hidden" class="hidden" name="page_id" value="<?php echo $widget_config['page_id']?>">
        <input type="hidden" class="hidden" name="widget_id" value="<?php echo $widget_config['widget_id']?>">
        <input type="hidden" class="hidden" name="store_id" value="<?php echo $store_id?>">
        <input type="hidden" class="hidden" name="store_layout_id" value="<?php echo $store_layout_id?>">
    </div>
    <div class="box">
        <hr>
        <div class="row">
            <div class="col-lg-8">
                <form id="module_form" class="form-horizontal" action="" method="post">
                    <div class="panel panel-default" id="fieldset_1">
                        <div class="form-group col-lg-12">
                            <div class="panel-heading"><strong>Visibility Settings</strong></div>
                        </div>
                        <br>
                        <div class="form-group col-lg-12">
                            <label for="always_display" class="col-lg-6 control-label">Always show Tawk.To widget on every page</label>
                            <div class="col-lg-6 control-label ">
                                <?php
                                $checked = true;
                                if (!$display_opts['always_display']) {
                                    $checked = false;
                                }
                                ?>
                                <input type="checkbox" class="col-lg-6" name="always_display" 
                                    id="always_display" value="1" <?php echo ($checked)?'checked':'';?> />
                            </div>
                        </div>
                        <div class="form-group col-lg-12">
                            <label for="hide_oncustom" class="col-lg-6 control-label">Except on pages:</label>
                            <div class="col-lg-6 control-label">
                                <?php if (!empty($display_opts['hide_oncustom'])) : ?>
                                    <?php $whitelist = json_decode($display_opts['hide_oncustom']) ?>
                                    <textarea class="form-control hide_specific" name="hide_oncustom" 
                                        id="hide_oncustom" cols="30" rows="10"><?php foreach ($whitelist as $page) { echo $page."\r\n"; } ?></textarea>
                                <?php else : ?>
                                    <textarea class="form-control hide_specific" name="hide_oncustom" id="hide_oncustom" cols="30" rows="10"></textarea>
                                <?php endif; ?>
                                <br>
                                <p style="text-align: justify;">
                                Add URLs to pages in which you would like to hide the widget. ( if "always show" is checked )<br>
                                Put each URL in a new line.
                                </p>
                            </div>
                        </div>
                        <div class="form-group col-lg-12">
                            <label for="show_onfrontpage" class="col-lg-6 control-label">Show on frontpage</label>
                            <div class="col-lg-6 control-label ">
                                <?php
                                $checked = false;
                                if ($display_opts['show_onfrontpage']) {
                                    $checked = true;
                                }
                                ?>
                                <input type="checkbox" class="col-lg-6 show_specific" name="show_onfrontpage" 
                                    id="show_onfrontpage" value="1" 
                                    <?php echo ($checked)?'checked':'';?> />
                            </div>
                        </div>
                        <div class="form-group col-lg-12">
                            <label for="show_oncategory" class="col-lg-6 control-label">Show on category pages</label>
                            <div class="col-lg-6 control-label ">
                                <?php
                                $checked = false;
                                if ($display_opts['show_oncategory']) {
                                    $checked = true;
                                }
                                ?>
                                <input type="checkbox" class="col-lg-6 show_specific" name="show_oncategory" id="show_oncategory" value="1" 
                                    <?php echo ($checked)?'checked':'';?>  />
                            </div>
                        </div>
                        <div class="form-group col-lg-12">
                            <label for="show_oncustom" class="col-lg-6 control-label">Show on pages:</label>
                            <div class="col-lg-6 control-label">
                                <?php if (!empty($display_opts['show_oncustom'])) : ?>
                                    <?php $whitelist = json_decode($display_opts['show_oncustom']) ?>
                                    <textarea class="form-control show_specific" name="show_oncustom" 
                                        id="show_oncustom" cols="30" rows="10"><?php foreach ($whitelist as $page) { echo $page."\r\n"; } ?></textarea>
                                <?php else : ?>
                                    <textarea class="form-control show_specific" name="show_oncustom" id="show_oncustom" cols="30" rows="10"></textarea>
                                <?php endif; ?>
                                <br>
                                <p style="text-align: justify;">
                                Add URLs to pages in which you would like to show the widget.<br>
                                Put each URL in a new line.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="panel-footer" style="position: relative; overflow: hidden; width: 100%; padding: 5px 0;">
                        <div id="optionsSuccessMessage" style="position:absolute;top:0;left;0;background-color: #dff0d8; color: #3c763d; border-color: #d6e9c6; font-weight: bold; display: none;" class="alert alert-success col-lg-5">Successfully set widget options to your site</div>
                        <label for="show_oncustom" class="col-lg-6 control-label"></label>
                        <div class="form-group">
                            <button type="submit" value="1" id="module_form_submit_btn" name="submitBlockCategories" class="btn btn-default pull-right"><i class="process-icon-save"></i> Save</button>    
                        </div>
                    </div>
                </form>
                
            </div>
            <div class="col-lg-4"></div>
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
        var store_layout =  e.data.id;
        jQuery.post('<?php echo $this->url->link('module/tawkto/setwidget', '', 'SSL') . '&token=' . $this->session->data['token'] ?>', {
            page_id   : e.data.pageId,
            widget_id : e.data.widgetId,
            store     : parseInt(store_layout),
            store_layout : e.data.id
        }, function(r) {
            if(r.success) {
                e.source.postMessage({action: 'setDone'}, baseUrl);
                jQuery('input[name="page_id"]').val(e.data.pageId);
                jQuery('input[name="widget_id"]').val(e.data.widgetId);
                var newval = parseInt(store_layout);
                jQuery('input[name="store_id"]').val(newval);
                jQuery('input[name="store_layout_id"]').val(e.data.id);
            } else {
                e.source.postMessage({action: 'setFail'}, baseUrl);
            }
        });
    }

    function removeWidget(e) {
        var store_layouts =  e.data.id;
        jQuery.post('<?php echo $this->url->link('module/tawkto/removewidget', '', 'SSL') . '&token=' . $this->session->data['token'] ?>', {
            store : parseInt(store_layout),
            store_layout : e.data.id,
        }, function(r) {
            if(r.success) {
                e.source.postMessage({action: 'removeDone'}, baseUrl);
                jQuery('input[name="page_id"]').val();
                jQuery('input[name="widget_id"]').val();
                jQuery('input[name="store_id"]').val();
                jQuery('input[name="store_layout_id"]').val();
            } else {
                e.source.postMessage({action: 'removeFail'}, baseUrl);
            }

        });
    }
    jQuery(document).ready(function() {
        if(jQuery("#always_display").prop("checked")){
            jQuery('.show_specific').prop('disabled', true);
        } else {
            jQuery('.hide_specific').prop('disabled', true);
        }

        jQuery("#always_display").change(function() {
            if(this.checked){
                jQuery('.hide_specific').prop('disabled', false);
                jQuery('.show_specific').prop('disabled', true);
            }else{
                jQuery('.hide_specific').prop('disabled', true);
                jQuery('.show_specific').prop('disabled', false);
            }
        });

        // process the form
        jQuery('#module_form').submit(function(event) {
            $path = '<?php echo $this->url->link('module/tawkto/setoptions', '', 'SSL') . '&token=' . $this->session->data['token'] ?>';
            jQuery.post($path, {
                action     : 'set_visibility',
                ajax       : true,
                page_id    : jQuery('input[name="page_id"]').val(),
                widget_id  : jQuery('input[name="widget_id"]').val(),
                store      : parseInt(jQuery('input[name="store_layout_id"]').val()),
                options    : jQuery(this).serialize()
            }, function(r) {
                if(r.success) {
                    $('#optionsSuccessMessage').toggle().delay(3000).fadeOut();
                }
            });

            // stop the form from submitting the normal way and refreshing the page
            event.preventDefault();
        });
    });
</script>

<?php echo $footer; ?>