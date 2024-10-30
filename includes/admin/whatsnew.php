<?php
function ipfm_ui_whats_new() {
    global $ipfm, $ecf;
    $ipfm->Options->setShowWhatsNew(FALSE);
    ?>
    <style>
        .ipfm-headline {
            font-size:40px;
            font-weight:bold;
            text-align:center;
        }
        .ipfm-sub-headline {
            font-size:35px;
            font-weight:normal;
            text-align:center;
        }
    </style>

    <p class="ipfm-headline">Getting started with CA Enhancer</p>
    <p class="ipfm-sub-headline">Watch this video before begin!</p>
    <div style="text-align: center">
        <iframe width="854" height="480" src="//www.youtube.com/embed/c3yfd5oiVGk?autoplay=1"></iframe>
        <br>
        <br>
        <?php
        $ecf->prefix='License';
        $args=array(
            'uri'=>IPFM_TAB_SETTINGS_URI
            , 'theme'=>'primary'
            , 'class'=>'btn-lg'
        );
        $ecf->button('fbConnect', $args);
        ?>
    </div>
<?php }