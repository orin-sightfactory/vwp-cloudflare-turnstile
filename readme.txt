=== VisualWP Cloudflare Turnstile ===
Contributors: Sightfactory
Tags: turnstile,anti-spam,captcha
Requires at least: 5.9
Tested up to: 6.1.1
Requires PHP: 7.4
Stable tag: 1.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html

Increase security and protect against bots, spammers and hackers with Cloudflare Turnstile, a friendly, free CAPTCHA replacement

== Description ==
Increase security and protect against bots, spammers and hackers. Cloudflare Turnstile is a friendly, free CAPTCHA replacement that delivers a frustration-free experience to website visitors.


= USE OF 3RD PARTY SERVICES =
VisualWP Cloudflare Turnstile relies on [Cloudflare Turnstile](https://www.cloudflare.com/products/turnstile/), a 3rd party API that connects to Cloudflare servers to generate the anti-spam field and will not work if website traffic to Cloudflare servers is blocked or impeded. Cloudflare has to look at some session data (like headers, user agent, and browser characteristics) to validate users without challenging them. Learn more by visiting Cloudflare  [Terms of Service](https://www.cloudflare.com/website-terms/) and [Privacy Policy](https://www.cloudflare.com/privacypolicy/)

[Login or Register on Cloudflare](https://www.cloudflare.com/products/turnstile/)  to generate your site key, secret key and setup approved domains. Customers are limited to 1 million siteverify calls per month during the open beta or can upgrade to [Enterprise Bot Management](https://developers.cloudflare.com/bots/get-started/bm-subscription/) via Cloudflare website for cases exceeding the free tier.


= CLOUDFLARE TERMS OF USE AND PRIVACY POLICY =


Terms of Service: [https://www.cloudflare.com/website-terms/](https://www.cloudflare.com/website-terms/)
Privacy Policy: [https://www.cloudflare.com/privacypolicy/](https://www.cloudflare.com/privacypolicy/)

== Installation ==
1. Download and save the **Visual WP Cloudflare Turnstile** plugin to your hard disk.
2. Login to your WordPress site and go to the Add Plugins page.
3. Click Upload Plugin button to upload the zip.
4. Click Install Now to install and activate the plugin.
5. Go to the settings page under Settings > Turnstile
6. Add your Site Key and Secret Key or create one from your Cloudflare account.
7. Click on Save Changes to save your keys to the database
8. Click on Test Website Configuration to ensure your website is communicating correctly with Cloudflare servers.
9. Try logging into your website in a private window to test that you can login correctly.
10. Finally, toggle the 'Enable Turnstile' in the settings screen to activate Turnstile protection on your website

== Changelog ==
* 1.0.2 Added feature to test configuration
* 1.0.1 Bug fix
* 1.0 Initial Release

== Upgrade Notice ==
* 1.0.2 New feature to enable/disable protection

== Frequently Asked Questions ==

= Where do I get a site key and secret key? =

You must generate your site key and secret key from your [Cloudflare account](https://www.cloudflare.com/products/turnstile/)

= What happens if I get locked out? =

Use the **Test Website Configuration** button on the Visual WP Turnstile settings screen to ensure your website can communicate with Cloudflare servers after saving your site key and secret key. This will ensure that things have been correctly set up. 

Also after saving your changes, but before logging out, try logging in using a different browser or incognito window to ensure you can safely log in before logging out of your current session. 

**If you are locked out and you have database access:**

Execute the following SQL commands in your database:
DELETE FROM wp_options WHERE option_name = 'vwptn_turnstile_status';

(Note, wp_options might have a different prefix in your installation depending on your preferred table prefix.)

**If you are locked out and you have file or ftp access:**
Browse to your plugins folder, normally in /wp-content/plugins and rename the vwp-cloudflare-turnstile folder to anything else such as vwp-cloudflare-turnstile_disabled
Once you are logged in, if you would like to re-activate the plugin, rename the plugin back to its original name and activate it from the Plugins screen. Then you can go to the settings screen to remove, update or replace the non-working site key or secret key. Double check the settings in your Cloudflare dashboard to ensure you have added the correct site key and secret key for the correct domain.
