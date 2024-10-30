<?php
if (!defined('ABSPATH')) exit;

class IPFM_SeparatorElementUi extends IPFM_BaseFooterElementUi {
    public function html(IPFM_FooterElement $element, $options=array()) {
        if($element->border=='') {
            $element->border='none';
        }
        if($element->color=='') {
            $element->color='#000000';
        }
        $element->borderHeight=intval($element->borderHeight);
        ?>
        <div style="width:100%; border-top:<?php echo $element->borderHeight?>px <?php echo $element->border?> <?php echo $element->color?>"></div>
    <?php }
}