<?php
if (!defined('ABSPATH')) exit;

//@iwp
class IPFM_FooterGroup {
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

    //@type=array
    //@ui-type=dropdown @ui-all=true @ui-align=left
    var $deviceType;
    //@type=int
    //@ui-type=toggle
    var $everywhere;

    //@type=array
    //@ui-type=dropdown @ui-lazy=WP_getPostTypes @ui-visible=everywhere:0
    var $type;
    //@type=int
    //@ui-type=toggle
    var $expert;
    //@type=array
    //@ui-type=dropdown @ui-multiple @ui-map=WP_getTermsTypes @ui-lazy=WP_getTermsItems
    var $terms;

    //@type=text
    //@ui-type=textarea @ui-visible=everywhere:0
    var $includeUrls;
    //@type=text
    //@ui-type=textarea @ui-visible=everywhere:1
    var $excludeUrls;

    //@type=text
    //@ui-type=color
    var $backgroundColor;

    //@type=int
    //@ui-type=number @ui-min=0
    var $maxWidth;
    //@type=int
    //@ui-type=number
    var $elementsCount;

    public function __costruct() {

    }
}