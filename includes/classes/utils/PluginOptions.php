<?php

class IPFM_PluginOptions extends IPFM_Options {
    public function __construct() {
    }

    public function setHtmlContentDone($value) {
        return $this->setRequest('HtmlContentDone', $value);
    }
    public function isHtmlContentDone() {
        return $this->getRequest('HtmlContentDone', FALSE);
    }

    public function setHtmlRendering($value) {
        return $this->setRequest('HtmlRendering', $value);
    }
    public function isHtmlRendering() {
        return $this->getRequest('HtmlRendering', FALSE);
    }

    public function getNewFooterItemId($class) {
        global $ipfm;
        $class=$ipfm->Dao->Utils->getClass($class);
        $key='NewFooterItemId_'.$class;
        $result=intval($this->getOption($key, 0));
        $result++;
        $this->setOption($key, $result);
        return $result;
    }

    public function getFooterClass() {
        $id=$this->getParentFooterGroupId();
        $result='Footer'.($id===FALSE || $id==0 ? 'Group' : 'Element');
        return $result;
    }
    public function isFooterGroup() {
        $class=$this->getFooterClass();
        $result=($class=='FooterGroup' ? TRUE : FALSE);
        return $result;
    }

    //ParentFooterGroup
    public function getParentFooterGroupId() {
        $result=$this->getOption('ParentFooterGroup', FALSE);
        if($result!==FALSE) {
            $result=intval($result);
        }
        return $result;
    }
    public function setParentFooterGroupId($value) {
        $this->setOption('ParentFooterGroup', $value);
    }

    //ShortcodeUsed
    public function isShortcodeUsed() {
        return $this->getRequest('ShortcodeUsed', FALSE);
    }
    public function setShortcodeUsed($value) {
        $this->setRequest('ShortcodeUsed', $value);
    }

    public function getArrayFooterItems($class, $parent=FALSE) {
        global $ipfm;
        $result=array();
        $class=$ipfm->Dao->Utils->getClass($class);
        switch ($class) {
            case 'IPFM_FooterGroup':
                $result=$this->getArrayFooterGroups();
                $result=$ipfm->Utils->toMap($result, 'id', FALSE, $class);
                break;
            case 'IPFM_FooterElement':
                $result=$this->getArrayFooterElements();
                if($parent!==FALSE) {
                    if($parent!==FALSE) {
                        $result=(isset($result[$parent]) ? $result[$parent] : array());
                        $result=$ipfm->Utils->toMap($result, 'id', FALSE, $class);
                    } else {
                        $result=array();
                    }
                }
                break;
        }
        return $result;
    }
    public function setArrayFooterItems($class, $items) {
        global $ipfm;
        $class=$ipfm->Dao->Utils->getClass($class);
        switch ($class) {
            case 'IPFM_FooterGroup':
                $this->setArrayFooterGroups($items);
                break;
            case 'IPFM_FooterElement':
                $this->setArrayFooterElements($items);
                break;
        }
    }

    //FooterGroups
    public function getArrayFooterGroups() {
        $result=$this->getOption('ArrayFooterGroups', array());
        return $result;
    }
    public function setArrayFooterGroups($array) {
        $this->setOption('ArrayFooterGroups', $array);
    }
    //FooterElements
    public function getArrayFooterElements() {
        $result=$this->getOption('ArrayFooterElements', array());
        return $result;
    }
    public function setArrayFooterElements($array) {
        $this->setOption('ArrayFooterElements', $array);
    }

    //PluginSettings
    public function getPluginSettings() {
        /* @var $result IPFM_PluginSettings */
        $result=$this->getClassOption('IPFM_PluginSettings', 'PluginSettings');
        if($result->allowUsageTracking===null) {
            $result->allowUsageTracking=1;
        }
        if($result->showPoweredBy===null) {
            $result->showPoweredBy=0;
        }
        return $result;
    }
    public function setPluginSettings(IPFM_PluginSettings $value, $overwrite=FALSE) {
        global $ipfm;
        $current=$this->getPluginSettings();
        if($current->allowUsageTracking!=$value->allowUsageTracking) {
            $this->setTrackingEnable($value->allowUsageTracking ? 1 : 0);
            $ipfm->Tracking->sendTracking(TRUE);
        }
        $this->setClassOption('PluginSettings', $value, $overwrite);
    }
}