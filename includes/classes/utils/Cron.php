<?php
if (!defined('ABSPATH')) exit;

class IPFM_Cron {
	public function __construct() {

	}
    public function init() {
        add_filter('cron_schedules', array($this, 'addSchedules'));
        $this->scheduleEvents();
    }

    public function addSchedules($schedules=array()){
        /*$schedules[IPFM_PLUGIN_PREFIX.'daily']=array(
            'interval'=> 86400
            , 'display'=>'{'.IPFM_PLUGIN_NAME.'} Daily'
        );
        $schedules[IPFM_PLUGIN_PREFIX.'weekly']=array(
            'interval'=> 604800
            , 'display'=>'{'.IPFM_PLUGIN_NAME.'} Weekly'
        );
        $schedules[IPFM_PLUGIN_PREFIX.'each1hour']=array(
            'interval'=> 60*60
            , 'display'=>'{'.IPFM_PLUGIN_NAME.'} Each 1 hour'
        );
        $schedules[IPFM_PLUGIN_PREFIX.'each1minute']=array(
            'interval'=> 10
            , 'display'=>'{'.IPFM_PLUGIN_NAME.'} Each 1 minute'
        );*/
        return $schedules;
    }
    public function scheduleEvents() {

    }
    private function wpScheduleEvent($recurrence, $function) {
        global $ipfm;
        if(!$ipfm->Utils->functionExists($function)) {
            return;
        }

        //ipfm_scheduler_daily|ipfm_scheduler_weekly
        /*$crons=_get_cron_array();
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
        _set_cron_array($crons);*/

        $hook='cron_'.IPFM_PLUGIN_PREFIX.$recurrence.'_'.$ipfm->Utils->getFunctionName($function);
        if(!wp_next_scheduled($hook)) {
            wp_schedule_event(time(), $recurrence, $hook);
        }
        add_action($hook, $function);
    }
}
