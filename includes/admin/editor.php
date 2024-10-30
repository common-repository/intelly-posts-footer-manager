<?php
function ipfm_ui_editor() {
    global $ipfm;

    $class=$ipfm->Options->getFooterClass();
    $id=$ipfm->Utils->iqs('id');
    if($id==0 && $ipfm->Manager->isLimitReached($class, FALSE)) {
        $ipfm->Ui->redirectManager();
    }

    $parent=FALSE;
    if(!$ipfm->Options->isFooterGroup()) {
        $pid=$ipfm->Options->getParentFooterGroupId();
        $parent=$ipfm->Manager->get('FooterGroup', $pid);
    }
    $instance=$ipfm->Manager->get($class, $id, $parent!==FALSE, TRUE);
    if($instance===FALSE && $id>0) {
        $ipfm->Ui->redirectManager();
    }

    $ipfm->Form->prefix='Editor';
    if($ipfm->Check->is('_action', 'Save1') || $ipfm->Check->is('_action', 'Save2')) {
        $instance=$ipfm->Dao->Utils->qs($class);
        if($ipfm->Options->isFooterGroup()) {
            /* @var $instance IPFM_FooterGroup */
            $fields='active|name|deviceType|everywhere|type';
        } else {
            /* @var $instance IPFM_FooterElement */
            $fields='active|name|what';
        }

        $all=TRUE;
        $ipfm->Ui->validateDomain($instance, $fields, $all);
        if(!$ipfm->Options->hasErrorMessages()) {
            $ipfm->Manager->store($class, $instance);
            if(!$ipfm->Options->hasErrorMessages()) {
                $ipfm->Ui->redirectManager($instance->id);
            }
        }
    }
    $ipfm->Options->writeMessages();
    ?>
    <h2><?php $ipfm->Lang->P('Title.Editor'.$class)?></h2>
    <?php

    $ipfm->Form->formStarts();
    {
        $ipfm->Form->hidden('id', $instance->id);
        $ipfm->Form->hidden('order', $instance->order);

        if($ipfm->Options->isFooterGroup()) {
            $ipfm->Form->hidden('elementsCount', $instance->elementsCount);
            /* @var $instance IPFM_FooterGroup */
            $title=($instance->id>0 ? 'Edit' : 'Add').$class;
            $ipfm->Form->openPanel($title);
            {
                $fields='active|name|deviceType|everywhere';
                $ipfm->Form->inputsForm($fields, $instance);

                $buttons=array();
                $button=array(
                    'submit'=>TRUE
                );
                $buttons['Save1']=$button;
                $options=array('id'=>'boxFooterSave', 'buttons'=>$buttons);
            }
            $ipfm->Form->closePanel($options);

            $options=array('id'=>'boxPosts', 'title'=>'Limitation');
            $ipfm->Form->openPanel($options);
            {
                $fields='type|expert|terms';
                $ipfm->Form->inputsForm($fields, $instance);

                ipfm_notice_pro_features();

                $buttons=array();
                $button=array(
                    'submit'=>TRUE
                );
                $buttons['Save2']=$button;
                $options=array('buttons'=>$buttons);
            }
            $ipfm->Form->closePanel($options);
        } else {
            /* @var $instance IPFM_FooterElement */
            $title=($instance->id>0 ? 'Edit' : 'Add').$class;
            $fields='active|name|what';
            $ipfm->Form->inputsPanel($title, $fields, $instance);

            $title=array('id'=>'boxBehaviour', 'title'=>'Behaviour', 'style'=>'display:none');
            $fields='border|color|shortcode|widget|wpEditor|htmlEditor|height|borderHeight';
            $ipfm->Form->inputsPanel($title, $fields, $instance);

            $ipfm->Form->openPanel('Aspect');
            {
                $fields='marginTop|marginBottom';
                $ipfm->Form->inputsForm($fields, $instance);

                ipfm_notice_pro_features();

                $buttons=array();
                $button=array(
                    'submit'=>TRUE
                );
                $buttons['Save1']=$button;
                $options=array('buttons'=>$buttons);
            }
            $ipfm->Form->closePanel($options);
        }
    }
    $ipfm->Form->formEnds();

    if($ipfm->Options->isFooterGroup()) {
        ipfm_editor_FooterGroup_scripts();
    } else {
        ipfm_editor_FooterElement_scripts();
    }
}
function ipfm_editor_FooterGroup_scripts() { ?>
    <script>
        function IPFM_inputChange() {
            var everywhere=(IPFM.check('everywhere')==1);
            var expert=(IPFM.check('expert')==1);

            if(everywhere) {
                jQuery('#boxPosts').hide();
                jQuery('#boxFooterSave').show();
            } else {
                var $type=jQuery('#type');
                var type=IPFM.val($type);
                /*if(type===undefined) {
                 type='';
                 }*/

                jQuery("div[id*='-row']").each(function() {
                    var $this=jQuery(this);
                    var id=IPFM.attr($this, 'id', '');

                    if(id.indexOf('___Include')>-1) {
                        if(type=='' || !expert) {
                            $this.hide();
                        } else {
                            if(id.indexOf('___'+type+'___')>-1 || id.indexOf('___'+type+'-row')>-1) {
                                $this.show();
                            } else {
                                $this.hide();
                            }
                        }
                    }
                });
                jQuery('#boxPosts').show();
                jQuery('#boxFooterSave').hide();
            }

            jQuery("select[id*='___Include']").each(function() {
                var $this=jQuery(this);
                var id=IPFM.attr($this, 'id', '');

                var $includeRow=jQuery('#'+id+'-row');
                var $excludeRow=jQuery('#'+id.replace('___Include', '___Exclude')+'-row');

                if(!$includeRow.is(':visible')) {
                    $excludeRow.hide();
                } else {
                    var value=IPFM.val($this);
                    if(value===undefined) {
                        value=[];
                    } else if(jQuery.type(value)=='string') {
                        value=value.split(',');
                    }
                    value='|'+value.join('|')+'|';

                    if(value.indexOf('|-1|')>-1) {
                        $excludeRow.show();
                    } else {
                        $excludeRow.hide();
                    }
                }
            });
        }

        jQuery('input,select,textarea').click(function() {
            IPFM_inputChange();
        });
        jQuery('input,select,textarea').change(function() {
            IPFM_inputChange();
        });

        IPFM_inputChange();
    </script>
<?php }
function ipfm_editor_FooterElement_scripts() { ?>
    <script>
        function IPFM_inputChange() {
            var $type=jQuery('#what');
            var type=IPFM.val($type);
            if(type=='') {
                jQuery('#boxBehaviour').hide();
            } else {
                jQuery('#boxBehaviour').show();
            }

            var effect=IPFM.val('effect');
            jQuery('.effect-desc').addClass('hidden');
            jQuery('.effect-desc-'+effect).removeClass('hidden');
        }

        jQuery('input,select,textarea').click(function() {
            IPFM_inputChange();
        });
        jQuery('input,select,textarea').change(function() {
            IPFM_inputChange();
        });
        IPFM_inputChange();
    </script>
<?php }

