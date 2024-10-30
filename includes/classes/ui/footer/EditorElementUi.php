<?php
if (!defined('ABSPATH')) exit;

class IPFM_EditorElementUi extends IPFM_BaseFooterElementUi {
    public function html(IPFM_FooterElement $element, $options=array()) {
        $content=$this->getContent($element->wpEditor);
        echo $content;
    }
}