<?php
register_activation_hook(IPFM_PLUGIN_FILE, 'ipfm_install');
function ipfm_install($networkwide=NULL) {
	global $wpdb, $ipfm;

    $time=$ipfm->Options->getPluginInstallDate();
    if($time==0) {
        $ipfm->Options->setPluginInstallDate(time());
        $ipfm->Options->setTrackingEnable(TRUE);
        $ipfm->Tracking->sendTracking(TRUE);
    } elseif($ipfm->Options->isTrackingEnable()) {
        $ipfm->Tracking->sendTracking(TRUE);
    }
    //ipfm_database_update();
    $ipfm->Options->setPluginUpdateDate(time());
    $ipfm->Options->setPluginFirstInstall(TRUE);
    $ipfm->Options->setTrackingLastSend(0);
}

/*function ipfm_database_update($force=FALSE) {
    global $ec;

    //remove OLD CAE issue
    $crons=_get_cron_array();
    foreach($crons as $time=>$jobs) {
        foreach($jobs as $k=>$v) {
            switch (strtolower($k)) {
                case 'ipfm_scheduler_daily':
                case 'ipfm_scheduler_weekly':
                    unset($jobs[$k]);
                    break;
            }
            if(count($jobs)==0) {
                unset($crons[$time]);
            }
        }
    }
    _set_cron_array($crons);

    $md5=$ec->Options->getDatabaseVersion();
    $compare=$ec->Dao->Utils->getDatabaseVersion();
    if($force || $md5!=$compare) {
        if($ec->Dao->Utils->databaseUpdate()) {
            $ec->Options->setDatabaseVersion($compare);
            $ec->Options->setDatabaseUpdateDate(time());
        }
    }
}*/

add_action('admin_init', 'ipfm_first_redirect');
function ipfm_first_redirect() {
    global $ipfm;
    if ($ipfm->Options->isPluginFirstInstall()) {
        $ipfm->Options->setPluginFirstInstall(FALSE);
        $ipfm->Options->setShowActivationNotice(TRUE);

        $ipfm->Options->setShowWhatsNew(FALSE); //TRUE
        $ipfm->Utils->redirect(IPFM_TAB_SETTINGS_URI);
    }
}



