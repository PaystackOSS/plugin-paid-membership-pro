=== Paystack Gateway for Paid Membership Pro ===
Contributors: paystack, kendysond, steveamaza, lukman008, andrewza, strangerstudios, paidmembershipspro
Donate link: https://paystack.com/demo
Tags: paid memberships pro, paystack, gateway, credit card, Naira
Requires at least: 5.2
Tested up to: 7.1
Stable tag: 1.9.2
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Pay with Paystack on Paid Membership Pro

== Description ==
 
Paid Membership Pro is a complete member management and membership subscriptions plugin for WordPress. Paid Memberships Pro is designed for premium content sites, clubs/associations, subscription products, newsletters and more!
 
The **Paystack Gateway for Paid Membership Pro** allows site owners from Nigeria, Ghana, Kenya and South Africa to accept payments from their customers via Paid Membership Pro.

To be able to use the Paystack Gateway for Paid Membership Pro, you must [have an account on Paystack](https://dashboard.paystack.com) from which you will get Test and Live API keys to connect the plugin to your Paystack business. Here are some benefits of using Paystack!

= Intuitive Dashboard =
Use the Paystack dashboard to manage your customers, payments, and track your growth.
 
= Fraud Protection =
For Paystack, stopping fraud is top priority. We've used machine learning to minimize risks, reduce chargebacks and its associated costs. Paystack's fraud systems is built to learn. And so it's continually adapting to both local and international fraud.
 
We screen every transaction by checking the IP, history, geolocation etc. to proactively identify fraudulent transactions. The entire network is used to prevent fraud. We learn from card and device fingerprints used to pay across different merchants.
 
= Multiple Channels =
We've done all the heavy lifting such that you can immediately start accepting payments across all channels. Allow your customers make payments via their credit/debit card, bank accounts, USSD and Mobile Money.
 
= Paystack Go! =
Track your business performance in the palm of your hand with Paystack Go! - This is a Progressive Web App that gives you access to your dashboard even when you are offline. You can easily look up transactions, track your businesses, and send invoices on the go.
 
If your Paystack business has been activated, simply visit [go.paystack.com](https://go.paystack.com) on your mobile phone to use Paystack Go.
 
= Join our growing community =
 
When you download Paystack, you join a community of more than ten thousand merchants, developers, and enthusiasts. We're one of the fastest-growing open source communities online, and no matter your skill level we'd love to have you!
 
If you’re interested in contributing to Paystack plugins and libraries we’ve got more than 100 contributors, and there’s always room for more. Head over to the [Paystack GitHub Repository](https://github.com/paystackHQ/) to find out how you can pitch in.
 
We also have a developer community on Slack where we share product announcements, private events and discuss contributions to open source library and plugins. Join the Payslack Community [here](https://payslack.slack.com/).
 
== Installation ==
 
= Minimum Requirements =
 
* Confirm that your server can conclude a TLSv1.2 connection to Paystack's servers. More information about this requirement can be gleaned here: [TLS v1.2 requirement](https://developers.paystack.co/blog/tls-v12-requirement).
* Installed and activated [Paid Membership Pro Plugin] (https://www.paidmembershipspro.com)
 
= Manual installation =
 
The manual installation method involves downloading our payment plugin and uploading it to your webserver via your favourite FTP application. The WordPress codex contains [instructions on how to do this here](https://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).
 
 
== Frequently Asked Questions ==
 
= Where can I find help and documentation to understand Paystack? =
 
You can find help and information on Paystack on our [Help Desk](https://paystack.com/help)
 
= Where can I get support or talk to other users? =
 
If you get stuck, you can ask for help in the [Paid Memberships Pro Support](https://www.paidmembershipspro.com/support).
 
= Paystack Gateway for Paid Membership Pro is awesome! Can I contribute? =

Yes you can! Join in on our [GitHub repository](https://github.com/strangerstudios/paystack-gateway-paid-memberships-pro)
 
== Screenshots ==
 
1. The slick Paystack settings panel.

== Changelog ==
= 1.9.2 - 2026-09-28 =
* SECURITY: Improved webhook signature verification and now only complete orders created by the Paystack gateway. #18 (@dparker1005)
* SECURITY: Added direct file access protection and escaped the webhook URL on the payment settings page. #17 (@dparker1005)
* BUG FIX: Fixed a fatal error when Paystack sent webhooks to the legacy webhook URL. #19 (@dparker1005)

= 1.9.1 - 2025-11-07 =
* BUG FIX: Fixed an issue where PMPro would show a warning that the gateway is not configured correctly when Paystack is set as the primary gateway. #14 (@andrewlimaza)
* BUG FIX: Fixed an issue with error output when syncing a subscription failed in the WordPress admin. #15 (@DAnn2012)

= 1.9 - 2025-07-11 =
* BUG FIX: Fixed an issue where the payment settings fields were not showing correctly in Paid Memberships Pro 3.5+
* REFACTOR: Improved support for Paid Memberships Pro 3.0+ to better support new subscription features moving forward.

= 1.8 - 2024-11-06 =
* ENHANCEMENT: Serving updates from the paidmembershipspro.com update server.

= 1.7.8 - 2024-10-09 =
* BUG FIX: Fixed an issue where free checkouts would cause a fatal error.

= 1.7.7 - 2024-07-19 =
* ENHANCEMENT: Added support for Paid Memberships Pro V3.1+ and used `pmpro_get_element_class` method on the frontend. (@andrewlimaza)
* BUG FIX: Fixed an issue where cancellations weren't working correctly in some cases/instances. (@andrewlimaza)
* BUG FIX: Fixed an issue where billing would bill later in the day at the start of the subscription. (@andrewlimaza)

= 1.7.6 - 2024-07-03 =
* ENHANCEMENT: Added webhook logging functionality to debug incoming webhook data. You may use the constant 'PMPRO_PAYSTACK_WEBHOOK_LOG' to enable this feature. Please delete logs and disable this constant after completing debugging.
* BUG FIX: Fixed an issue where the webhook handler was not correctly storing the order amount if the subscription amount changed.
* BUG FIX: Fixed an issue when renewal payments were being processed and failing to complete the order.

= 1.7.4 - 2024-04-24 =
* ENHANCEMENT: Added new filter `pmpro_paystack_webhook_level` to tweak the level given to members after checkout. This includes support for the Set Expiration Dates Add On.

= 1.7.3 - 2024-04-15 =
* SECURITY: Improved sanitization to output of translatable strings.
* ENHANCEMENT: Added functionality for refunds within Paid Memberships Pro. Supports full refunds only.
* BUG FIX: Fixed an issue where subscriptions weren't being linked inside Paid Memberships Pro correctly when confirming the membership.

= 1.7.2 - 2024-02-15 =
* ENHANCEMENT: Added support for Paid Memberships Pro 3.0+ subscriptions.
* BUG FIX: Fixed an issue where discount codes were not reflecting on Paid Memberships Pro side.

= 1.7.1 - 2023-09-13 =
* SECURITY: Improved security to the webhook handler, this now checks for the presence of the Paystack signature header before processing the request.
* ENHANCEMENT: Only allow card checkout for recurring subscriptions as other payment options don't allow subscriptions.
* BUG FIX: Fixed an issue where intervals weren't being set correctly. This now supports all intervals, for quarterly and biannually please use 3 month and 6 month in the recurring subscription fields respectively.
* BUG FIX: Fixed an issue where non-expiring memberships would obtain an expiration date incorrectly.
* REFACTOR: Minor improvements to the settings UI page to align with other Paid Memberships Pro gateways.

= 1.4 =
* Add quarterly subscription option for recurring payments set to 3 months or 90 days cycle period.

= 1.5 = 
* Added plugin metrics tracker
* Add biannual subscription option for recurring payments set to 6 months or 180 days cycle period.


= 1.6.0 = 
* Add functionalty to delay subscriptions renewal using the PM Pro Subscription Delays addon.
= 1.6.1 = 
* BUG-FIX -  Disabled non-recurring payment methods like USSD, QR for subscription plans etc.
= 1.6.2 =
* Implement webhook to listen for cancelled subscriptions.
= 1.6.3 = 
* BUG FIX - Fix issue where setting webhook URL automatically cancels customer subscriptions.
= 1.7.0 = 
* BUG FIX - Fix issue where an 'Invalid Reference' error is being thrown when discount codes are used.
* BUG FIX - Fix issue where end date for subbscriptions is '1970-01-01'.
* Compatibility with WordPress 5.9
