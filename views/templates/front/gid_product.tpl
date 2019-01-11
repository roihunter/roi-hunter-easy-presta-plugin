<!-- Global Site Tag (gtag.js)
Conversion ID: {$google_conversion_id}
-->
<script async src="https://www.googletagmanager.com/gtag/js?id=AW-{$google_conversion_id}"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  {literal}function gtag(){dataLayer.push(arguments);}{/literal}
  gtag('js', new Date());

  gtag('config', 'AW-{$google_conversion_id}');
  {$inner_cart}
  // Product detail
  gtag('event', 'view_item', {
    send_to: 'AW-{$google_conversion_id}',
    dynx_itemid: {$gid_product},
    dynx_pagetype: 'offerdetail',
    dynx_totalvalue:  {$gid_price},
  });
</script>
 
 