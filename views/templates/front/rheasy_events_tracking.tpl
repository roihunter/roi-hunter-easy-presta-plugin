{*
* PHP version 5
*
* LICENSE: The buyer can free use/edit/modify this software in anyway
* The buyer is NOT allowed to redistribute this module in anyway or resell it
* or redistribute it to third party
*
* @author    ROI Hunter Easy
* @copyright 2014-2019 ROI Hunter
* @license   EULA
* @version   1.0
* @link      https://easy.roihunter.com/
*}

{if $activeProfile == 'production'}
    <script src="https://storage.googleapis.com/goostav-static-files-master/presta-tracking.js" async></script>
{elseif $activeProfile == 'staging'}
    <script src="https://storage.googleapis.com/goostav-static-files-staging/presta-tracking.js" async></script>
{else}
    <script>console.error("Cannot load events tracking script: reason: unknown profile");</script>
{/if}