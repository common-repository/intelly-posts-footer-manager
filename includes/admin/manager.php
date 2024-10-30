<?php
function ipfm_notice_pro_features() {
    global $ipfm;
    ?>
    <br/>
    <div class="message updated below-h2 iwp" style="width: 100%">
        <div style="height:10px;"></div>
        <?php
        $i=1;
        while($ipfm->Lang->H('Notice.ProHeader'.$i)) {
            $ipfm->Lang->P('Notice.ProHeader'.$i);
            echo '<br/>';
            ++$i;
        }
        $i=1;
        ?>
        <br/>
        <?php

        /*$options = array('public' => TRUE, '_builtin' => FALSE);
        $q=get_post_types($options, 'names');
        if(is_array($q) && count($q)>0) {
            sort($q);
            $q=implode(', ', $q);
            $q='(<b>'.$q.'</b>)';
        } else {
            $q='';
        }*/
        $q='';
        while($ipfm->Lang->H('Notice.ProFeature'.$i)) { ?>
            <div style="clear:both; margin-top: 2px;"></div>
            <div style="float:left; vertical-align:middle; height:24px; margin-right:5px; margin-top:-5px;">
                <img src="<?php echo IPFM_PLUGIN_IMAGES_URI?>tick.png" />
            </div>
            <div style="float:left; vertical-align:middle; height:24px;">
                <?php $ipfm->Lang->P('Notice.ProFeature'.$i, $q)?>
            </div>
            <?php ++$i;
        }
        ?>
        <div style="clear:both;"></div>
        <div style="height:10px;"></div>
        <div style="float:right;">
            <?php
            $url=IPFM_PAGE_PREMIUM.'?utm_source=free-users&utm_medium=wp-cta&utm_campaign=wp-plugin';
            ?>
            <a href="<?php echo $url?>" target="_blank">
                <b><?php $ipfm->Lang->P('Notice.ProCTA')?></b>
            </a>
        </div>
        <div style="height:10px; clear:both;"></div>
    </div>
    <br/>
<?php }

//clone
function ipfm_ui_install_demo() {
    global $ipfm;
    $array=$ipfm->Options->getArrayFooterItems('FooterGroup');
    if(count($array)==0) {
        $previous=$ipfm->Options->getParentFooterGroupId();

        $group=$ipfm->Manager->get('FooterGroup', 0, FALSE, TRUE);
        $group->name=$ipfm->Lang->L('Demo.GroupName');
        $group->everywhere=FALSE;
        $group->type='post';
        $group->expert=FALSE;
        $ipfm->Manager->store('FooterGroup', $group);

        $ipfm->Options->setParentFooterGroupId($group->id);

        $element=$ipfm->Manager->get('FooterElement', 0, $group->id, TRUE);
        $element->name=$ipfm->Lang->L('Demo.LineElementName');
        $element->what=IPFM_FooterElementConstants::WHAT_SEPARATOR;
        $element->border=IPFM_FooterElementConstants::BORDER_DOTTED;
        $element->color='#ff0000';
        $element->borderHeight=5;
        $ipfm->Manager->store('FooterElement', $element);

        $element=$ipfm->Manager->get('FooterElement', 0, $group->id, TRUE);
        $element->name=$ipfm->Lang->L('Demo.TextElementName');
        $element->what=IPFM_FooterElementConstants::WHAT_EDITOR;
        $element->wpEditor='Simple Text';
        $ipfm->Manager->store('FooterElement', $element);

        $ipfm->Options->setParentFooterGroupId($previous);
    }
}
function ipfm_ui_manager_clonefootergroup($ids) {
    ipfm_ui_manager_clone($ids);
}
function ipfm_ui_manager_clonefooterelement($ids) {
    ipfm_ui_manager_clone($ids);
}
function ipfm_ui_manager_clone($ids) {
    global $ipfm;
    $class=$ipfm->Options->getFooterClass();
    $success=$ipfm->Manager->copy($class, $ids);
    $ipfm->Options->pushMessage($success, 'CloneWelcomeBar');
    $ipfm->Ui->redirectManager();
}
//delete
function ipfm_ui_manager_deletefootergroup($ids) {
    ipfm_ui_manager_delete($ids);
}
function ipfm_ui_manager_deletefooterelement($ids) {
    ipfm_ui_manager_delete($ids);
}
function ipfm_ui_manager_delete($ids) {
    global $ipfm;
    $class=$ipfm->Options->getFooterClass();
    $success=$ipfm->Manager->delete($class, $ids);
    $ipfm->Options->pushMessage($success, 'Delete'.$class);
    $ipfm->Ui->redirectManager();
}
function ipfm_ui_check_incompatible_plugins() {
    global $ipfm;
    if(class_exists('PageExpirationRobot')) {
        $ipfm->Options->pushWarningMessage('PleaseDeactivatePageExpirationRobot');
    }
}
function ipfm_ui_manager() {
    global $ipfm;
    ipfm_ui_check_incompatible_plugins();
    $class=$ipfm->Options->getFooterClass();

    $go=$ipfm->Utils->qs('go');
    $id=$ipfm->Utils->iqs('id', 0);

    if($go!='') {
        $go=intval($go);
        switch ($go) {
            case 1:
                $ipfm->Options->setParentFooterGroupId($id);
                break;
            case 0:
                $ipfm->Options->setParentFooterGroupId(FALSE);
                break;
        }
        $ipfm->Ui->redirectManager();
    }

    $parent=FALSE;
    if(!$ipfm->Options->isFooterGroup()) {
        $pid=$ipfm->Options->getParentFooterGroupId();
        $parent=$ipfm->Manager->get('FooterGroup', $pid);
    }

    $ipfm->Form->prefix='Manager';
    $action=$ipfm->Utils->qs('_action', '');
    $function=FALSE;
    if($ipfm->Check->nonce() && $action!=='') {
        $action=strtolower($action);
        $ids=$ipfm->Utils->toArray($ipfm->Utils->qs('ids', array()));
        $onlyOne=FALSE;
        $allowEmpty=FALSE;
        if($ipfm->Utils->contains($action, 'clone')) {
            $onlyOne=TRUE;
        }
        if(!$allowEmpty && ($ipfm->Utils->isEmpty($ids))) {
            $ipfm->Options->pushWarningMessage('Select'.$class.'ToAction');
        } elseif(!$ipfm->Utils->isEmpty($ids) && count($ids)>1 && $onlyOne) {
            $ipfm->Options->pushWarningMessage('SelectOnlyOne'.$class.'ToAction');
        } else {
            $function='ipfm_ui_manager_'.$action;
            $ipfm->Utils->functionCall($function, $ids);
            $function=TRUE;
        }
    }

    if($id>0) {
        $instance=$ipfm->Manager->get($class, $id, $parent!==FALSE);
        if($instance!==FALSE && $function===FALSE) {
            $ipfm->Options->pushSuccessMessage($class.'Updated', $instance->name);
        }
    }

    $ipfm->Manager->isLimitReached($class, TRUE);
    $ipfm->Options->writeMessages();
    $backUri='';
    if($ipfm->Options->isFooterGroup()) { ?>
        <h2 class="mb10"><?php $ipfm->Lang->P('Title.Manager'.$class, IPFM_PLUGIN_NAME, IPFM_PLUGIN_VERSION)?></h2>
    <?php } else {
        $backUri=$ipfm->Utils->addQueryString(array('go'=>0), IPFM_TAB_MANAGER_URI);
        ?>
        <h2 class="mb0"><?php $ipfm->Lang->P('Title.Manager'.$class, $backUri, $parent->name)?></h2>
    <?php }

    if($ipfm->Options->isFooterGroup()) {
        $items=$ipfm->Manager->query($class);
    } else {
        $items=$ipfm->Manager->query($class, FALSE, $parent->id);
    }

    if (count($items)>0) {
        $options=array('class'=>'admin-form ipfm-manager-form');
        $ipfm->Form->formStarts($options);
        $ipfm->Form->hidden('_action', '');
        ?>
        <div style="float:left;">
            <?php
            if($backUri!='') {
                $button=array(
                    'theme'=>'grey'
                    , 'uri'=>$backUri
                );
                $ipfm->Form->button('Back', $button);
            }

            if(!$ipfm->Manager->isLimitReached($class, FALSE)) {
                $button=array(
                    'theme'=>'primary'
                    , 'uri'=>IPFM_TAB_EDITOR_URI
                    , 'id'=>'btnAdd'.$class
                    , 'name'=>'btnAdd'.$class
                );
                $ipfm->Form->button('Add', $button);

                $button=array(
                    'theme'=>'success'
                    , 'id'=>'btnClone'.$class
                    , 'name'=>'btnClone'.$class
                );
                $ipfm->Form->submit('Clone'.$class, $button);
            }

            $button=array(
                'theme'=>'danger'
                , 'prompt'=>TRUE
                , 'id'=>'btnDelete'.$class
                , 'name'=>'btnDelete'.$class
            );
            $ipfm->Form->submit('Delete'.$class, $button);
            ?>
        </div>
        <div style="clear:both;"></div>
        <br/>
        <h4 class="mb10"><?php $ipfm->Lang->P('Subtitle.Manager'.$class)?></h4>
        <?php
            $args=array('id'=>'tblSortable');
            if($ipfm->Options->isFooterGroup()) {
                $fields='@id|active|groupName|shortcode|deviceType|everywhere|type|elementsCount|buttons';
                $args['groupName']=array(
                    'align'=>'left'
                    , 'function'=>function($v) {
                        /* @var $v IPFM_FooterGroup */
                        echo $v->name;
                    }
                );
                $args['type']=array('function'=>'ipfm_column_FooterGroup_type');
                $args['shortcode']=array('function'=>'ipfm_column_FooterGroup_shortcode');
                $args['buttons']=array(
                    'align'=>'left'
                    , 'function'=>'ipfm_column_FooterGroup_buttons'
                );
            } else {
                $fields='@id|active|elementName|what|marginTop|marginBottom|buttons';
                $args['elementName']=array(
                    'align'=>'left'
                    , 'function'=>function($v) {
                        /* @var $v IPFM_FooterElement */
                        echo $v->name;
                    }
                );
                $args['buttons']=array(
                    'align'=>'left'
                    , 'function'=>'ipfm_column_FooterElement_buttons'
                );
            }
            $ipfm->Form->inputTable($fields, $items, $args);
            $ipfm->Form->br();
            $ipfm->Form->i('Footer.Manager'.$class);

            $ipfm->Form->br();
            ipfm_notice_pro_features();
        ?>
    <?php
        $ipfm->Form->formEnds();
        if(count($items)>1) {
            ipfm_manager_sortable_scripts();
        }
    } else { ?>
        <h2 class="mb0"><?php $ipfm->Lang->P('Empty'.$class.'List', IPFM_TAB_EDITOR_URI)?></h2>
    <?php }
}

function ipfm_manager_sortable_scripts() {
    ?>
    <style>
        .ui-state-highlight {
            border: 1px solid #F4E449!important;
            background-color: #F4E449!important;
        }
        #tblSortable tbody tr:hover {
            cursor: move!important;
        }
        #tblSortable tbody tr a:hover {
            cursor: hand!important;
        }
    </style>
    <script>
        jQuery(function() {
            var $sortable=jQuery("#tblSortable .table-body");
            $sortable.sortable({
                tolerance:'intersect'
                , cursor:'move'
                , items:'tr'
                , placeholder:'ui-state-highlight'
                , nested: 'tbody'
                , update: function(event, ui) {
                    var orders=$sortable.sortable('serialize');
                    var data={action: 'IPFM_changeOrder', order: orders};
                    jQuery.post(ajaxurl, data, function(result) {

                    });
                }
            });
            $sortable.disableSelection();
        });
    </script>
    <?php
}

function ipfm_column_FooterGroup_type($v) {
    /* @var $v IPFM_FooterGroup */
    global $ipfm;
    $text = 'Everywhere';
    if(!$v->everywhere) {
        $options=array();
        $ipfm->Form->getMasterAjaxDomain($v, 'type', $v->type, $options);
        if(isset($options['values'])) {
            $text=$ipfm->Form->optionsText($options['values'], $v->type);
        }
    }
    echo $text;
}
function ipfm_column_FooterGroup_shortcode($v) {
    /* @var $v IPFM_FooterGroup */
    global $ipfm;
    $args=array(
        'noLayout'=>TRUE
        , 'style'=>'width:100px'
        , 'class'=>'gui-input ipfm-select-onfocus text-center'
        , 'readonly'=>TRUE
    );
    $code = '[pfm id="'.$v->id.'"]';
    $ipfm->Form->text('', $code, $args);
}
function ipfm_column_FooterGroup_buttons($v) {
    global $ipfm;
    /* @var $v IPFM_FooterGroup */
    $uri=IPFM_TAB_EDITOR_URI.'&id='.$v->id;
    $button=array(
        'theme'=>'grey'
        , 'uri'=>$uri
        , 'class'=>'btn-sm'
        , 'id'=>''
        , 'name'=>''
    );
    $ipfm->Form->button('Edit', $button);

    $uri=IPFM_TAB_MANAGER_URI.'&id='.$v->id.'&go=1';
    $v->elementsCount=intval($v->elementsCount);
    $button=array(
        'theme'=>'primary'
        , 'uri'=>$uri
        , 'class'=>'btn-sm'
        , 'label'=>$ipfm->Lang->L('SeeElements', $v->elementsCount)
        , 'id'=>''
        , 'name'=>''
    );
    $ipfm->Form->button('SeeAlso', $button);
}
function ipfm_column_FooterElement_buttons($v) {
    global $ipfm;
    /* @var $v IPFM_FooterElement */
    $uri=IPFM_TAB_EDITOR_URI.'&id='.$v->id;
    $button=array(
        'theme'=>'grey'
        , 'uri'=>$uri
        , 'class'=>'btn-sm'
        , 'id'=>''
        , 'name'=>''
    );
    $ipfm->Form->button('Edit', $button);
}
