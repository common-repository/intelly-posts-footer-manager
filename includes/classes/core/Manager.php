<?php
if (!defined('ABSPATH')) exit;

class IPFM_Manager {
    public function __construct() {

    }
    public function init() {
        add_action('wp_ajax_IPFM_changeOrder', array(&$this, 'changeOrder'));
    }

    public function isLimitReached($class, $notice=TRUE) {
        global $ipfm;

        $class=$ipfm->Dao->Utils->getClass($class);
        $pid=$ipfm->Options->getParentFooterGroupId();
        $array=$ipfm->Options->getArrayFooterItems($class, $pid);
        $limit=0;
        switch ($class) {
            case 'IPFM_FooterGroup':
                $limit=3;
                break;
            case 'IPFM_FooterElement':
                $limit=5;
                break;
        }
        $exceed=($limit>0 && count($array)>=$limit);
        if ($exceed && $notice) {
            $ipfm->Options->pushInfoMessage('LimitReached.'.$class, $limit, IPFM_TAB_PREMIUM_URI);
        }
        return $exceed;
    }
    public function changeOrder() {
        global $ipfm;
        $data=array();
        parse_str($_POST['order'], $data);

        if (isset($data['row'])) {
            $class=$ipfm->Options->getFooterClass();
            $all=$ipfm->Options->getArrayFooterItems($class, FALSE);
            if($ipfm->Options->isFooterGroup()) {
                $parent=FALSE;
                $array=$all;
            } else {
                $parent=$ipfm->Options->getParentFooterGroupId();
                $parent=$this->get('FooterGroup', $parent);
                $array=array();
                if(isset($all[$parent->id])) {
                    $array=$all[$parent->id];
                }
            }
            foreach($array as $k=>$v) {
                /* @var $v IPFM_FooterGroup */
                $v->order=0;
                $array[$k]=$v;
            }

            $index=1;
            foreach($data['row'] as $order=>$id) {
                /* @var $v IPFM_FooterGroup */
                $v=$array[$id];
                $v->order=$index;
                $array[$id]=$v;
                ++$index;
            }

            if($ipfm->Options->isFooterGroup()) {
                $all=$array;
            } else {
                $all[$parent->id]=$array;
            }
            $ipfm->Options->setArrayFooterItems($class, $all);
        }
        echo 'OK';
        wp_die();
    }
    public function query($class, $id=FALSE, $parent=FALSE) {
        global $ipfm;
        if(is_numeric($id) && intval($id)===0) {
            return FALSE;
        }

        if(is_bool($parent) && $parent!==FALSE) {
            $parent=$ipfm->Options->getParentFooterGroupId();
        } elseif(is_numeric($parent)) {
            $parent=intval($parent);
        } else {
            $parent=FALSE;
        }
        $array=$ipfm->Options->getArrayFooterItems($class, $parent);

        if($id!==FALSE) {
            $result=(isset($array[$id]) ? $array[$id] : FALSE);
        } else {
            $array=array_values($array);
            usort($array, array($this, 'query_Compare'));
            $result=$array;
        }
        return $result;
    }
    public function query_Compare($o1, $o2) {
        global $ipfm;
        $v1=$ipfm->Utils->iget($o1, 'order', FALSE);
        $v2=$ipfm->Utils->iget($o2, 'order', FALSE);
        return strcasecmp($v1, $v2);
    }
    public function get($class, $id, $parent=FALSE, $new=FALSE) {
        global $ipfm;
        $class=$ipfm->Dao->Utils->getClass($class);
        $result=$this->query($class, $id, $parent);
        /* @var $result IPFM_FooterElement */
        if($result===FALSE && $new) {
            switch ($class) {
                case 'IPFM_FooterGroup':
                    /* @var $result IPFM_FooterGroup */
                    $result=$ipfm->Dao->Utils->newDomainClass('FooterGroup');
                    $result->active=TRUE;
                    $result->name='New Group';
                    $result->deviceType=-1;
                    $result->everywhere=1;
                    break;
                case 'IPFM_FooterElement':
                    /* @var $result IPFM_FooterElement */
                    $result=$ipfm->Dao->Utils->newDomainClass('FooterElement');
                    $result->active=TRUE;
                    $result->name='New Element';
                    $result->marginBottom=0;
                    $result->marginTop=0;
                    $result->border=IPFM_FooterElementConstants::BORDER_DOTTED;
                    $result->height=50;
                    $result->borderHeight=5;
                    $result->effect=IPFM_FooterElementConstants::EFFECT_SHAKE;
                    $result->effectDuration=1;
                    break;
            }
        }
        return $result;
    }

    public function store($class, $instance) {
        global $ipfm;
        $result=TRUE;

        $items=$ipfm->Options->getArrayFooterItems($class);
        $instance->id=intval($instance->id);
        if($instance->id<=0) {
            $new=$ipfm->Options->getNewFooterItemId($class);
            $instance->id=$new;
        }

        if($result) {
            $class=$ipfm->Dao->Utils->getClass($class);
            switch ($class) {
                case 'IPFM_FooterGroup':
                    /* @var $instance IPFM_FooterGroup */
                    if(intval($instance->order)<=0) {
                        $instance->order=count($items)+1;
                    }
                    if($instance->everywhere) {
                        $instance->type='';
                    }
                    $items[$instance->id]=$instance;
                    break;
                case 'IPFM_FooterElement':
                    /* @var $instance IPFM_FooterElement */
                    $pid=$ipfm->Options->getParentFooterGroupId();
                    if(!isset($items[$pid])) {
                        $items[$pid]=array();
                    }
                    if(intval($instance->order)<=0) {
                        $instance->order=count($items[$pid])+1;
                    }
                    $items[$pid][$instance->id]=$instance;

                    $parents=$ipfm->Options->getArrayFooterItems('FooterGroup');
                    if(isset($parents[$pid])) {
                        /* @var $parent IPFM_FooterGroup */
                        $parent=$parents[$pid];
                        $parent->elementsCount=count($items[$pid]);
                        $parents[$pid]=$parent;
                    }
                    $ipfm->Options->setArrayFooterItems('FooterGroup', $parents);
                    break;
            }
            $ipfm->Options->setArrayFooterItems($class, $items);
        }
        return $result;
    }
    public function delete($class, $ids) {
        global $ipfm;

        $ids=$ipfm->Utils->toArray($ids);
        $class=$ipfm->Dao->Utils->getClass($class);
        $items=$ipfm->Options->getArrayFooterItems($class);
        $pid=FALSE;
        foreach($ids as $id) {
            $id=intval($id);
            switch ($class) {
                case 'IPFM_FooterGroup':
                    unset($items[$id]);
                    break;
                case 'IPFM_FooterElement':
                    $pid=$ipfm->Options->getParentFooterGroupId();
                    if(!isset($items[$pid])) {
                        $items[$pid]=array();
                    }
                    $elements=$items[$pid];
                    unset($elements[$id]);
                    $items[$pid]=$elements;
                    break;
            }
        }
        $ipfm->Options->setArrayFooterItems($class, $items);

        if($pid!==FALSE) {
            $parent=$this->get('FooterGroup', $pid);
            if($parent!==FALSE) {
                $parent->elementsCount=count($items[$pid]);
            }
            $this->store('FooterGroup', $parent);
        }
        return TRUE;
    }
    public function copy($class, $ids) {
        global $ipfm;
        $ids=$ipfm->Utils->toArray($ids);
        if(count($ids)==0) {
            return FALSE;
        }

        $class=$ipfm->Dao->Utils->getClass($class);
        $pid=$ipfm->Options->getParentFooterGroupId();
        $array=$ipfm->Options->getArrayFooterItems($class, $pid);
        $result=FALSE;
        $copy=FALSE;
        foreach($ids as $id) {
            $result=TRUE;
            if(isset($array[$id])) {
                /* @var $source IPFM_FooterElement */
                $source=$array[$id];
                $copy=clone($source);
                /* @var $copy IPFM_FooterElement */
                $copy->id=0;
                $copy->order=0;
                $copy->name='Copy of '.$copy->name;
                $this->store($class, $copy);
                if($class=='IPFM_FooterGroup') {
                    $ipfm->Options->setParentFooterGroupId($copy->id);
                    $array=$ipfm->Options->getArrayFooterItems('FooterElement', $source->id);
                    foreach($array as $k=>$v) {
                        /* @var $v IPFM_FooterElement */
                        $v->id=0;
                        $this->store('FooterElement', $v);
                    }
                    $ipfm->Options->setParentFooterGroupId(FALSE);
                }
            }
        }
        if($copy!==FALSE) {
            $ipfm->Ui->redirectEdit($copy->id);
        }
        return $result;
    }
}