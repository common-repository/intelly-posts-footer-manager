<?php
if (!defined('ABSPATH')) exit;

class IPFM_SpacerElementUi extends IPFM_BaseFooterElementUi {
    public function html(IPFM_FooterElement $element, $options=array()) {
        $element->height=intval($element->height);
        ?>
        <div style="width:100%; height: <?php echo $element->height?>px; min-height: <?php echo $element->height?>px;"></div>
    <?php }
}