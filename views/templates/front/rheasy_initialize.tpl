{*
* This template file contains script which initialize RhEasy global object
*
* We use RhEasy object as storage contains all neccessary information nedded to fire remarketing events
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
        {if isset($rhEasyDto)}
            var rhEasyGlobal = {$rhEasyDto->toJson()};
        {else}
            return;
        {/if}

        var emptyAddObject = {
            add: function (f) {
                return true;
            }
        };

        rhEasyGlobal.onCartChanged = emptyAddObject;
        rhEasyGlobal.onCategoryViewed = emptyAddObject;
        rhEasyGlobal.onOrderPlaced = emptyAddObject;
        rhEasyGlobal.onPageLoaded = emptyAddObject;
        rhEasyGlobal.onProductViewed = emptyAddObject;

        var createAddObject = function (rhEasyDtoObject) {
            return {
                add: function (f) {
                    f(rhEasyDtoObject);
                    return true;
                }
            };
        };

        {if isset($rhEasyPageDto)}
            rhEasyGlobal.onPageLoaded = createAddObject({$rhEasyPageDto->toJson()});
        {/if}

        {if isset($rhEasyProductDto)}
            rhEasyGlobal.onProductViewed = createAddObject({$rhEasyProductDto->toJson()});
        {/if}

        {if isset($rhEasyCategoryDto) }
            rhEasyGlobal.onCategoryViewed = createAddObject({$rhEasyCategoryDto->toJson()});
        {/if}

        {if isset($rhEasyOrderDto) }
            rhEasyGlobal.onOrderPlaced = createAddObject({$rhEasyOrderDto->toJson()});
        {/if}

        {if isset($rhEasyCartDto) }
            rhEasyGlobal.onCartChanged = createAddObject({$rhEasyCartDto->toJson()});
        {/if}

        if (!window.RhEasy) {
            window.RhEasy = rhEasyGlobal;
        }
    })();
</script>
