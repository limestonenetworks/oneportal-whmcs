# WHMCS addon for OnePortal

## Installation

Move/Upload all of the files (oneportal.api.php  oneportal.php  README  usageupdate.cron.php) to modules/servers/oneportal


## Setup

For every hosting product/service while you set them up:

1.  Go to the "Module Settings" tab
2.  Select "Oneportal" as the "Module Name"
3.  Input your "API Key" from OnePortal's "Administrative" -> "API"
4.  Check any options you'd like
5.  Change the "rDNS Domain" to your domain name
6.  Leave the option as "Do not automatically setup this product"

Then, go to the "Custom Fields" and add a field with the following:

- Field Name: Server ID
- Field Type: Server ID
- (optionally) Description: Unique Server ID within the data center
- (optionally) Click "Admin Only" if you don't want to show this information to the client
- (optionally) Click "Show on invoice" if you want the Server ID on your clients' invoices

Then, when you provision a new server for a client:

1.  Modify the product through the client's "Products/Services" tab
2.  Locate the "Server ID" field and put in the server's ID as either "D####" or "LSN-D####" where the # signs are the ID of your server in OnePortal
3.  Click "Save Changes"
4.  After the server is provisioned in the data center more information will be available in the "Bandwidth", "Hardware" and "IP Addresses" sections of the product



## Bandwidth Statistics (optional, may not work due to WHMCS limitations)

To update bandwidth usage, you should setup a cron job for the following:

	php -q /full_path_to/whmcs/modules/servers/oneportal/usageupdate.cron.php

We recommend every 5 minutes. If you use this feature, be sure to put your API key on line 4 of usageupdate.cron.php where you see "$oneportal_api_key ="