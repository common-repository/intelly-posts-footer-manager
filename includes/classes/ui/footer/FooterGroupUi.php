<?php
if (!defined('ABSPATH')) exit;

class IPFM_FooterGroupUi {
    public function __construct() {
    }

    public function html($options = array(), $shortcode = false)
    {
        global $ipfm;
        $ipfm->Options->setHtmlRendering(TRUE);
        /* @var $limit IPFM_FooterGroup */
        $limit=FALSE;
        if(is_object($options) && get_class($options)=='IPFM_FooterGroup') {
            $limit=$options;
            $options=array();
        }

        $defaults=array();
        $options=$ipfm->Utils->parseArgs($options, $defaults);

        $data=$this->getPageData();
        $groups=$ipfm->Manager->query('FooterGroup');
        if($groups!==FALSE && count($groups)>0) {
            $first=TRUE;
            foreach($groups as $groupdId=>$group) {
                /* @var $group IPFM_FooterGroup */
                if($limit!==FALSE && $limit->id!=$group->id) {
                    continue;
                }

                if ($this->isVisible($group, $data, $shortcode)) {
                    $elements=$ipfm->Manager->query('FooterElement', FALSE, $group->id);
                    if($elements!==FALSE && count($elements)>0) {
                        if($first) {
                            $attributes=array(
                                'id'=>'ipfm-content'
                            );
                            $attributes=$ipfm->Utils->getTextArgs($attributes);
                            echo '<div '.$attributes.'>';
                            $first=FALSE;
                        } else {
                            echo '<div style="clear:both"></div>';
                        }

                        $attributes=array(
                            'class'=>'ipfm-group ipfm-group-id-'.$group->id
                            , 'style'=>($group->backgroundColor=='' ? '' : 'background-color:'.$group->backgroundColor)
                        );
                        $attributes=$ipfm->Utils->getTextArgs($attributes);
                        echo '<div '.$attributes.'>';

                        $clear=FALSE;
                        foreach($elements as $elementId=>$element) {
                            /* @var $element IPFM_FooterElement */
                            $class=$ipfm->Utils->upperCamelCase($element->what);
                            $class='IPFM_'.$class.'ElementUi';
                            if(class_exists($class)) {
                                /* @var $class IPFM_BaseFooterElementUi */
                                $class=new $class();

                                $classes=array();
                                $classes[]='ipfm-item';
                                $classes[]='ipfm-item-type-'.$element->what;
                                $classes[]='ipfm-item-id-'.$element->id;

                                $styles=array();
                                if($element->backgroundColor!='') {
                                    $styles[]='background-color:'.$element->backgroundColor;
                                }
                                if($element->marginTop>0) {
                                    $styles[]='margin-top:'.$element->marginTop.'px';
                                }
                                if($element->marginBottom>0) {
                                    $styles[]='margin-bottom:'.$element->marginBottom.'px';
                                }
                                $styles=implode('; ', $styles);
                                $classes=implode(' ', $classes);

                                $attributes=array(
                                    'class'=>$classes
                                    , 'style'=>$styles
                                );
                                $attributes=$ipfm->Utils->getTextArgs($attributes);
                                echo '<div '.$attributes.'>';
                                $class->html($element, $options);
                                echo '</div>';

                                if($clear) {
                                    echo '<div style="clear:both;"></div>';
                                }
                                $clear=TRUE;
                            }
                        }
                        $settings=$ipfm->Options->getPluginSettings();
                        if($settings->showPoweredBy) {
                            $attributes=array(
                                'class'=>'ipfm-item ipfm-poweredby'
                                , 'style'=>'text-align: right; font-size:11px;'
                            );
                            $attributes=$ipfm->Utils->getTextArgs($attributes);
                            echo '<div '.$attributes.'>';
                            ?>
                            <a href="https://intellywp.com/posts-footer-manager/?utm_campaign=poweredby" target="_blank">
                                Powered By <b>Posts' Footer Manager</b>
                            </a>
                            <?php
                            echo '</div>';
                        }
                        echo '</div>';
                    }
                }
            }
            if(!$first) {
                echo '</div>';
            }
        }
        $ipfm->Options->setHtmlRendering(FALSE);
    }
    public function getPageData() {
        global $post, $ipfm;
        $data=new IPFM_PageData();
        if($post && isset($post->ID) && (is_page($post->ID) || is_single($post->ID))) {
            $data->postId=$post->ID;
            $data->postType=$post->post_type;

            $args=array('type'=>$data->postType);
            $items=$ipfm->Utils->query(IPFM_QUERY_TAXONOMY_TYPES, $args);
            foreach($items as $v) {
                $termType=$v['id'];

                $args=array('fields'=>'ids');
                $termsIds=wp_get_object_terms($data->postId, $termType, $args);
                $data->terms[$termType]=$termsIds;
            }
        } elseif(is_tax() || is_category() || is_tag()) {
            $k=get_queried_object();
            $k=$ipfm->Utils->get($k, 'name');

            global $wp_taxonomies;
            if(isset($wp_taxonomies[$k])) {
                $v=$wp_taxonomies[$k];
                $types=$ipfm->Utils->get($v, 'object_type');
                $types=$ipfm->Utils->toArray($types);
                foreach($types as $v) {
                    $data->postType=$v;
                }
            }
        }
        return $data;
    }

    public function isVisible(IPFM_FooterGroup $group, IPFM_PageData $data, $shortcode = false)
    {
        global $ipfm;
        $data->postId=intval($data->postId);
        if (!$group->active || $data->postId <= 0 || $data->postType == '') {
            return FALSE;
        }
        if($group->everywhere) {
            return TRUE;
        }
        if(is_array($group->type)) {
            if(count($group->type)>0) {
                $group->type=$group->type[0];
            } else {
                $group->type='';
            }
        }

        $group->deviceType=$ipfm->Utils->toArray($group->deviceType);
        if(count($group->deviceType)>0) {
            $detect=new IPFM_Mobile_Detect();
            if ($detect->isMobile()) {
                $type=IPFM_FooterGroupConstants::DEVICE_TYPE_MOBILE;
            } elseif($detect->isTablet()){
                $type=IPFM_FooterGroupConstants::DEVICE_TYPE_TABLET;
            } else { //if(!$detect->isMobile() && !$detect->isTablet()) {
                $type=IPFM_FooterGroupConstants::DEVICE_TYPE_DESKTOP;
            }

            $result=FALSE;
            if(in_array(-1, $group->deviceType) || in_array($type, $group->deviceType)) {
                $result=TRUE;
            }
            if(!$result) {
                return FALSE;
            }
        }

        $result=FALSE;
        if ($group->type == $data->postType || trim($group->type) === '' || $shortcode) {
            if($group->expert) {
                $t=$group->type;
                $some=FALSE;
                $include=TRUE;
                $exclude=FALSE;
                foreach($group->terms as $k=>$v) {
                    if($ipfm->Utils->contains($k, '___'.$t.'___') || $ipfm->Utils->endsWith($k, '___'.$t)) {
                        $ids=$ipfm->Utils->toArray($v);
                        $ids=$ipfm->Utils->toMap($ids);
                        if(count($ids)>0) {
                            $some=TRUE;
                            $term=explode('___', $k);
                            $term=$term[count($term)-1];

                            if($ipfm->Utils->contains($k, '___IncludePosts___')) {
                                if(!isset($ids[-1]) && !isset($ids[$data->postId])){
                                    $include=FALSE;
                                }
                            } elseif($ipfm->Utils->contains($k, '___ExcludePosts___')) {
                                if(isset($ids[-1]) || isset($ids[$data->postId])) {
                                    $exclude=TRUE;
                                }
                            } elseif($ipfm->Utils->contains($k, '___IncludeTerms___')) {
                                if(isset($data->terms[$term])) {
                                    $postTermsIds=$ipfm->Utils->toArray($data->terms[$term]);
                                    if(count($postTermsIds)>0) {
                                        foreach($postTermsIds as $postTermId) {
                                            if(!isset($ids[-1]) && !isset($ids[$postTermId])){
                                                $include=FALSE;
                                            }
                                        }
                                    }
                                }
                            } elseif($ipfm->Utils->contains($k, '___ExcludeTerms___')) {
                                if(isset($data->terms[$term])) {
                                    $postTermsIds=$ipfm->Utils->toArray($data->terms[$term]);
                                    if(count($postTermsIds)>0) {
                                        foreach($postTermsIds as $postTermId) {
                                            if(isset($ids[-1]) || isset($postTermId)) {
                                                $exclude=TRUE;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                if(!$some) {
                    $result=TRUE;
                } elseif($include && !$exclude) {
                    $result=TRUE;
                }
            } else {
                $result=TRUE;
            }
        }
        return $result;
    }
}