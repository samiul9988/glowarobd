# API Endpoint Renaming List

The following table lists the current client-side visible API endpoints and their proposed new names for better clarity and RESTful compliance.

| Current Path | Proposed Path | Reason |
| :--- | :--- | :--- |
| `/business-settings` | `/site-settings` | More descriptive of the data returned |
| `/resend-code` | `/otp/resend` | Groups OTP related actions |
| `/verify-phone` | `/otp/verify` | Groups OTP related actions |
| `/shipping_cost` | `/shipping/calculate` | Clearer action name |
| `/user/shipping/address` | `/user/addresses` | Concise and pluralized |
| `/order/store` | `/orders/create` | RESTful naming |
| `/cartswithdelivery` | `/cart/delivery-info` | More readable |
| `/categories` | `/catalog/categories` | Contextual grouping |
| `/sub-categories` | `/catalog/sub-categories` | Contextual grouping |
| `/cart-summary` | `/cart/summary` | Grouping cart actions |
| `/cart/store_delivery_info` | `/cart/delivery-info/save` | Action specificity |
| `/validate-data` | `/checkout/validate` | Contextual grouping |
| `/sslcommerz/begin` | `/payment/sslcommerz/init` | Standard naming |
| `/bkash/begin` | `/payment/bkash/init` | Standard naming |
| `/update-payment-status` | `/payment/status/update` | RESTful naming |
| `/get-assigned-coupons` | `/coupons/assigned` | RESTful naming |
| `/carts` | `/cart/items` | RESTful naming |
