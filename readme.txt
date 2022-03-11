=== Paystack Gateway for Paid Membership Pro ===
Contributors: paystack, kendysond, steveamaza, lukman008
Donate link: https://paystack.com/demo
Tags: paystack, recurrent payments, nigeria, mastercard, visa, target, Naira, payments, verve, paid membership pro
Requires at least: 3.1
Tested up to: 5.9
Stable tag: 1.7.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Pay with Paystack on Paid Membership Pro

== Description ==
 
Paid Membership Pro is a complete member management and membership subscriptions plugin for WordPress. Paid Memberships Pro is designed for premium content sites, clubs/associations, subscription products, newsletters and more!
 
The **Paystack Gateway for Paid Membership Pro** allows site owners from Nigeria and Ghana to accept payments from their customers via Paid Membership Pro.

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
* Installed and activated Paid Membership Pro Plugin
 
= Automatic installation =
 
Automatic installation is the easiest option as WordPress handles the file transfers itself and you don’t need to leave your web browser. To do an automatic install of WPJobster Paystack Gateway, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.
 
In the search field type "Paystack Gateway for Paid Membership Pro” and click Search Plugins. Once you’ve found our payment plugin you can view details about it such as the point release, rating and description. Most importantly of course, you can install it by simply clicking “Install Now”.
 
= Manual installation =
 
The manual installation method involves downloading our payment plugin and uploading it to your webserver via your favourite FTP application. The WordPress codex contains [instructions on how to do this here](https://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).
 
= Updating =
 
Automatic updates should work like a charm; as always though, ensure you backup your site just in case.
 
== Frequently Asked Questions ==
 
= Where can I find help and documentation to understand Paystack? =
 
You can find help and information on Paystack on our [Help Desk](https://paystack.com/help)
 
= Where can I get support or talk to other users? =
 
If you get stuck, you can ask for help in the [Paystack Gateway for Paid Membership Pro Plugin Forum](https://wordpress.org/support/plugin/paystack-gateway-paid-memberships-pro). You can also directly email support@paystack.com for assistance.
 
= Paystack Gateway for Paid Membership Pro is awesome! Can I contribute? =

Yes you can! Join in on our [GitHub repository](https://github.com/PaystackHQ/paystack-gateway-for-paid-memberships-pro) :)
 
== Screenshots ==
 
1. The slick Paystack settings panel.

== Changelog ==

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
