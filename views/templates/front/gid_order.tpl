<!-- Global Site Tag (gtag.js)
Conversion ID: {$google_conversion_id}
-->
<script async src="https://www.googletagmanager.com/gtag/js?id=AW-{$google_conversion_id}"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  {literal}function gtag(){dataLayer.push(arguments);}{/literal}
  gtag('js', new Date());

  gtag('config', 'AW-{$google_conversion_id}');
  
  
  
    // Checkout ("Thank you" page)
  gtag('event', 'purchase', {
    send_to: 'AW-{$google_conversion_id}/{$google_conversion_label}',
    value: {$gid_totalvalue},
    currency: '{$currency}',
    transaction_id: {$id_order}, // Order ID
    dynx_itemid: [{$gid_products}],
    dynx_pagetype: 'conversion',
    dynx_totalvalue: {$gid_totalvalue},
  });
</script>
 
 