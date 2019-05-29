{* We assume that RhEasy object is already initialized on the page *}
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