<?php
if (!defined('ABSPATH')) exit;

class IPFM_BaseFooterElementUi {
    public function html(IPFM_FooterElement $element, $options=array()) {

    }
    public function getContent($content) {
        $content=apply_filters('the_content', $content);
        return $content;
    }
}