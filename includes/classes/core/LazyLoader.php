<?php

class IPFM_LazyLoader {
    //WP
    public function WP_getTermsTypes(){
        global $ipfm;

        $result=array();
        $types=$ipfm->Utils->query(IPFM_QUERY_POST_TYPES);
        foreach($types as $v) {
            $pid=$v['id'];
            $result[]=array('id'=>'___IncludePosts___'.$pid, 'text'=>$ipfm->Lang->L('IncludePostOfType', $v['text']));
            $result[]=array('id'=>'___ExcludePosts___'.$pid, 'text'=>$ipfm->Lang->L('ExcludePostOfType', $v['text']));

            $options=array('type'=>$v['id']);
            $array=$ipfm->Utils->query(IPFM_QUERY_TAXONOMY_TYPES, $options);
            foreach($array as $v) {
                $id=$v['id'];
                $result[]=array('id'=>'___IncludeTerms___'.$pid.'___'.$id, 'text'=>$ipfm->Lang->L('IncludeTermOfType', $v['text']));
                $result[]=array('id'=>'___ExcludeTerms___'.$pid.'___'.$id, 'text'=>$ipfm->Lang->L('ExcludeTermOfType', $v['text']));
            }
        }
        return $result;
    }
    public function WP_getTermsItems($type){
        global $ipfm;

        $type=$ipfm->Utils->qs('_mapKey', $type);
        if($type=='') {
            return FALSE;
        }

        $utype=$type;
        $utype=explode('___', $utype);
        $utype=$utype[count($utype)-1];
        $args=array('type'=>$utype);
        if($ipfm->Utils->contains($type, '___Include')) {
            $args['all']=TRUE;
        } else {
            $args['all']=FALSE;
        }

        if($ipfm->Utils->contains($type, 'Posts___')) {
            $result=$ipfm->Utils->query(IPFM_QUERY_POSTS_OF_TYPE, $args);
        } else {
            $result=$ipfm->Utils->query(IPFM_QUERY_TAXONOMIES_OF_TYPE, $args);
        }
        return $result;
    }
    public function WP_getPostTypes(){
        global $ipfm;
        $result=$ipfm->Utils->query(IPFM_QUERY_POST_TYPES);
        return $result;
    }

    //Facebook
    public function Facebook_getAdAccounts($accountId=0) {
        global $ipfm;
        $result=$this->Facebook_getName();
        if($result!==FALSE) {
            return $result;
        }

        if($accountId===0 || (is_array($accountId) && count($accountId)==0)) {
            $accountId=$ipfm->Utils->qs('parentId', '');
        }

        $result=array();
        $profile=$ipfm->Options->getFacebookProfiles($accountId);
        if($profile!==FALSE && isset($profile['adAccounts'])) {
            $accounts=$profile['adAccounts'];
            foreach($accounts as $k=>$v) {
                if(!is_array($v) || !isset($v['name'])) {
                    $v=$k;
                } else {
                    $v=$v['name'].' ('.$k.')';
                }
                $result[]=array('id'=>$k, 'text'=>$v);
            }
        }
        return $result;
    }

    public function execute($action) {
        global $ipfm;
        $result=array();
        $function=array($this, $action);
        if($ipfm->Utils->functionExists($function)) {
            $result=$ipfm->Utils->functionCall($function);
        } else {
            $result['error']='NO FUNCTION '.$action.' DEFINED';
        }
        return $result;
    }
    public function executeJson($action) {
        $json=$this->execute($action);
        echo json_encode($json);
        return (count($json)>0 && !isset($json['error']));
    }

    private function getArgs($parents, $size) {
        global $ipfm;
        if($parents===0 || $parents=='' || (is_array($parents) && count($parents)==0)) {
            $parents=$ipfm->Utils->qs('parentId', 0);
        }

        $result=FALSE;
        $parents=$ipfm->Utils->toArray($parents);
        if(count($parents)==$size) {
            $result=$parents;
        }
        return $result;
    }
}