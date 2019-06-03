{if $activeProfile == 'production'}
    <script src="https://storage.googleapis.com/goostav-static-files-master/presta-tracking.js" async></script>
{elseif $activeProfile == 'staging'}
    <script src="https://storage.googleapis.com/goostav-static-files-staging/presta-tracking.js" async></script>
{else}
    <script>console.error("Cannot load events tracking script: reason: unknown profile");</script>
{/if}