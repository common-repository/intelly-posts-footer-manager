<?php
class IPFM_Singleton {
    var $Lang;
    var $Utils;
    var $Options;
    var $Log;
    var $Cron;
    var $Tracking;
    var $Tabs;
    var $Lazy;
    var $Ui;
    var $Manager;
    var $Dao;
    var $Form;
    var $Check;

    function __construct() {
        $this->Lang=new IPFM_Language();
        $this->Utils=new IPFM_Utils();
        $this->Options=new IPFM_PluginOptions();
        $this->Log=new IPFM_Logger();
        $this->Cron=new IPFM_Cron();
        $this->Tracking=new IPFM_Tracking();
        $this->Tabs=new IPFM_Tabs();
        $this->Lazy=new IPFM_LazyLoader();
        $this->Dao=new IPFM_Dao();
        $this->Ui=new IPFM_Ui();
        $this->Manager=new IPFM_Manager();
        $this->Form=new IPFM_CrazyForm();
        $this->Check=new IPFM_Check();
    }
    function init() {
        $this->Lang->load('ipfm', IPFM_PLUGIN_DIR.'languages/Lang.txt');
        $this->Lang->bundle->autoPush=TRUE;
        $this->Dao->Utils->load(IPFM_PLUGIN_PREFIX, IPFM_PLUGIN_DIR.'includes/classes/domain/');
        $this->Tabs->init();
        $this->Manager->init();
        $this->Cron->init();
    }
}