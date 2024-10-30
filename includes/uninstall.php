<?php
register_deactivation_hook(IPFM_PLUGIN_FILE, 'ipfm_uninstall');
function ipfm_uninstall($networkwide=NULL) {
	global $wpdb, $ipfm;
    $ipfm->Options->setActive(FALSE);
}
?>