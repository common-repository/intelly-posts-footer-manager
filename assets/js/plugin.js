//IntellyWP
jQuery('.wrap .updated.fade').remove();
jQuery('.woocommerce-message').remove();
jQuery('.error').remove();
jQuery('.info').remove();
jQuery('.update-nag').remove();

jQuery(function() {
    "use strict";
    //WooCommerce errors
    var removeWooUpdateTheme = setInterval(function () {
        if (jQuery('.wrap .updated.fade').length > 0) {
            jQuery('.wrap .updated.fade').remove();
            clearInterval(removeWooUpdateTheme);
        }
    }, 100);
    var removeWooMessage = setInterval(function () {
        if (jQuery('.woocommerce-message').length > 0) {
            jQuery('.woocommerce-message').remove();
            clearInterval(removeWooMessage);
        }
    }, 100);

    jQuery('.wrap .updated.fade').remove();
    jQuery('.woocommerce-message').remove();
    jQuery('.error').remove();
    jQuery('.info').remove();
    jQuery('.update-nag').remove();
});

jQuery(function() {
    if(jQuery('.wrap .updated.fade').length>0) {
        jQuery('.wrap .updated.fade').remove();
    }
    if(jQuery('.woocommerce-message').length>0) {
        jQuery('.woocommerce-message').remove();
    }
    jQuery('.update-nag,.updated,.error').each(function() {
        var $self=jQuery(this);
        if(!$self.hasClass('iwp')) {
            $self.remove();
        }
    });
});

jQuery(function() {
    "use strict";

    //WooCommerce errors
    var removeWooUpdateTheme=setInterval(function() {
        if(jQuery('.wrap .updated.fade').length>0) {
            jQuery('.wrap .updated.fade').remove();
            clearInterval(removeWooUpdateTheme);
        }
    }, 100);
    var removeWooMessage=setInterval(function() {
        if(jQuery('.woocommerce-message').length>0) {
            jQuery('.woocommerce-message').remove();
            clearInterval(removeWooMessage);
        }
    }, 100);

    jQuery('form').on('submit', function() {
        jQuery('input, select').prop('disabled', false);
        return true;
    });

    var LB_visibleChanges={};
    jQuery('input,select,textarea').each(function() {
        var $self=jQuery(this);
        var name=IPFM.attr($self, 'name', '');
        var visible=IPFM.attr($self, 'ui-visible', '');
        if(visible!='') {
            var conditions=visible.split('&');
            var index=0;
            for(index=0; index<conditions.length; index++) {
                var k=conditions[index].split(':');
                k=k[0];
                var v=LB_visibleChanges[k];
                if(v==undefined) {
                    v=new Array();
                }
                v.push(name);
                LB_visibleChanges[k]=v;
            }
        }
    });

    jQuery.each(LB_visibleChanges, function(k,v) {
        var $what=jQuery('[name='+k+']');
        $what.change(function() {
            jQuery.each(v, function(i,name) {
                var $self=jQuery('[name='+name+']');
                var $div=jQuery('#'+name+'-row');
                var visible=IPFM.attr($self, 'ui-visible', '');

                var all=true;
                var conditions=visible.split('&');
                var index=0;
                for(index=0; index<conditions.length; index++) {
                    var text=conditions[index].split(':');
                    var $compare=IPFM.jQuery(text[0]);
                    var current=IPFM.val($compare);
                    var options=text[1];
                    options=options.split('|');

                    var found=false;
                    jQuery.each(options, function(i,compare) {
                        if(compare!='' && compare==current) {
                            found=true;
                            return false;
                        }
                    });

                    if(!found) {
                        all=false;
                        break;
                    }
                }

                if(all) {
                    $div.show();
                } else {
                    $div.hide();
                }
            });
        });
        $what.trigger('change');
        //console.log('WHAT=%s TRIGGER CHANGE', IPFM.attr($what, 'name'));
    });

    if(jQuery().multiselect) {
        jQuery('.ipfm-multiselect').multiselect({
            buttonClass: 'btn btn-default btn-sm ph15',
            dropRight: true
        });
    }
    jQuery('.ipfm-dropdown').each(function() {
        var $self=jQuery(this);
        var options={};
        IPFM.select2($self, options);
        IPFM.changeShowOptions($self);

        var ajax=IPFM.attr($self, 'ipfm-ajax', false);
        var lazy=IPFM.attr($self, 'ipfm-lazy', false);
        var help=IPFM.attr($self, 'ipfm-help', '');
        var parent=IPFM.attr($self, 'ipfm-master', '');

        if (parent!=='') {
            var masters=parent.split('|');
            var $parent=false;
            if(masters.length==1) {
                $parent=IPFM.jQuery(masters[0]);
            } else {
                //register only to the last
                $parent=IPFM.jQuery(masters[masters.length-1]);
            }
            $parent.change(function() {
                //console.log('PARENT CHANGE %s > %s'
                //    , IPFM.attr($parent, 'name'), IPFM.attr($self, 'name'));
                IPFM.select2($self, {data: []});

                var parentId=IPFM.val(parent);
                var check=false;
                if(parentId!==undefined && parentId!='') {
                    var array=parentId.split('|');
                    check=true;
                    jQuery.each(array, function(i,v) {
                        if(v=='') {
                            check=false;
                            return false;
                        }
                    });
                }
                if(lazy && parentId!='' && check) {
                    $parent.prop('disabled', true);
                    $self.prop('disabled', true);

                    var id=$self.attr('id');
                    var $text=jQuery('#select2-'+id+'-container .select2-selection__placeholder');
                    var placeholder=$text.html();
                    $text.html('Loading data..');

                    jQuery.ajax({
                        type: 'POST'
                        , dataType: 'json'
                        , data: {
                            //action: 'lb_ajax_ll'
                            //, lb_action: lazy
                            action: lazy
                            , parentId: parentId
                        }
                        , success: function(data) {
                            //console.log('success');
                            //console.log(data);

                            IPFM.select2($self, {data: data});
                            $self.prop('disabled', false);
                            $parent.prop('disabled', false);
                            $text.html(placeholder);
                        }
                        , error: function(data) {
                            //console.log('error');
                            //console.log(data);

                            $self.prop('disabled', false);
                            $parent.prop('disabled', false);
                            $text.html(placeholder);
                        }
                    });
                }
            });
            /*var v=$self.val();
            if(v==null || (jQuery.isArray(v) && v.length==0) || v=='') {
                $parent.trigger('change');
            }*/
        }
    });
    jQuery('.ipfm-tags').each(function() {
        var $self=jQuery(this);
        var options={
            tags: true
            , tokenSeparators: [',', ' ']
        };
        IPFM.select2($self, options);
        IPFM.changeShowOptions($self);
    });

    jQuery('.ipfm-tags, .ipfm-dropdown').change(function() {
        var $self=jQuery(this);
        IPFM.changeShowOptions($self);
    });

    jQuery(".ipfm-hideShow").click(function () {
        IPFM.hideShow(this);
    });
    jQuery(".ipfm-hideShow").each(function () {
        IPFM.hideShow(this);
    });
    jQuery(".alert-dismissable .close").on('click', function() {
        var $self=jQuery(this);
        $self.parent().parent().remove();
    });

    if(jQuery.colorpicker) {
        jQuery('.ipfm-colorpicker').colorpicker();
    }

    //ipfm-timer
    jQuery('.ipfm-timer').on('change', function() {
        var $self=jQuery(this);
        var name=IPFM.attr($self, 'name');

        var $days=IPFM.jQuery(name+'Days');
        var $hours=IPFM.jQuery(name+'Hours');
        var $minutes=IPFM.jQuery(name+'Minutes');
        var $seconds=IPFM.jQuery(name+'Seconds');

        var text=$days.val()+':'+$hours.val()+':'+$minutes.val()+':'+$seconds.val();
        text=IPFM.formatTimer(text);
        $self.val(text);

        text=text.split(':');
        $days.val(parseInt(text[0]));
        $hours.val(parseInt(text[1]));
        $minutes.val(parseInt(text[2]));
        $seconds.val(parseInt(text[3]));
    });
    jQuery('.ipfm-timer').each(function() {
        var $self=jQuery(this);
        var name=IPFM.attr($self, 'name');

        var $days=IPFM.jQuery(name+'Days');
        var $hours=IPFM.jQuery(name+'Hours');
        var $minutes=IPFM.jQuery(name+'Minutes');
        var $seconds=IPFM.jQuery(name+'Seconds');

        $days.on('change', function() {
            $self.trigger('change');
        })
        $hours.on('change', function() {
            $self.trigger('change');
        })
        $minutes.on('change', function() {
            $self.trigger('change');
        })
        $seconds.on('change', function() {
            $self.trigger('change');
        })
        $self.trigger('change');
    });

    /*jQuery('.ipfm-time:not([readonly])').timepicker({
        beforeShow: function(input, inst) {
            var themeClass='theme-primary';
            inst.dpDiv.wrap('<div class="'+themeClass+'"></div>');
        }
    });*/
    jQuery('.ipfm-datetime:not([readonly])').datetimepicker({
        prevText: '<i class="fa fa-chevron-left"></i>'
        , nextText: '<i class="fa fa-chevron-right"></i>'
        , format: 'DD/MM/YYYY HH:mm'
        , beforeShow: function(input, inst) {
            var themeClass='theme-primary';
            inst.dpDiv.wrap('<div class="'+themeClass+'"></div>');
        }
        , firstDay: 1
    });
    if(jQuery(".ipfm-date:not([readonly])").length>0) {
        jQuery(".ipfm-date:not([readonly])").datepicker({
            prevText: '<i class="fa fa-chevron-left"></i>'
            , nextText: '<i class="fa fa-chevron-right"></i>'
            , showButtonPanel: false
            , dateFormat: 'dd/mm/yy'
            , beforeShow: function(input, inst) {
                var themeClass='theme-primary';
                inst.dpDiv.wrap('<div class="'+themeClass+'"></div>');
            }
            , firstDay: 1
        });
    }

    /*if(jQuery(".ecTags").length>0) {
        jQuery(".ecTags").select2({
            placeholder: "Type here..."
            , theme: "classic"
            , width: '300px'
        });
    }

    if(jQuery(".ecColorSelect").length>0) {
        jQuery(".ecColorSelect").select2({
            placeholder: "Type here..."
            , theme: "classic"
            , width: '300px'
            , formatResult: IPFM_formatColorOption
            , formatSelection: IPFM_formatColorOption
            , escapeMarkup: function(m) {
                return m;
            }
        });
    }*/
    jQuery('.ipfm-button-toggle').on('click', function() {
        var $self=jQuery(this);
        var showClass=$self.attr('data-filter');
        if(showClass=='') {
            return;
        }
        var pos=showClass.lastIndexOf('-');
        var baseClass=showClass.substring(0, pos);

        //console.log('baseClass=%s, count=%s', showClass, jQuery('.'+baseClass).length);
        //console.log('showClass=%s, count=%s', showClass, jQuery('.'+showClass).length);

        $self.parent().children().each(function(i,v) {
            var $this=jQuery(this);
            if(!$this.hasClass('ipfm-button-toggle')) {
                return;
            }

            var thisClass=$this.attr('data-filter');
            if(thisClass.indexOf(baseClass)===0) {
                $this.removeClass('active');
                $this.removeClass('btn-info');
            }
        });

        jQuery('.'+baseClass).hide();
        jQuery('.'+showClass).show();
        $self.addClass('active');
        $self.addClass('btn-info');
    });

    jQuery.browser = {};
    (function () {
    jQuery.browser.msie = false;
    jQuery.browser.version = 0;
    if (navigator.userAgent.match(/MSIE ([0-9]+)\./)) {
    jQuery.browser.msie = true;
    jQuery.browser.version = RegExp.$1;
    }
    })();

    if(jQuery('[data-toggle=tooltip]').qtip) {
        jQuery('[data-toggle=tooltip]').qtip({
            position: {
                corner: {
                    target: 'topMiddle',
                    tooltip: 'bottomLeft'
                }
            },
            show: {
                when: {event: 'mouseover'}
            },
            hide: {
                fixed: true,
                when: {event: 'mouseout'}
            },
            style: {
                tip: 'bottomLeft',
                name: 'blue'
            }
        });
    }

    var IPFM_WpMedia;
    jQuery('.ipfm-upload-button').on('click', function(e) {
        e.preventDefault();
        var $button=jQuery(this);
        var name=IPFM.attr($button, 'data-id', '');
        var multiple=IPFM.attr($button, 'ui-multiple', false);
        multiple=IPFM.isTrue(multiple);
        var $text=IPFM.jQuery(name);

        //If the uploader object has already been created, reopen the dialog
        if (IPFM_WpMedia) {
            IPFM_WpMedia.open();
            return;
        }
        //Extend the wp.media object
        IPFM_WpMedia=wp.media.frames.file_frame=wp.media({
            title: 'Choose Image'
            , button: {
                text: 'Choose Image'
            }
            , multiple: multiple
        });

        //When a file is selected, grab the URL and set it as the text field's value
        IPFM_WpMedia.on('select', function() {
            var attachment=IPFM_WpMedia.state().get('selection').first().toJSON();
            $text.val(attachment.url);
        });

        //Open the uploader dialog
        IPFM_WpMedia.open();
    });
    jQuery('.ipfm-select-onfocus').focus(function() {
        var $self=jQuery(this);
        $self.select();
    });
});
