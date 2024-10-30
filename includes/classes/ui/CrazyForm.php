<?php
if (!defined('ABSPATH')) exit;

class IPFM_CrazyForm {
    var $prefix='';
    var $namePrefix='';
    var $readonly=FALSE;

    var $labels=TRUE;
    var $newline;
    var $helps=FALSE;
    var $textCenter=FALSE;
    var $tooltips=FALSE;
    var $blockOpened=FALSE;

    private $search=FALSE;
    private $noncePresent=FALSE;
    private $hiddenActionCreated=FALSE;
    private $buttonPresent=FALSE;
    var $icon=FALSE;

    public function __construct() {
    }

    public function newline() { ?>
        <div class="ipfm-form-newline"></div>
    <?php }

    public function formStarts($options=array()) {
        global $ipfm;
        $defaults=array(
            'method'=>'POST'
            , 'action'=>''
            , 'class'=>''
            , 'openBlock'=>FALSE
        );
        $options=$ipfm->Utils->parseArgs($options, $defaults);
        ?>
        <form method="<?php echo $options['method']?>" action="<?php echo $options['action']?>" class="<?php echo $options['class']?>">
    <?php
        if($options['openBlock']) {
            $this->openBlock();
        }
    }
    public function formEnds($options=array()) {
        global $ipfm;
        $defaults=array(
            'noncePresent'=>$this->noncePresent
            , 'buttonPresent'=>$this->buttonPresent
        );
        $options=$ipfm->Utils->parseArgs($options, $defaults);

        if(!$options['noncePresent']) {
            $this->nonce();
        }
        if(!$options['buttonPresent']) {
            $this->submit();
        }
        $this->closeBlock();
        ?>
        </form>
        <?php /*<div style="clear:both;"></div>*/?>
        <?php
        $this->noncePresent=FALSE;
    }
    public function divStarts($args=array()) {
        global $ipfm;

        if(is_bool($args)) {
            $args=array('style'=>'display:'.($args ? 'block': 'none'));
        }

        $defaults=array();
        $other=$ipfm->Utils->getTextArgs($args, $defaults);
        ?>
        <div <?php echo $other?>>
    <?php }
    public function divEnds($clear=FALSE) { ?>
        </div>
        <?php if($clear) { ?>
            <div style="clear:both;"></div>
        <?php } ?>
    <?php }

    public function i($message, $v1=NULL, $v2=NULL, $v3=NULL, $v4=NULL, $v5=NULL) {
        global $ipfm; ?>
        <i><?php $ipfm->Lang->P($message, $v1, $v2, $v3, $v4, $v5) ?></i>
    <?php }
    public function p($message, $v1=NULL, $v2=NULL, $v3=NULL, $v4=NULL, $v5=NULL) {
        global $ipfm;
        ?>
        <p style="font-weight:bold;">
            <?php
            $ipfm->Lang->P($message, $v1, $v2, $v3, $v4, $v5);
            if($ipfm->Lang->H($message.'Subtitle')) { ?>
                <br/>
                <span style="font-weight:normal;">
                    <i><?php $ipfm->Lang->P($message.'Subtitle', $v1, $v2, $v3, $v4, $v5)?></i>
                </span>
            <?php } ?>
        </p>
    <?php }
    public function br() { ?>
        <br/>
    <?php }
    public function clearBoth() { ?>
        <div style="clear:both;"></div>
    <?php }
    private function getTooltipAttributes($tooltip, $options=array(), $echo=TRUE) {
        global $ipfm;
        if($tooltip===FALSE || $tooltip=='') {
            return;
        }

        $data=array(
            'data-toggle'=>'tooltip'
            , 'data-placement'=>'top'
            , 'title'=>$ipfm->Lang->L($tooltip)
        );
        $dump='';
        foreach($data as $k=>$v) {
            $dump.=' '.$k.'="'.str_replace('"', '', $v).'"';
        }
        if($echo) {
            echo $dump;
        } else {
            return $dump;
        }
    }
    private function openInput($name, $options=array()) {
        global $ipfm;

        $defaults=array(
            'name'=>$name
            , 'class'=>($this->icon ? 'field prepend-icon' : 'field prepend-noicon')
            , 'label'=>TRUE
            , 'textLabel'=>''
            , 'md9'=>TRUE
            , 'style'=>''
            , 'labelPrefix'=>''
            , 'col-md'=>'col-md-4'
            , 'tooltipPlacement'=>'top'
            , 'row-hidden'=>FALSE
            , 'mapKey'=>FALSE
        );
        $options=$ipfm->Utils->parseArgs($options, $defaults);
        if(!is_numeric($options['col-md'])) {
            $options['col-md']=str_replace('col-md-', '', $options['col-md']);
            if(!is_numeric($options['col-md'])) {
                $options['col-md']=4;
            }
        }
        $k=$this->prefix;
        if($k!='') {
            $k.='.';
        }
        $name=$options['name'];
        $name=str_replace('[]', '', $name);
        $class=$options['class'];
        $k.=$name;

        $label=$k;
        if(is_string($options['label'])) {
            $label=$options['label'];
        }

        $tooltip=(isset($options['tooltip']) ? $options['tooltip'] : '');
        if(!isset($options['tooltip']) && $this->tooltips) {
            $tooltip=$label.'.Tooltip';
        }
        //$mb=($this->search ? 'mb15' : 'row mb10');
        $mb=($this->search ? 'mb15' : 'row mb0');
        if($this->search) { ?>
            <h5><small><?php $ipfm->Lang->P($label) ?></small></h5>
        <?php }
        $style='';
        if($options['row-hidden']) {
            $style.='; display:none;';
        }
        ?>
        <div class="section <?php echo $mb?>" id="<?php $this->getName($name, $options) ?>-row" style="<?php echo $style?>">
            <?php if(!$this->search) { ?>
                <label for="<?php $this->getName($name, $options) ?>" class="field-label col-md-<?php echo $options['col-md']?> text-left" style="<?php echo $options['style'] ?>" <?php $this->getTooltipAttributes($tooltip, $options) ?>>
                    <?php
                    $textLabel='';
                    if(isset($options['textLabel']) && $options['textLabel']!='') {
                        $textLabel=$options['textLabel'];
                    } else {
                        if(isset($options['labelPrefix']) && $options['labelPrefix']!='') {
                            $textLabel=$ipfm->Lang->L($options['labelPrefix']);
                        }

                        $text='';
                        if($options['mapKey']!==FALSE) {
                            $text=$options['mapKey']['text'];
                        }
                        $textLabel.=' '.$ipfm->Lang->L($label, $text);
                    }
                    $textLabel=trim($textLabel);
                    echo $textLabel;
                    ?>
                </label>
                <?php if($options['md9']) { ?>
                    <div class="col-md-<?php echo (12-$options['col-md'])?>">
                <?php } ?>
            <?php }
            if(is_bool($options['label']) && $options['label']) { ?>
                <label for="<?php $this->getName($name, $options)?>" class="<?php echo $class?>">
            <?php }
    }
    private function closeInput($name, $options=array()) {
        global $ipfm;

        $defaults=array(
            'name'=>$name
            , 'class'=>'field-icon'
            , 'label'=>TRUE
            , 'md9'=>TRUE
        );
        $options=$ipfm->Utils->parseArgs($options, $defaults);
        $name=$options['name'];
        $icon='';
        if($this->icon) {
            if(!isset($options['icon']) ||
                ($options['icon']!==FALSE) && $options['icon']!=='') {
                $icon=$this->getIcon($name);
            }
        }
        if($icon!='') {
            if ($options['class'] == $defaults['class']) { ?>
                <label for="<?php $this->getName($name, $options) ?>" class="field-icon">
                    <i class="fa fa-<?php echo $icon ?>"></i>
                </label>
            <?php } else { ?>
                <i class="<?php echo $options['class'] ?>"></i>
            <?php }
        }
        if($options['label']) { ?>
            </label>
        <?php }
        if(!$this->search) {
            if(isset($options['afterLabel']) && $options['afterLabel']!='') {
                echo $options['afterLabel'];
            }
            if($options['md9']) { ?>
            </div>
        <?php }
        } ?>
    </div>
    <?php }

    public function getIcon($name) {
        global $ipfm;
        $icons=array(
            'user'=>'name|surname|username|user'
            , 'barcode'=>'taxCode|key'
            , 'envelope-o'=>'email|send'
            , 'at'=>'email'
            , 'phone'=>'phone'
            , 'lock'=>'password'
            , 'unlock'=>'confirmPassword|disconnect'
            , 'mobile'=>'mobile'
            , 'fax'=>'fax'
            , 'map-marker'=>'address'
            , 'certificate'=>'star|zip'
            , 'building-o'=>'region|province|country|city|place'
            , 'euro'=>'price|currency|amount|cost|advance'
            , 'edit'=>'note|description|body|subject|comment'
            , 'globe'=>'website|site'
            , 'tag'=>'tag'
            , 'calendar'=>'date|dt1|dt2'
            , 'home'=>'home|company'
            , 'clock-o'=>'time'
            , 'arrows-v'=>'scroll'
            , 'floppy-o'=>'save'
            , 'angle-double-right'=>'next'
            , 'angle-double-left'=>'previous|back'
            , 'trash-o'=>'remove|delete|trash'
            , 'refresh'=>'sync|refresh|change'
            , 'plus-circle'=>'add|plus'
            , 'clone'=>'clone'
            , 'ban'=>'ban|cancel|abort'
            , 'facebook-square'=>'facebook|fb|fbconnect'
            , 'plug'=>'plug|authorize'
            , 'eye'=>'eye|see'
            , 'bug'=>'bug|error'
            , 'sign-in'=>'login'
            , 'thumbs-o-down'=>'suspend|stop|deactivate'
            , 'thumbs-o-up'=>'activate'
            , 'slack'=>'id|day'
            , 'undo'=>'undo'
            , 'pencil'=>'edit'
            , 'check-square-o'=>'finish'
            , 'check'=>'confirm'
            , 'upload'=>'import'
        );
        $result=$ipfm->Utils->match($name, $icons, 'question');
        return $result;
    }

    private function timerText($name, $suffix, $value) {
        global $ipfm;
        $name.=$suffix;

        $options=array(
            'noLayout'=>TRUE
            , 'class'=>'gui-input col-xs-1 text-center'
            , 'style'=>'width:10%'
        );
        $value=intval($value);
        $this->number($name, $value, $options);
        ?>
        <label for="<?php $this->getName($name) ?>" class="field-label col-xs-1 text-center" style="width:10%">
            <?php $ipfm->Lang->P(lcfirst($suffix))?>
        </label>
    <?php }
    public function timer($name, $value='', $options=array()) {
        global $ipfm;
        if(!is_array($options)) {
            $options=array();
        }

        $value=$ipfm->Utils->get($name, $value, $value);
        $value=$ipfm->Utils->formatTimer($value);
        $values=explode(':', $value);

        if($ipfm->Utils->get($options, 'noLayout', FALSE)===FALSE) {
            $args=array();
            $args=$this->getLabelOptions($args, $options);
            $args['label']=FALSE;
            $this->openInput($name, $args);
        }

        $this->timerText($name, 'Days', $values[0]);
        $this->timerText($name, 'Hours', $values[1]);
        $this->timerText($name, 'Minutes', $values[2]);
        $this->timerText($name, 'Seconds', $values[3]);

        $options['class']='ipfm-timer';
        $this->hidden($name, $value, $options);

        if($ipfm->Utils->get($options, 'noLayout', FALSE)===FALSE) {
            $args=array();
            $args['label']=FALSE;
            $this->closeInput($name, $args);
        }
    }
    public function upload($name, $value='', $options=array()) {
        global $ipfm;
        $default=array(
            'multiple'=>FALSE
        );
        $options=$ipfm->Utils->parseArgs($options, $default);

        $value=$ipfm->Utils->get($name, $value, $value);
        if($ipfm->Utils->get($options, 'noLayout', FALSE)===FALSE) {
            $args=array(
                'label'=>TRUE
            );
            $args=$this->getLabelOptions($args, $options);
            $this->openInput($name, $args);
        }
        $multiple=($options['multiple'] ? 'true' : 'false');
        ?>
        <span class="btn btn-primary ipfm-upload-button" ui-multiple="<?php echo $multiple ?>" data-id="<?php echo $name ?>">
            <?php $ipfm->Lang->P('Upload.Button')?>
        </span>
        <?php
        $args=array(
            'noLayout'=>TRUE
            , 'class'=>'gui-input text-left'
            , 'placeholder'=>$ipfm->Lang->L('Upload.Placeholder')
        );
        $this->text($name, $value, $args);

        if($ipfm->Utils->get($options, 'noLayout', FALSE)===FALSE) {
            $args=array();
            $args['label']=FALSE;
            $this->closeInput($name, $args);
        }
    }
    public function text($name, $value='', $options=array()) {
        global $ipfm;

        $value=$ipfm->Utils->get($name, $value, $value);
        if($name=='') {
            $name='u'.md5(uniqid('a'));
        }
        $type='text';
        if(isset($options['type'])) {
            $type=$options['type'];
        }

        $defaults=array('class'=>'gui-input');
        if($this->textCenter) {
            $defaults['class'].=' text-center';
            if(isset($options['class'])) {
                $options['class'].=' text-center';
            }
        }
        $other=$ipfm->Utils->getTextArgs($options, $defaults, 'type|label|noLayout|textLabel');
        $options=$ipfm->Utils->parseArgs($options, $defaults);

        if($ipfm->Utils->get($options, 'noLayout', FALSE)===FALSE) {
            $args=array();
            $args=$this->getLabelOptions($args, $options);
            $this->openInput($name, $args);
        }
        ?>
        <input type="<?php echo $type?>" id="<?php $this->getName($name, $options) ?>" name="<?php $this->getName($name, $options) ?>" <?php echo $other?> />
        <script>
            jQuery('#<?php $this->getName($name, $options) ?>').val("<?php echo str_replace('"', '\"', $value) ?>");
        </script>
        <?php
        if($ipfm->Utils->get($options, 'noLayout', FALSE)===FALSE) {
            $this->closeInput($name, $args);
        }
    }
    private function getName($name, $options=array(), $echo=TRUE) {
        $name=$this->namePrefix.$name;
        $name=str_replace('.', '_', $name);
        if($options===FALSE) {
            $options=array();
            $echo=FALSE;
        }

        if(isset($options['mapKey']) && is_array($options['mapKey']) && isset($options['mapKey']['id'])) {
            $name.='___'.$options['mapKey']['id'];
        }

        //if(!is_array($options)) {
        //    $options=array();
        //}
        //dopo se lo faccio potrebbe succedere un casino con le validazioni etc
        //inoltre poi un campo senza nome nn può essere READONLY
        //if(count($options)>0 && isset($options['readonly']) && $options['readonly']!='') {
        //    $name='';
        //}

        if($echo) {
            echo $name;
        } else {
            return $name;
        }
    }
    public function hidden($name, $value='', $options=NULL) {
        global $ipfm;
        if($name=='_action') {
            $this->hiddenActionCreated=TRUE;
        }
        $value=$ipfm->Utils->get($name, $value, $value);
        if(is_bool($value)) {
            $value=($value ? 1 : 0);
        }
        $defaults=array();
        $other=$ipfm->Utils->getTextArgs($options, $defaults, 'type|label|noLayout|textLabel');
        ?>
        <input type="hidden" id="<?php $this->getName($name) ?>" name="<?php $this->getName($name) ?>" value="<?php echo $value ?>" <?php echo $other?> />
    <?php }

    public function nonce($action='nonce', $name='_wpnonce', $referer=true, $echo=true) {
        if($name=='') {
            $name=$action;
        }
        $this->noncePresent=TRUE;
        wp_nonce_field($action, $name, $referer, $echo);
    }

    public function textarea($name, $value='', $options=NULL) {
        global $ipfm;

        $value=$ipfm->Utils->get($name, $value, $value);
        //$defaults=array('rows'=>10, 'class'=>'gui-textarea');
        $defaults=array('class'=>'gui-textarea');
        $other=$ipfm->Utils->getTextArgs($options, $defaults, 'noLayout|textLabel');
        $options=$ipfm->Utils->parseArgs($options, $defaults);

        if($ipfm->Utils->get($options, 'noLayout', FALSE)===FALSE) {
            $args=array();
            $args=$this->getLabelOptions($args, $options);
            $this->openInput($name, $args);
        }
        $text=str_replace("'", "\\'", $value);
        $text=str_replace("\n", "\\n", $text);
        ?>
            <textarea dir="ltr" dirname="ltr" id="<?php $this->getName($name, $options) ?>" name="<?php $this->getName($name, $options) ?>" <?php echo $other?> ></textarea>
        <script>
            jQuery('#<?php $this->getName($name, $options) ?>').val('<?php echo $text ?>');
        </script>
        <?php
        if($ipfm->Utils->get($options, 'noLayout', FALSE)===FALSE) {
            $args=array();
            $this->closeInput($name, $args);
        }
    }
    public function email($name, $value='', $options=NULL) {
        global $ipfm;
        $defaults=array('type'=>'email');
        $options=$ipfm->Utils->parseArgs($options, $defaults);
        $this->text($name, $value, $options);
    }
    public function password($name, $value='', $options=NULL) {
        global $ipfm;
        $defaults=array('type'=>'password');
        $options=$ipfm->Utils->parseArgs($options, $defaults);
        $this->text($name, $value, $options);
    }
    public function currency($name, $value='', $options=NULL) {
        global $ipfm;
        //number does not support comma
        //$defaults=array('type'=>'number');
        //$options=$ec->Utils->parseArgs($options, $defaults);
        $this->text($name, $value, $options);
    }
    public function number($name, $value='', $options=NULL) {
        global $ipfm;
        $defaults=array('type'=>'number');
        $options=$ipfm->Utils->parseArgs($options, $defaults);
        $this->text($name, $value, $options);
    }
    public function tags($name, $value, $values, $options=NULL) {
        global $ipfm;
        if(!is_array($options)) {
            $options=array();
        }
        $options['type']='tags';
        $value=$ipfm->Utils->toArray($value);
        foreach($value as $k) {
            $exists=FALSE;
            foreach($values as $v) {
                if($v['id']==$k) {
                    $exists=TRUE;
                    break;
                }
            }

            if(!$exists) {
                $values[]=array('id'=>$k, 'text'=>$k);
            }
        }
        $this->dropdown($name, $value, $values, TRUE, $options);
    }
    public function multiselect($name, $value, $values, $options=array()) {
        if(!is_array($options)) {
            $options=array();
        }
        $options['type']='multiselect';
        $options['optgroup']=TRUE;
        $this->dropdown($name, $value, $values, TRUE, $options);
    }
    public function dropdown($name, $value, $values, $multiple=FALSE, $options=NULL) {
        global $ipfm;
        $value=$ipfm->Utils->get($name, $value, $value);

        if(!is_array($options)) {
            $options=array();
        }
        if(isset($options['readonly'])) {
            $options['disabled']="disabled";
            unset($options['readonly']);
        }
        if(isset($options['multiple'])) {
            $multiple=$options['multiple'];
            unset($options['multiple']);
        }
        if(!isset($options['type'])) {
            $options['type']='dropdown';
        }
        if(!isset($options['class'])) {
            $options['class']='';
        }
        $options['class'].=' ipfm-'.$options['type'];

        $help=$this->prefix;
        if($help!='') {
            $help.='.';
        }
        $help.=$name.'.Help';
        if($ipfm->Lang->H($help)) {
            $help=$ipfm->Lang->L($help);
        } else {
            $help='Dropdown.'.($multiple ? 'SelectAtLeastOneValue' : 'SelectOneValue');
            if($options['type']=='tags') {
                $help='Dropdown.SelectTagValue';
            }
            $help=$ipfm->Lang->L($help);
        }

        $defaults=array(
            'class'=>$options['class']
            , 'ipfm-ajax'=>''
            , 'ipfm-lazy'=>''
            , 'ipfm-domain'=>''
            , 'ipfm-class'=>''
            , 'ipfm-help'=>$help
            , 'optgroup'=>FALSE
        );
        $other=$ipfm->Utils->getTextArgs($options, $defaults, 'title|noLayout|type|optgroup|textLabel');
        $options=$ipfm->Utils->parseArgs($options, $defaults);

        if(!is_array($value)) {
            $value=array($value);
        }
        if(is_string($values)) {
            $values=explode(',', $values);
        }
        if(is_array($values) && count($values)>0) {
            if(!isset($values[0]['id']) && !isset($values[0]['text'])) {
                //this is a normal array so I use the values for "id" field and the "name" into the txt file
                $temp=array();
                foreach($values as $v) {
                    if(is_numeric($v) || !is_null($v)) {
                    $temp[]=array('id'=>$v, 'text'=>$ipfm->Lang->L($this->prefix.'.'.$name.'.'.$v));
                    }
                }
                $values=$temp;
            }
        }

        foreach($value as $v) {
            if(is_numeric($v) && intval($v)==-1) {
                //[All] option
                $value=array(-1);
                break;
            }
        }

        //sort array
        $values=$ipfm->Utils->sortOptions($values);
        if($ipfm->Utils->get($options, 'noLayout', FALSE)===FALSE) {
            $args=array('class'=>'field select');
            $args=$this->getLabelOptions($args, $options);
            $this->openInput($name, $args);
        }
        ?>
        <select id="<?php $this->getName($name, $options) ?>" name="<?php $this->getName($name, $options) ?><?php echo ($multiple ? '[]' : '')?>" <?php echo ($multiple ? 'multiple="multiple"' : '')?> <?php echo $other?> style="display:none;">
            <?php
            if($options['optgroup']) {
                $label=$this->prefix.'.'.$name.'.Optgroup';
                echo '<optgroup label="'.$ipfm->Lang->L($label).'">';
            }
            foreach($values as $v) {
                $other='';
                if(isset($v['style'])) {
                    $other.=' style="'.$v['style'].'"';
                }
                if(isset($v['data'])) {
                    $other.=' data="'.$v['data'].'"';
                }
                if(isset($v['show'])) {
                    $other.=' show="'.$v['show'].'"';
                }

                $selected='';
                if($ipfm->Utils->inArray($v['id'], $value)) {
                    $selected=' selected="selected"';
                }
                ?>
                <option value="<?php echo $v['id']?>" <?php echo $selected?> <?php echo $other?>><?php echo (isset($v['text']) ? $v['text'] : $v['name'])?></option>
            <?php }
            if($options['optgroup']) {
                echo '</optgroup>';
            }
            ?>
        </select>
        <?php
        if($ipfm->Utils->get($options, 'noLayout', FALSE)===FALSE) {
            $args=array('icon'=>FALSE);
            $this->closeInput($name, $args);
        }
    }

    private function getLabelOptions($labelOptions, $componentOptions) {
        if(isset($componentOptions['row-hidden'])) {
            $labelOptions['row-hidden']=$componentOptions['row-hidden'];
        }
        if(isset($componentOptions['textLabel'])) {
            $labelOptions['textLabel']=$componentOptions['textLabel'];
        }
        if(isset($componentOptions['mapKey'])) {
            $labelOptions['mapKey']=$componentOptions['mapKey'];
        }
        return $labelOptions;
    }
    public function checklist($name, $value, $values, $options=NULL) {
        global $ipfm;
        $defaults=array('type'=>'checkbox');
        $options=$ipfm->Utils->parseArgs($options, $defaults);

        $selected=$ipfm->Utils->get($name, $value, $value);
        $selected=$ipfm->Utils->toArray($selected);

        if($ipfm->Utils->get($options, 'noLayout', FALSE)===FALSE) {
            $args=array(
                //switch-round
                'class'=>'switch switch-primary block mt5'
                , 'icon'=>FALSE
                , 'label'=>FALSE
            );
            $args=$this->getLabelOptions($args, $options);
            $this->openInput($name, $args);
        }
        ?>
        <div class="option-group field">
            <?php foreach($values as $v) {

                $k=$v['id'];
                if(isset($v['text'])) {
                    $v=$v['text'];
                } else {
                    $v=$v['name'];
                }

                $checked=in_array($k, $selected);
                ?>
                <label class="option option-primary block mt10">
                    <input type="<?php echo $options['type']?>" name="<?php $this->getName($name, $options) ?>[]" id="<?php $this->getName($name) ?>_<?php echo $k ?>" value="<?php echo $selected?>" <?php echo ($checked ? 'checked="checked"' : '')?>>
                    <span class="<?php echo $options['type']?>"></span><?php echo $v?>
                </label>
            <?php } ?>
        </div>
        <?php
        if($ipfm->Utils->get($options, 'noLayout', FALSE)===FALSE) {
            $this->closeInput($name, $args);
        }
    }
    public function radiolist($name, $value, $values, $options=NULL) {
        if(!$options || !is_array($options)) {
            $options=array();
        }
        $options['type']='radio';
        $this->checklist($name, $value, $values, $options);
    }
    function getInfoUpload($value) {
        global $ipfm;
        $files=array();
        $text='';
        $value=$ipfm->Utils->toArray($value);
        foreach($value as $v) {
            if($v=='') {
                continue;
            }

            $v=str_replace("\\", "/", $v);
            $v=explode("/", $v);

            if($text!='') {
                $text.=',';
            }
            $text.=$v[count($v)-1];
            $v=IPFM_UPLOAD_BASE_DIR.implode("/", $v);
            $f=$ipfm->Utils->getFileInfo($v);
            if($f!==FALSE) {
                $files[]=$f;
            }
        }

        $result=array(
            'files'=>$files
            , 'text'=>$text
        );
        return $result;
    }
    public function checkbox($name, $value='', $selected=1, $options=array()) {
        if(!is_array($options)) {
            $options=array();
        }
        $options['class']='checkbox-custom checkbox-primary block mt5'; //mb5 mt10
        $this->toggle($name, $value, $selected, $options);
    }
    public function toggle($name, $value='', $selected=1, $options=array()) {
        global $ipfm;

        $value=$ipfm->Utils->get($name, $value, $value);
        if(is_bool($value)) {
            $value=($value ? 1 : 0);
        }
        $checked=($value==$selected);
        $id=$name;
        if($ipfm->Utils->endsWith($id, '[]')) {
            $id=substr($id, 0, strlen($id)-2);
            $id.='_'.$selected;
        }

        $defaults=array(
            'data-on'=>$ipfm->Lang->L('Toggle.Yes')
            , 'data-off'=>$ipfm->Lang->L('Toggle.No')
            , 'afterText'=>''
            , 'class'=>'switch switch-round switch-primary block mt5' //mt10
            , 'ui-visible'=>''
        );
        $options=$ipfm->Utils->parseArgs($options, $defaults);
        $otherText='';
        if($options['ui-visible']) {
            $otherText=' ui-visible="'.$options['ui-visible'].'" ';
        }
        $disabled='';
        if(isset($options['disabled']) || isset($options['readonly'])) {
            $options['readonly']='readonly';
            $disabled='disabled="disabled"';
        }

        if($ipfm->Utils->get($options, 'noLayout', FALSE)===FALSE) {
            $args=array(
                'class'=>$options['class']
                , 'icon'=>FALSE
            );
            $args=$this->getLabelOptions($args, $options);
            $this->openInput($id, $args);
        } else { ?>
            <label for="<?php $this->getName($id)?>" class="<?php echo $options['class']?>">
        <?php }

        $dataCss='';
        if(strpos($options['class'], 'checkbox')!==FALSE) {
            $dataCss='height:21px; ';
        }
        ?>
        <input type="checkbox" name="<?php $this->getName($name, $options) ?>" id="<?php $this->getName($id) ?>" value="<?php echo $selected?>" <?php echo ($checked ? 'checked="checked"' : '')?> <?php echo $disabled?>  <?php echo $otherText?>>
        <label for="<?php echo $id ?>" data-on="<?php echo $options['data-on'] ?>" data-off="<?php echo $options['data-off'] ?>" style="<?php echo $dataCss?>"></label>
        <?php if($options['afterText']!='') { ?>
            <span><?php echo $options['afterText']?></span>
        <?php } ?>

        <?php
        if($ipfm->Utils->get($options, 'noLayout', FALSE)===FALSE) {
            $this->closeInput($name, $args);
        } else { ?>
            </label>
        <?php }
    }

    var $_aceEditorUsed=FALSE;

    public function editor($name, $value='', $options=NULL) {
        global $ipfm;

        $defaults=array(
            'editor'=>'wp'
            , 'theme'=>'monokai'
            , 'ui-visible'=>''
            , 'height'=>200
        );
        $options=$ipfm->Utils->parseArgs($options, $defaults);

        $value=$ipfm->Utils->get($name, $value, $value);
        if($ipfm->Utils->get($options, 'noLayout', FALSE)===FALSE) {
            $args=array();
            $args=$this->getLabelOptions($args, $options);
            $this->openInput($name, $args);
        }

        $id=$this->getName($name, $options, FALSE);
        switch ($options['editor']) {
            case 'wp':
            case 'wordpress':
                $settings=array(
                    'wpautop'=>TRUE
                    , 'media_buttons'=>TRUE
                    , 'drag_drop_upload'=>FALSE
                    , 'editor_height'=>$options['height']
                );
                wp_editor($value, $id, $settings);
                ?>
                <script>
                    jQuery('#<?php echo $id?>').attr('ui-visible', '<?php echo $options['ui-visible']?>');
                </script>
                <?php
                break;
            case 'html':
                if(!$this->_aceEditorUsed) { ?>
                    <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.2.3/ace.js" type="text/javascript" charset="utf-8"></script>
                    <?php $this->_aceEditorUsed=TRUE;
                }

                $ace='ACE_'.$id;
                ?>
                <pre id="<?php echo $id?>Ace" style="height:<?php echo $options['height']+50?>px;"><?php echo $value?></pre>
                <textarea id="<?php echo $id?>" name="<?php echo $id?>" ui-visible="<?php echo $options['ui-visible']?>" style="display: none;"></textarea>
                <script>
                    var text=jQuery('#<?php echo $id?>Ace').html();
                    var <?php echo $ace?>=ace.edit("<?php echo $id?>Ace");
                    <?php echo $ace?>.setTheme("ace/theme/<?php echo $options['theme']?>");
                    <?php echo $ace?>.getSession().setMode("ace/mode/<?php echo $options['editor']?>");
                    <?php echo $ace?>.getSession().setUseSoftTabs(true);
                    <?php echo $ace?>.getSession().setUseWrapMode(true);
                    <?php echo $ace?>.setValue(text);

                    jQuery('#<?php echo $id?>Ace').focusout(function() {
                        var $hidden=jQuery('#<?php echo $id?>');
                        var code=<?php echo $ace?>.getValue();
                        $hidden.html(code);
                    });
                    jQuery('#<?php echo $id?>Ace').trigger('focusout');
                </script>
                <?php
                break;
        }
        if($ipfm->Utils->get($options, 'noLayout', FALSE)===FALSE) {
            $this->closeInput($name, $args);
        }
    }
    public function colorpicker($name, $value='', $options=NULL) {
        global $ipfm;

        $defaults=array('ui-visible'=>'');
        $options=$ipfm->Utils->parseArgs($options, $defaults);

        $value=$ipfm->Utils->get($name, $value, $value);
        if($ipfm->Utils->get($options, 'noLayout', FALSE)===FALSE) {
            $args=array();
            $args=$this->getLabelOptions($args, $options);
            $this->openInput($name, $args);
        }
        ?>
        <div class="input-group colorpicker-component cursor ipfm-colorpicker">
            <span class="input-group-addon">
                <i></i>
            </span>
            <input type="text" id="<?php $this->getName($name) ?>" name="<?php $this->getName($name, $options) ?>" value="<?php echo $value?>" class="gui-input" ui-visible="<?php echo $options['ui-visible']?>"/>
        </div>
        <?php
        if($ipfm->Utils->get($options, 'noLayout', FALSE)===FALSE) {
            $this->closeInput($name, $args);
        }
    }
    public function date($name, $value='', $options=NULL) {
        global $ipfm;

        $value=$ipfm->Utils->get($name, $value, $value);
        $value=$ipfm->Utils->formatDate($value);

        $defaults=array('class'=>'gui-input ipfm-date');
        $options=$ipfm->Utils->parseArgs($options, $defaults);
        $this->text($name, $value, $options);
    }
    public function time($name, $value='', $options=NULL) {
        global $ipfm;

        $value=$ipfm->Utils->get($name, $value, $value);
        $value=$ipfm->Utils->formatTime($value);

        $defaults=array('class'=>'gui-input ipfm-time');
        $options=$ipfm->Utils->parseArgs($options, $defaults);
        $this->text($name, $value, $options);
    }
    public function datetime($name, $value='', $options=NULL) {
        global $ipfm;

        $value=$ipfm->Utils->get($name, $value, $value);
        $value=$ipfm->Utils->formatDatetime($value);

        $defaults=array('class'=>'gui-input ipfm-datetime');
        $options=$ipfm->Utils->parseArgs($options, $defaults);
        $this->text($name, $value, $options);
    }
    function getMasterAjaxDomain($instance, $name, $value, &$options, $params=array()) {
        global $ipfm;
        $result=FALSE;

        if(!is_array($params)) {
            $params=array();
        }
        if(isset($params['values'])) {
            $options['values']=$params['values'];
            return TRUE;
        }

        if(is_array($instance) && $name===TRUE) {
            //tricks :(
            $column=$instance;
        } else {
            $column=$ipfm->Dao->Utils->getColumn($instance, $name);
        }

        $parentId=FALSE;
        $parent=$ipfm->Utils->get($column, 'ui-master', '');
        if($parent!=='') {
            $array=explode('|', $parent);
            $parentId=array();
            foreach($array as $v) {
                $parentId[]=$ipfm->Utils->get($instance, $v);
            }
            $parentId=implode('|', $parentId);
            $options['ipfm-master']=$parent;
            $result=TRUE;
        }
        //domain
        $domain=$ipfm->Utils->get($column, 'ui-domain', '');
        if($domain!=='') {
            $options['ipfm-domain']=$domain;
            $_POST['domain']=$domain;
            $result=TRUE;
        }

        //ajax
        $action=$ipfm->Utils->get($column, 'ui-ajax', '');
        if($action!=='' && method_exists($ipfm->Lazy, $action)) {
            $options['ipfm-ajax']=$action;

            $_POST['parentId']=$parentId;
            //$_POST['_ids']=$value;
            $values=call_user_func(array($ipfm->Lazy, $action), $params);
            //unset($_POST['_ids']);
            unset($_POST['parentId']);

            $options['values']=$values;
            $result=TRUE;
        }

        //lazy
        $action=$ipfm->Utils->get($column, 'ui-lazy', '');
        $function=array($ipfm->Lazy, $action);
        if($action!=='' && $ipfm->Utils->functionExists($function)) {
            $options['ipfm-lazy']=$action;

            $_POST['parentId']=$parentId;
            //$_POST['_ids']=$value;
            $values=$ipfm->Utils->functionCall($function, $params);
            //unset($_POST['_ids']);
            unset($_POST['parentId']);

            $options['values']=$values;
            $result=TRUE;
        }

        if(isset($options['values']) && isset($column['ui-all']) && $ipfm->Utils->isTrue($column['ui-all'])) {
            $first=array();
            $first[]=array(
                'id'=>-1
                , 'text'=>'['.$ipfm->Lang->L('All').']'
            );
            $options['values']=$ipfm->Utils->arrayPush($first, $options['values']);
        }

        unset($_POST['domain']);
        return $result;
    }
    

    public function inputsForm($fields, $instance, $param=array()) {
        global $ipfm;
        $fields=$ipfm->Utils->toArray($fields);
        foreach($fields as $v) {
            $this->inputForm($instance, $v, $param);
        }
    }
    public function inputForm($instance, $name, $params=array()) {
        global $ipfm;
        $options=array();
        if(isset($params['noLayout'])) {
            $options['noLayout']=$params['noLayout'];
            unset($params['noLayout']);
        }

        $name=$ipfm->Ui->getFieldOptions($instance, $name, $options);
        $column=$ipfm->Dao->Utils->getColumn($instance, $name);
        if(isset($column['alias'])) {
            $name=$column['alias'];
        }

        if(isset($column['ui-map']) && $column['ui-map']!='') {
            $function=array($ipfm->Lazy, $column['ui-map']);
            $types=$ipfm->Utils->functionCall($function);
        } else {
            $types=array();
            $types[]=array('id'=>'', 'text'=>'');
        }

        if(isset($column['ui-type'])) {
            foreach($types as $v) {
                if($v['id']!='') {
                    $_POST['_mapKey']=$v['id'];
                    $value=$ipfm->Utils->get($instance, $name.'.'.$v['id'], '');
                    $options['mapKey']=$v;
                } else {
                    $value=$ipfm->Utils->get($instance, $name, '');
                    unset($_POST['_mapKey']);
                    unset($options['mapKey']);
                }

                $exists=isset($column['ui-exists']);
                if($value || !$exists) {
                    if(isset($options['hidden']) && $options['hidden']) {
                        $value=$ipfm->Dao->Utils->encode($instance, $name, $value, FALSE);
                        $this->hidden($name, $value);
                    } else {
                        $multiple=$ipfm->Utils->get($column, 'ui-multiple', FALSE);
                        $multiple=$ipfm->Utils->isTrue($multiple);
                        $autocomplete=$ipfm->Utils->get($column, 'ui-autocomplete', '');

                        if($autocomplete!='') {
                            $options['autocomplete']=$autocomplete;
                        }

                        //$prefix=get_class($instance);
                        //$prefix=str_replace(IPFM_PLUGIN_PREFIX, '', $prefix).'_';
                        $type=strtolower($column['ui-type']);
                        switch ($type) {
                            case 'editor':
                                $options['editor'] = isset($column['ui-editor']) ? $column['ui-editor'] : 'pfm';
                                break;
                            case 'dropdown':
                            case 'tags':
                            case 'multiselect':
                                $values=$this->options($instance, $name);
                                $options['values']=$values;
                                $options['multiple']=$multiple;
                                //this function can override $options['values'] elements
                                $this->getMasterAjaxDomain($instance, $name, $value, $options, $params);
                                break;
                            case 'radiolist':
                            case 'checklist':
                                $values=$this->options($instance, $name);
                                $options['values']=$values;
                                break;
                        }
                        $this->inputComponent($type, $name, $value, $options);
                    }
                }
            }

            unset($_POST['_mapKey']);
            unset($options['mapKey']);
        }
    }
    public function inputComponent($type, $name, $value, $options=array()) {
        $values=array();
        $multiple=FALSE;
        $selected=1;
        $md='';
        if(isset($options['col-md'])) {
            $md=$options['col-md'];
            unset($options['col-md']);
        }
        if(isset($options['selected'])) {
            $selected=$options['selected'];
            unset($options['selected']);
        }
        if(isset($options['values'])) {
            $values=$options['values'];
            unset($options['values']);
        }
        if(isset($options['multiple']) && $options['multiple']) {
            $multiple=TRUE;
            unset($options['multiple']);
        }

        if($md!='') {
            echo "\n<div class=\"".$md."\">\n";
        }
        $type=strtolower($type);
        switch ($type) {
            case 'color':
            case 'colorpicker':
                $this->colorpicker($name, $value, $options);
                break;
            case 'editor':
                $this->editor($name, $value, $options);
                break;
            case 'text':
                $this->text($name, $value, $options);
                break;
            case 'timer':
                $this->timer($name, $value, $options);
                break;
            case 'upload':
                $this->upload($name, $value, $options);
                break;
            case 'textarea':
                $this->textarea($name, $value, $options);
                break;
            case 'hidden':
                $this->hidden($name, $value, $options);
                break;
            case 'currency':
                $this->currency($name, $value, $options);
                break;
            case 'number':
                $this->number($name, $value, $options);
                break;
            case 'password':
                $this->password($name, $value, $options);
                break;
            case 'email':
                $this->email($name, $value, $options);
                break;
            case 'dropdown':
                $this->dropdown($name, $value, $values, $multiple, $options);
                break;
            case 'multiselect':
                $this->multiselect($name, $value, $values, $options);
                break;
            case 'tags':
                $this->tags($name, $value, $values, $options);
                break;
            case 'date':
                $this->date($name, $value, $options);
                break;
            case 'time':
                $this->time($name, $value, $options);
                break;
            case 'datetime':
                $this->datetime($name, $value, $options);
                break;
            case 'toggle':
                $this->toggle($name, $value, 1, $options);
                break;
            case 'check':
                $this->checkbox($name, $value, $selected, $options);
                break;
            case 'checklist':
                $this->checklist($name, $value, $values, $options);
                break;
            case 'radiolist':
                $this->radiolist($name, $value, $values, $options);
                break;
        }
        if($md!='') {
            echo "\n</div>\n";
        }
    }
    public function options($class, $name) {
        global $ipfm;

        $values=array();
        $column=$ipfm->Dao->Utils->getColumn($class, $name);
        $dropdownPrefix=$ipfm->Utils->upperUnderscoreCase($name).'_';
        $dropdownPrefix=str_replace('_IDS_', '_', $dropdownPrefix);
        $dropdownPrefix=str_replace('_ID_', '_', $dropdownPrefix);
        if(isset($column['ui-prefix']) && $column['ui-prefix']!='') {
            $dropdownPrefix=$column['ui-prefix'];
        }
        if(strpos($dropdownPrefix, '::')==FALSE) {
            $v=$class;
            if(is_object($class)) {
                $v=get_class($class);
            }
            $dropdownPrefix=$v.'Constants::'.$dropdownPrefix;
        }

        $dropdownPrefix=explode('::', $dropdownPrefix);
        $dropdownPrefix[0]=str_replace('Search', '', $dropdownPrefix[0]);
        if(!class_exists($dropdownPrefix[0])) {
            $dropdownPrefix[0]=IPFM_PLUGIN_PREFIX.$dropdownPrefix[0];
        }
        if(!class_exists($dropdownPrefix[0])) {
            $result=array();
            return $result;
        }

        $reflection=new ReflectionClass($dropdownPrefix[0]);
        $constants=$reflection->getConstants();
        foreach($constants as $k=>$v) {
            if($ipfm->Utils->startsWith($k, $dropdownPrefix[1])) {
                $id=$v;
                $k='Dropdown.'.$dropdownPrefix[0].'.'.$k;
                $v=$ipfm->Lang->L($k);
                $values[$v]=$id;
            }
        }

        $inverseKeys=TRUE;
        $result=array();
        if(is_array($values) && count($values)>0) {
            ksort($values);
            $i=0;
            foreach($values as $k=>$v) {
                $colors=$ipfm->Utils->get($column, 'ui-style', '', $i);
                if(strpos($colors, ':')===FALSE) {
                    $colors.=':';
                }
                $colors=explode(':', $colors);
                $style='';
                if($colors[0]!='') {
                    $style.='color:'.$colors[0].'; ';
                }
                if($colors[1]!='') {
                    $style.='font-weight:'.$colors[1].'; ';
                }
                if($inverseKeys) {
                    $result[]=array('id'=>$v, 'text'=>$k, 'style'=>$style);
                } else {
                    $result[]=array('id'=>$k, 'text'=>$v, 'style'=>$style);
                }
                ++$i;
            }
        }

        if(isset($column['ui-all'])) {
            $result[]=array('id'=>-1, 'text'=>'[All]');
        }
        $result=$ipfm->Utils->sortOptions($result);
        return $result;
    }

    private function getOpenTag($instance, $name, $options) {
        global $ipfm;
        if($options===FALSE || $options==='') {
            return '';
        }
        if(is_string($options)) {
            $options=array('tag'=>$options);
        }
        $defaults=array(
            'tag'=>''
            , 'style'=>''
            , 'align'=>''
        );
        $options=$ipfm->Utils->parseArgs($options, $defaults);
        if($options['tag']=='') {
            return '';
        }

        if(is_object($instance)) {
            $instance=get_class($instance);
        }
        foreach ($instance as &$value) {
            $value = print_r($value, true);
        }
        $instance=str_replace(IPFM_PLUGIN_PREFIX, '', $instance);
        $instance=str_replace('Search', '', $instance);
        $column=$ipfm->Dao->Utils->getColumn($instance, $name);
        if($options['align']!='') {
            $column['ui-align']=$options['align'];
        } elseif(!isset($column['ui-align']) || $column['ui-align']=='') {
            $column['ui-align']='center';
        }
        $class=' class="text-'.$column['ui-align'].'" ';
        $result='<'.$options['tag'].$class.' style="'.$options['style'].'">';
        return $result;
    }
    private function getCloseTag($tag) {
        if($tag===FALSE || $tag=='') {
            return '';
        }
        $result="</".$tag.">";
        return $result;
    }
    public function inputHeader($instance, $name, $options) {
        global $ipfm;
        $defaults=array(
            'tag'=>FALSE
            , 'echo'=>TRUE
            , 'style'=>''
            , 'align'=>''
            , 'header'=>''
            , 'rawColumnName'=>FALSE
        );
        $options=$ipfm->Utils->parseArgs($options, $defaults);
        $args=array();
        $name=$ipfm->Ui->getFieldOptions($instance, $name, $args);
        $buffer=$this->getOpenTag($instance, $name, $options);
        $column=$ipfm->Dao->Utils->getColumn($instance, $name);
        if($options['header']!='') {
            $header=$ipfm->Lang->L($options['header']);
        } else {
            $header='';
            if($this->prefix!='') {
                $header=$this->prefix.'.';
            }
            $header.=$name.'.Header';
            if($ipfm->Lang->H($header) || !$options['rawColumnName']) {
                $header=$ipfm->Lang->L($header);
            } else {
                $header=$name;
            }
        }
        $buffer.=$header;
        if(isset($column['ui-type'])) {
            $suffix='';
            switch (strtolower($column['ui-type'])) {
                case 'percentage':
                    $symbol=true;
                    if(isset($column['ui-symbol'])) {
                        $symbol=$ipfm->Utils->isTrue($column['ui-symbol']);
                    }
                    if($symbol) {
                        $suffix=' %';
                    }
                    break;
                case 'currency':
                    $symbol=$ipfm->Utils->getDefaultCurrencySymbol();
                    $symbol=$ipfm->Utils->getCurrencySymbol($symbol);
                    $suffix=' '.$symbol;
                    break;
            }
            $buffer.=$suffix;
        }
        $buffer.=$this->getCloseTag($options['tag']);
        if($options['echo']) {
            echo $buffer;
        }
    }
    private function getUiStyle($column, $i) {
        global $ipfm;
        if(is_bool($i)) {
            $i=($i ? 1 : 0);
        }
        $colors=$ipfm->Utils->get($column, 'ui-style', '', $i);
        if(strpos($colors, ':')===FALSE) {
            $colors.=':';
        }
        $colors=explode(':', $colors);
        $style='';
        if($colors[0]!='') {
            $style.='color:'.$colors[0].'; ';
        }
        if($colors[1]!='') {
            $style.='font-weight:'.$colors[1].'; ';
        }
        return $style;
    }
    public function inputGet($instance, $name, $tag=FALSE, $echo=TRUE) {
        global $ipfm;
        $options=array();
        if(is_array($echo)) {
            $options=$echo;
            $echo=TRUE;
        }
        $name=$ipfm->Ui->getFieldOptions($instance, $name, $options);
        $value='#'.$name.'#??';
        if(is_array($instance)) {
            if(isset($instance[$name])) {
                $value=$instance[$name];
            } else {
                $value='';
            }
            if(isset($options['format_'.$name])) {
                switch (strtolower($options['format_'.$name])) {
                    case 'datetime':
                        $value=$ipfm->Utils->formatDatetime($value);
                        break;
                    case 'time':
                        $value=$ipfm->Utils->formatTime($value);
                        break;
                    case 'date':
                        $value=$ipfm->Utils->formatDate($value);
                        break;
                    case 'gravatar':
                        $value=$ipfm->Utils->getGravatarImage($value);
                        break;
                    case 'currency':
                        $value=$ipfm->Utils->formatCurrencyMoney($value);
                        break;
                    case 'percentage':
                        $value=$ipfm->Utils->formatPercentage($value);
                        break;
                }
            }
        } else {
        $column=$ipfm->Dao->Utils->getColumn($instance, $name);
        if(isset($column['alias'])) {
            $name=$column['alias'];
        }

        if($column===FALSE) {
            $value='#'.$name.'#??';
        } elseif(!isset($column['ui-type'])) {
            $value='ui-type #'.$name.'#??';
        } elseif(isset($options['check']) && $options['check']) {
            $ids=$ipfm->Utils->qs('ids', array());
            $value=$ipfm->Utils->get($instance, $name, '');
            $options['selected']=$value;
            $options['noLayout']=TRUE;
            if(!$ipfm->Utils->inArray($value, $ids)) {
                $value=FALSE;
            }

            ob_start();
                unset($options['readonly']);
                unset($options['disabled']);
            $this->inputComponent('check', 'ids[]', $value, $options);
            $value=ob_get_clean();
        } else {
            $value=$ipfm->Utils->get($instance, $name, '');
            $source=$value;

            $type=strtolower($column['ui-type']);
            switch ($type) {
                case 'currency':
                        $value=round(floatval($value), 4);
                    if($value!=0) {
                            $value=$ipfm->Utils->formatCurrencyMoney($value);
                    } else {
                        $value='';
                    }
                    break;
                case 'number':
                    $value=intval($value);
                    break;
                case 'percentage':
                    $symbol=true;
                    if(isset($column['ui-symbol'])) {
                        $symbol=$ipfm->Utils->isTrue($column['ui-symbol']);
                    }
                    $value=$ipfm->Utils->formatPercentage($value, $symbol);
                    break;
                case 'checklist':
                case 'radiolist':
                    $values=$this->options($instance, $name);
                    $value=$this->optionsText($values, $value);
                    break;
                case 'dropdown':
                case 'tags':
                    case 'select':
                    $values=$this->options($instance, $name);
                    $_GET['_inputGet']=$value;
                    if($this->getMasterAjaxDomain($instance, $name, $value, $options)) {
                        if(isset($options['values'])) {
                            $values=$options['values'];
                            unset($options['values']);
                        }
                    }
                        unset($_GET['_inputGet']);
                    $value=$this->optionsText($values, $value);
                    break;
                case 'date':
                    $value=$ipfm->Utils->formatDate($value);
                    break;
                case 'time':
                    $value=$ipfm->Utils->formatTime($value);
                    break;
                case 'datetime':
                    $value=$ipfm->Utils->formatDatetime($value);
                    break;
                case 'toggle':
                    $value=$ipfm->Utils->isTrue($value);
                    $style=$this->getUiStyle($column, $value);
                    $value=$ipfm->Lang->L($value ? 'Toggle.Yes' : 'Toggle.No');
                    if($style!='') {
                        $value='<span style="'.$style.'">'.$value.'</span>';
                    }
                    break;
                }
            }
        }

        $buffer=$this->getOpenTag($instance, $name, $tag);
        if(isset($options['ui-link']) && $options['ui-link']) {
            $target='_self';
            if(isset($options['ui-target']) && $options['ui-target']) {
                $target=$options['ui-target'];
            }
            if($value!='') {
                $buffer.='<a href="'.$options['ui-link'].$instance->id.'" target="'.$target.'">';
                $buffer.=$value;
                $buffer.='</a>';
            }
        } else {
            $buffer.=$value;
        }
        $buffer.=$this->getCloseTag($tag);
        if($echo) {
            echo $buffer;
        } else {
            return $buffer;
        }
    }
    public function inputSearch($instance, $name) {
        $prev=$this->search;
        $this->search=TRUE;
        $this->inputForm($instance, $name);
        $this->search=$prev;
    }
    public function optionsText($options, $value) {
        global $ipfm;
        $value=$ipfm->Utils->toArray($value);
        if($options===FALSE || count($options)==0 || count($value)==0) {
            return '';
        }

        $buffer='';
        foreach($options as $v) {
            if(isset($v['id']) && in_array($v['id'], $value)) {
                if($buffer!='') {
                    $buffer.=', ';
                }
                if(!isset($v['text']) && isset($v['name'])) {
                    $v['text']=$v['name'];
                }

                if(isset($v['style']) && $v['style']!='') {
                    $buffer.='<span style="'.$v['style'].'">'.$v['text']."</span>";
                } else {
                    $buffer.=$v['text'];
                }
            }
        }
        return $buffer;
    }

    public function submit($name='', $options=array()) {
        global $ipfm;
        $defaults=array(
            'name'=>'btnSubmit'
            , 'prompt'=>FALSE
            , 'submit'=>TRUE
        );
        $options=$ipfm->Utils->parseArgs($options, $defaults);
        if($name=='') {
            $name='Save';
        }
        $this->button($name, $options);
    }
    public function buttonset($name, $buttons, $options=array()) {
        global $ipfm;
        if(count($buttons)==0) {
            return;
        }

        $defaults=array(
            'theme'=>''
            , 'icon'=>''
            , 'class'=>''
            , 'rowClass'=>''
            , 'buttonClass'=>''
            , 'clearBoth'=>FALSE
            , 'br'=>FALSE
            , 'noLayout'=>FALSE
        );
        $options=$ipfm->Utils->parseArgs($options, $defaults);

        $inputArgs=array(
            'md9'=>FALSE
            , 'label'=>$this->prefix.'.'.$name
                //, 'style'=>'font-size:11px;'
            , 'col-md'=>'col-md-3'
            , 'rowClass'=>$options['rowClass']
        );
        $class='bs-component';
        if(!$options['noLayout']) {
            $class='col-md-9 bs-component';
            $this->openInput($name, $inputArgs);
        }

        $args=array('class'=>$class);
        $this->divStarts($args);
        {
            $args=array('class'=>'btn-group');
            $this->divStarts($args);
            {
                foreach($buttons as $v) {
                    if(is_string($v)) {
                        $v=array('value'=>$v);
                    } elseif(!is_array($v)) {
                        throw new Exception('buttonset: VALUE MUST BE STRING OR ARRAY');
                    }
                    $name=$v['value'];
                    $defaults=array(
                        'theme'=>$options['theme']
                        , 'icon'=>$options['icon']
                        , 'class'=>$options['buttonClass']
                        , 'data-filter'=>''
                        , 'data-id'=>''
                            //, 'class'=>'light mr5'
                        , 'script'=>FALSE
                    );
                    $v=$ipfm->Utils->parseArgs($v, $defaults);
                    $this->button($name, $v);
                }
            }
            $this->divEnds();
            if($options['br']) {
                $this->br();
            }
            if($options['clearBoth']) {
                $this->clearBoth();
            }
        }
        $this->divEnds();

        if(!$options['noLayout']) {
            $this->closeInput($name, $inputArgs);
        }
    }
    public function button($value, $options=NULL) {
        global $ipfm;
        if(!$this->hiddenActionCreated) {
            $this->hidden('_action', '');
        }

        $this->buttonPresent=TRUE;
        $defaults=array(
            'theme'=>'primary'
            , 'icon'=>$this->getIcon($value, 'cog')
            , 'id'=>'btn'.$value
            , 'name'=>'btn'.$value
            , 'uri'=>FALSE
            , 'type'=>'button'
            , 'prompt'=>FALSE
            , 'rightSpace'=>TRUE
            , 'leftSpace'=>FALSE
            , 'submit'=>FALSE
            , 'class'=>''
            , 'style'=>''
            , 'data-id'=>''
            , 'label'=>''
        );
        $options=$ipfm->Utils->parseArgs($options, $defaults);
        $uri=$options['uri'];
        $onclick=($uri===FALSE || $uri==='' ? '' : 'onclick="window.location=\''.$uri.'\';"');

        $icon=$options['icon'];
        $leftIcon=TRUE;
        $nextWords=$ipfm->Utils->toArray('next|finish|save');
        foreach($nextWords as $w) {
            if(stripos($value, $w)!==FALSE) {
                $leftIcon=FALSE;
                break;
            }
        }
        //btn-block means 100% width
        if($options['leftSpace']) {
            echo '&nbsp;';
        }
        if($options['label']=='') {
            $options['label']=$ipfm->Lang->L($this->prefix.'.Button.'.$value);
        }
        ?>
        <button type="<?php echo $options['type']?>" id="<?php $this->getName($options['id'])?>" name="<?php $this->getName($options['name'])?>" class="btn <?php echo $options['class']?> btn-<?php echo $options['theme']?>" <?php echo $onclick?> value="<?php echo $value?>" data-id="<?php echo $options['data-id']?>" style="<?php echo $options['style']?>">
            <?php if($leftIcon) { ?>
                <i class="fa fa-<?php echo $icon?>"></i>&nbsp;
            <?php } ?>
            <?php echo $options['label']?>
            <?php if(!$leftIcon) { ?>
                &nbsp;<i class="fa fa-<?php echo $icon?>"></i>
            <?php } ?>
        </button>
    <?php
        if($options['rightSpace']) {
            echo '&nbsp;';
        }

        if($options['prompt']!==FALSE) {
            $args=$options['prompt'];
            if(!is_array($args)) {
                $args=array();
            }
            if(!isset($args['submit'])) {
                $args['submit']=$options['submit'];
            }
            if(!isset($args['btnConfirmTheme']) && isset($options['theme'])) {
                $args['btnConfirmTheme']=$options['theme'];
            }
            $this->prompt($options['id'], $args);
        }
        if($options['prompt']==FALSE) { ?>
            <script>
                jQuery(function() {
                    <?php $this->jQueryBtnConfirm($options['id'], $options['id'], $options) ?>
                });
            </script>
        <?php }
    }

    public function openBlock() {
        $this->blockOpened=TRUE;
        ?>
        <div class="mw1000 left-block">
    <?php }
    public function closeBlock() {
        if(!$this->blockOpened) {
            return;
        } ?>
        </div>
    <?php }
    public function panel($title, $callback=FALSE, $options=array()) {
        if(is_callable($title) && $callback==FALSE) {
            $callback=$title;
            $title='';
        }
        $style=(isset($options['style']) ? $options['style'] : 'primary');
        $this->openPanel($title, $style);
        $callback();
        $this->closePanel();
    }
    public function inputsPanel($title, $fields, $instance) {
        $this->openPanel($title);
        {
            $this->inputsForm($fields, $instance);
        }
        $this->closePanel();
    }
    public function openPanel($options=array()) {
        global $ipfm;
        if(is_string($options)) {
            $options=array('title'=>$options);
        }
        $defaults=array(
            'title'=>''
            , 'titleText'=>''
            , 'subtitle'=>TRUE
            , 'style'=>''
            , 'panelTop'=>FALSE
            , 'panelColor'=>''
            , 'icon'=>''
            , 'class'=>''
            , 'body'=>TRUE
	        , 'buttons'=>FALSE
            , 'id'=>''
        );
        $options=$ipfm->Utils->parseArgs($options, $defaults);
        if($options['title']=='') {
            $options['title']='Name';
        }
        $options['title']=ucfirst($options['title']);

        $title='Panel.'.$this->prefix.'.'.$options['title'];
        $title=$ipfm->Lang->L($title);
        if($options['titleText']!='') {
            $title=$options['titleText'];
        }

        $style=$options['style'];
        $panel='';
        if($style!='') {
            //list($style, $color)=$ec->Utils->pickColor();
            $panel='panel-'.$style;
        }
        if($options['panelTop']) {
            $panel.=' panel-border top';
        }

        $subtitle='';
        if(is_bool($options['subtitle']) && $options['subtitle']) {
            $subtitle=$ipfm->Lang->L('Panel.'.$this->prefix.'.'.$options['title'].'Subtitle');
        } elseif(is_string($options['subtitle']) && $options['subtitle']!='') {
            $subtitle=$ipfm->Lang->L($options['subtitle']);
        }
        ?>
        <div class="panel <?php echo $panel?> mt20 mb20 <?php echo $options['class']?>" id="<?php echo $options['id']?>">
            <?php if(is_array($options['buttons']) && count($options['buttons'])>0) { ?>
                <div class="panel-header-buttons text-left">
                    <?php $ipfm->Form->buttons($options['buttons']); ?>
                </div>
            <?php } else { ?>
                <div class="panel-heading">
                    <?php if ($options['icon'] != '') { ?>
                        <span class="panel-icon">
                            <i class="fa fa-<?php echo $options['icon'] ?>"></i>
                        </span>
                    <?php } ?>
                    <span class="panel-title">
                        <?php echo $title ?>
                    </span>
                </div>
            <?php } ?>

            <?php if ($options['body']) { ?>
                <div class="panel-body bg-light dark">
                    <?php if ($subtitle != '') { ?>
                        <div class="panel-subtitle">
                            <?php echo $subtitle ?>
                        </div>
                    <?php } ?>
                    <div class="admin-form">
            <?php }
    }
    public function closePanel($options=array()) {
        global $ipfm;
        $defaults=array(
            'body'=>TRUE
            , 'buttons'=>FALSE
            , 'id'=>''
        );
        $options=$ipfm->Utils->parseArgs($options, $defaults);

        if ($options['body']) { ?>
                </div>
            </div>
            <?php
            if (is_array($options['buttons']) && count($options['buttons']) > 0) { ?>
                <div class="panel-footer-buttons text-right" id="<?php echo $options['id']?>">
                    <?php $ipfm->Form->buttons($options['buttons']); ?>
                </div>
            <?php } ?>
        </div>
    <?php }
    }

    //popup
    public function prompt($buttonId, $options=array()) {
        global $ipfm;
        $p=$this->prefix.'.Prompt.'.$buttonId.'.';
        $defaults=array(
            'btnAbort'=>$buttonId.'Abort'
            , 'btnAbortTheme'=>''
            , 'btnAbortText'=>$p.'ButtonAbort'
            , 'btnConfirm'=>$buttonId.'Confirm'
            , 'btnConfirmTheme'=>'primary'
            , 'btnConfirmText'=>$p.'ButtonConfirm'
            , 'uri'=>''
            , 'submit'=>TRUE
            , 'effect'=>'newspaper'
        );
        $options=$ipfm->Utils->parseArgs($options, $defaults);
        $options['btnAbortText']=$ipfm->Lang->L($options['btnAbortText']);
        $options['btnConfirmText']=$ipfm->Lang->L($options['btnConfirmText']);
        if(!isset($options['btnAbortIcon'])) {
            $options['btnAbortIcon']=$this->getIcon($options['btnAbortText']);
        }
        if(!isset($options['btnConfirmIcon'])) {
            $options['btnConfirmIcon']=$this->getIcon($options['btnConfirmText']);
        }
        $modalId='modal-prompt-'.$buttonId;
        ?>
        <!-- Panel popup -->
        <div id="<?php echo $modalId?>" class="popup-basic bg-none mfp-with-anim mfp-hide">
            <div class="panel panel-<?php echo $options['btnConfirmTheme']?>">
                <div class="panel-heading">
                    <span class="panel-icon">
                        <i class="fa fa-question-circle"></i>
                    </span>
                    <span class="panel-title"><?php $ipfm->Lang->P($p.'Title')?></span>
                </div>
                <div class="panel-body">
                    <p><?php $ipfm->Lang->P($p.'Text')?></p>
                </div>
                <div class="panel-footer text-right">
                    <button id="<?php echo $options['btnAbort']?>" class="btn btn-<?php echo $options['btnAbortTheme']?>" type="button">
                        <?php if($options['btnAbortIcon']!==FALSE) { ?>
                            <i class="fa fa-<?php echo $options['btnAbortIcon'] ?>"></i>
                            &nbsp;
                        <?php } ?>
                        <?php echo $options['btnAbortText'] ?>
                    </button>
                    <button id="<?php echo $options['btnConfirm']?>" class="btn btn-<?php echo $options['btnConfirmTheme']?>" type="button">
                        <?php if($options['btnConfirmIcon']!==FALSE) { ?>
                            <i class="fa fa-<?php echo $options['btnConfirmIcon'] ?>"></i>
                            &nbsp;
                        <?php } ?>
                        <?php echo $options['btnConfirmText'] ?>
                    </button>
                </div>
            </div>
        </div>
        <script>
            jQuery(function() {
                jQuery('#<?php echo $buttonId?>').on('click', function() {
                    jQuery.magnificPopup.open({
                        removalDelay: 0//500
                        , items: {
                            src: '#<?php echo $modalId?>'
                        }
                        //, overflowY: 'hidden'
                        /*, callbacks: {
                            beforeOpen: function(e) {
                                this.st.mainClass='mfp-<?php echo $options['effect']?>';
                            }
                        }*/
                        , midClick: true
                    });
                });
                <?php $this->jQueryBtnConfirm($buttonId, $options['btnConfirm'], $options) ?>
                jQuery('#<?php echo $options['btnAbort']?>').on('click', function(e) {
                    e.preventDefault();
                    jQuery.magnificPopup.close();
                });
            });
        </script>
    <?php }
    private function jQueryBtnConfirm($btnButtonId, $btnConfirmId, $options) { ?>
        jQuery('#<?php echo $btnConfirmId ?>').on('click', function(e) {
            e.preventDefault();
        <?php if($options['uri']!==FALSE && $options['uri']!='') { ?>
            location.href="<?php echo $options['uri']?>";
        <?php } elseif($options['submit']) { ?>
            var $btn=jQuery('#<?php echo $btnButtonId?>');
            var $form=$btn.closest('form');
            var $action=jQuery('[name=_action]');
            if($action.length>0) {
                $action.val($btn.val());
            }

            if($form.length>0) {
                jQuery('input, select').prop('disabled', false);
                $form[0].submit();
            }
        <?php } ?>
        });
    <?php }
    private function getColumnDetails($options, $name) {
        global $ipfm;
        $defaults=array(
            'style'=>''
            , 'header'=>''
            , 'function'=>FALSE
            , 'align'=>''
            , 'class'=>''
        );
        $result=(isset($options[$name]) ? $options[$name] : array());
        $result=$ipfm->Utils->parseArgs($result, $defaults);
        return $result;
    }
    public function inputTable($fields, $items, $options=array()) {
        global $ipfm;
        $defaults=array(
            'class'=>''
            , 'style'=>''
            , 'data-filter'=>''
            , 'rowOptions'=>array()
            , 'bgClass'=>FALSE
            , 'rawColumnName'=>FALSE
            , 'id'=>''
        );
        $options=$ipfm->Utils->parseArgs($options, $defaults);
        $fields=$ipfm->Utils->toArray($fields);
        ?>
        <div class="table-responsive">
            <table class="table bg-white tc-checkbox-1 <?php echo $options['class']?>" style="<?php echo $options['style']?>" data-filter="<?php echo $options['data-filter']?>" id="<?php echo $options['id']?>">
            <thead class="table-header">
                    <tr class="bg-light">
                    <?php
                        foreach($fields as $name) {
                            $details=$this->getColumnDetails($options, $name);
                            $args=array(
                                'tag'=>'th'
                                , 'rawColumnName'=>$options['rawColumnName']
                                , 'style'=>$details['style']
                                , 'header'=>$details['header']
                                , 'align'=>$details['align']
                            );
                            $this->inputHeader($items, $name, $args);
                    } ?>
                </tr>
            </thead>
            <tbody class="table-body">
                <?php
                $i=0;
                foreach($items as $instance) {
                    $i++;
                    if($options['bgClass']!==FALSE) {
                        $bgClass=$options['bgClass']($instance);
                    } else {
                        $bgClass=($i%2==0 ? 'even' : 'odd');
                    }
                    ?>
                    <tr class="<?php echo $bgClass?>" id="row_<?php echo $ipfm->Utils->iget($instance, 'id', 0)?>">
                        <?php foreach($fields as $name) {
                            $details=$this->getColumnDetails($options, $name);
                            $rowOptions=$options['rowOptions'];
                            if(!is_array($rowOptions)) {
                                $rowOptions=array();
                            }
                            $args=array();
                            $columnName=$ipfm->Ui->getFieldOptions($instance, $name, $args);
                            $column=$ipfm->Dao->Utils->getColumn($instance, $columnName);
                            $align=$ipfm->Utils->get($column, 'ui-align', '');
                            if($align=='') {
                                $alignKey='align_'.$name;
                                $align=(isset($rowOptions[$alignKey]) ? $rowOptions[$alignKey] : '');
                            }
                            if($align=='') {
                                $align=$details['align'];
                                if($align=='') {
                                    $align='center';
                                }
                            }

                            $columnKey='column_'.$columnName;
                            $alignKey='class_'.$name;
                            $class=(isset($rowOptions[$alignKey]) ? $rowOptions[$alignKey] : '');
                            if($class=='') {
                                $class=$details['class'];
                            }

                            echo '<td class="'.$class.' text-'.$align.'" style="'.$details['style'].'">';
                            if(isset($options[$columnKey])) {
                                $ipfm->Utils->functionCall($options[$columnKey], $instance);
                            } elseif($details['function']!==FALSE) {
                                $ipfm->Utils->functionCall($details['function'], $instance);
                            } else {
                                $this->inputGet($instance, $name, '', $rowOptions);
                            }
                            echo '</td>';
                        } ?>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        </div>
    <?php }
    public function submits($buttons) {
        if($buttons===FALSE || count($buttons)==0) {
            return;
        }
        foreach ($buttons as $k=>$v) {
            $v['submit']=TRUE;
            $this->button($k, $v);
        }
    }
    public function buttons($buttons) {
        if($buttons===FALSE || count($buttons)==0) {
            return;
        }
        foreach ($buttons as $k=>$v) {
            $this->button($k, $v);
        }
    }
}