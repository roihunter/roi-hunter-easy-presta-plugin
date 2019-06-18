{*
 * ROI Hunter module in iFrame
 *
 * LICENSE: The buyer can free use/edit/modify this software in anyway
 * The buyer is NOT allowed to redistribute this module in anyway or resell it
 * or redistribute it to third party
 *
 * @author    ROI Hunter Easy
 * @copyright 2019 ROI Hunter
 * @license   EULA
 * @version   1.0
 * @link      https://easy.roihunter.com/
*}

{extends file="helpers/view/view.tpl"}

{block name="override_tpl"}
    <script type="text/javascript">
    var admin_order_tab_link = "{addslashes($link->getAdminLink('AdminRoihunter'))}";
 
     function iFrameLoad() {
       
        // pass base url to React iframe fro future API calls to this site
        var iFrame = document.getElementById('RoiHunterEasyIFrame');
        iFrame.contentWindow.postMessage({
            {foreach from=$params key=klic item=hodnota}
            "{$klic}": "{$hodnota}",  
            {/foreach}
            }, '*'
        );
 
        // Create IE + others compatible event handler
        var eventMethod = window.addEventListener ? "addEventListener" : "attachEvent";
        var eventer = window[eventMethod];
        var messageEvent = eventMethod == "attachEvent" ? "onmessage" : "message";
 
        // Listen to message from child window
        eventer(messageEvent, function (e) {
            if (e.data.type === "roihunter_plugin_height") {
//            Change size of iFrame to correspond new height of content
//            console.log("new height: " + e.data.height);
                document.getElementById('RoiHunterEasyIFrame').style.height = e.data.height + 'px';
            } else if (e.data.type === "roihunter_location") {
                window.top.location = e.data.location;
            } else {
//            console.log("Unknown message event", e);
            }
        }, false);
    }
    </script>
  <iframe src="{$iframeBaseUrl}"
       id="RoiHunterEasyIFrame"
       scrolling="yes"
       frameBorder="0"
       allowfullscreen="true"
       align="center"
       onload="iFrameLoad()"
       style="width: 100%; min-height: 500px">
    <p>Your browser does not support iFrames.</p>
</iframe>

{/block}
