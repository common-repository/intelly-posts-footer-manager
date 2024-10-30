<?php
if (!defined('ABSPATH')) exit;

class IPFM_HtmlElementUi extends IPFM_BaseFooterElementUi {
    public function html(IPFM_FooterElement $element, $options=array()) {
        echo $element->htmlEditor;
    }
}