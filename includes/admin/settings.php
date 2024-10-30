<?php
function ipfm_ui_settings() {
    global $ipfm;

    $ipfm->Form->prefix='License';
    if($ipfm->Check->nonce()) {
        $action=$ipfm->Utils->qs('_action', '');
        switch ($action) {
            case 'Save':
                /* @var $newSettings IPFM_PluginSettings */
                $newSettings=$ipfm->Dao->Utils->qs('IPFM_PluginSettings');
                $ipfm->Options->setPluginSettings($newSettings);
                break;
        }
    }

    $settings=$ipfm->Options->getPluginSettings();
    $ipfm->Options->writeMessages();
    ?>
    <h2><?php $ipfm->Lang->P('Title.Settings')?></h2>
    <?php
    $ipfm->Form->formStarts();
    {
        $ipfm->Form->openPanel('PluginSection');
        {
            $fields='httpReferer|allowUsageTracking|showPoweredBy';
            $ipfm->Form->inputsForm($fields, $settings);

            $buttons=array();
            $button=array(
                'submit'=>TRUE
            );
            $buttons['Save']=$button;
            $options=array('buttons'=>$buttons);
        }
        $ipfm->Form->closePanel($options);
    }
    $ipfm->Form->formEnds();
}