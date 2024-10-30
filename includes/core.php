<?php
function IPFM_wp_head() {
    global $ipfm;
}
add_filter('wp_head', 'IPFM_wp_head');

function IPFM_wp_footer() {
    global $ipfm;
}
add_filter('wp_footer', 'IPFM_wp_footer');

function IPFM_admin_footer() {
    global $ipfm;
    if($ipfm->Lang->bundle->autoPush && IPFM_AUTOSAVE_LANG) {
        $ipfm->Lang->bundle->store(IPFM_PLUGIN_DIR.'languages/Lang.txt');
    }
}
add_filter('admin_footer', 'IPFM_admin_footer');

function IPFM_pfm($atts, $content='') {
    global $ipfm;
    $default=array(
        'id'=>0
    );
    $options=shortcode_atts($default, $atts);
    $group=$ipfm->Manager->get('FooterGroup', $options['id']);
    if($group===FALSE) {
        return;
    }

    $ipfm->Options->setHtmlContentDone(TRUE);
    ob_start();
    $shortcode = true;
    $ipfm->Ui->Footer->html($group, $shortcode);
    $result = ob_get_contents();
    ob_end_clean();
    if ($result != '') {
        $ipfm->Options->setShortcodeUsed(TRUE);
    }
    return $result;
}
add_shortcode('pfm', 'IPFM_pfm');

function IPFM_the_content($content) {
    global $ipfm;
    if(is_singular() && is_main_query()) {
        if(!$ipfm->Options->isShortcodeUsed() && !$ipfm->Options->isHtmlRendering()) {
            $ipfm->Options->setHtmlContentDone(TRUE);
            ob_start();
            $ipfm->Ui->Footer->html();
            $footer=ob_get_contents();
            ob_end_clean();
            if($footer!='') {
                $content.=$footer;
            }
        }
    }
    return $content;
}
add_filter('the_content', 'IPFM_the_content');

function ipfm_ui_first_time() {
    global $ipfm;
    if($ipfm->Options->isShowActivationNotice()) {
        ipfm_ui_install_demo();
        $ipfm->Options->setShowActivationNotice(FALSE);
    }
}
