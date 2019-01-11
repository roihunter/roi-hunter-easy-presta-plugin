{*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{extends file="helpers/view/view.tpl"}

{block name="override_tpl"}
    <script type="text/javascript">
    var admin_order_tab_link = "{$link->getAdminLink('AdminRoihunter')|addslashes}";
 
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
