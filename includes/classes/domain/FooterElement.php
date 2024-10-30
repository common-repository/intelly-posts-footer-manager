<?php
if (!defined('ABSPATH')) exit;

//@iwp
class IPFM_FooterElement {
    //@type=int @primary
    //@ui-type=number
    var $id;
    //@type=text
    //@ui-type=text @ui-align=left
    var $name;

    //@type=int
    //@ui-type=toggle
    var $active;
    //@type=int
    //@ui-type=number
    var $order;

    //@type=text
    //@ui-type=dropdown
    var $what;

    //@type=text
    //@ui-type=color
    var $backgroundColor;
    //@type=text
    //@ui-type=color @ui-visible=what:SEPARATOR
    var $color;
    //@type=text
    //@ui-type=dropdown @ui-visible=what:SEPARATOR
    var $border;

    //@type=text
    //@ui-type=dropdown @ui-lazy= @ui-visible=what:WIDGET
    var $widget;

    //@type=text
    //@ui-type=textarea @ui-visible=what:SHORTCODE
    var $shortcode;
    //@type=text
    //@ui-type=editor @ui-editor=wp @ui-visible=what:EDITOR
    var $wpEditor;
    //@type=text
    //@ui-type=editor @ui-editor=html @ui-visible=what:HTML
    var $htmlEditor;
    //@type=text
    //@ui-type=editor @ui-editor=js @ui-visible=what:JAVASCRIPT
    var $jsEditor;
    //@type=text
    //@ui-type=editor @ui-editor=css @ui-visible=what:CSS
    var $cssEditor;

    //@type=int
    //@ui-type=number @ui-visible=what:SPACER
    var $height;
    //@type=int
    //@ui-type=number @ui-visible=what:SEPARATOR
    var $borderHeight;

    //@type=int
    //@ui-type=number
    var $marginTop;
    //@type=int
    //@ui-type=number
    var $marginBottom;

    public function __costruct() {

    }
}