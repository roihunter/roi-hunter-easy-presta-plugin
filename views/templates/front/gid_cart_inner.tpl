 
  // Cart page + add to cart hook
  gtag('event', 'add_to_cart', {
    send_to: 'AW-{$google_conversion_id}',
    dynx_itemid: [{$gid_cart_products}],
    dynx_pagetype: 'conversionintent',
    dynx_totalvalue: {$gid_price},
  });
 
 
 