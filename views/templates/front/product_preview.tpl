{*
* We assume that RhEasy object is already initialized on the page
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

<script>
    (function () {
        if (!window.RhEasy) {
            return;
        }

        {if isset($rhEasyProductDto)}
            window.RhEasy.onProductViewed.add = function (f) {
                f({$rhEasyProductDto->toJson()});
                return true;
            };
        {/if}
    })();
</script>