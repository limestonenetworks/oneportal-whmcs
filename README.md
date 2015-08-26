# WHMCS addon for OnePortal

This WHMCS addon is provided without support. Limestone Networks' support department does not provide any assistance with the installation or use of this addon.

# Installation

TLS 1.2 support for curl is required.

Dedicated servers: Move/Upload all of the files in the oneportal folder to modules/servers/oneportal
Cloud servers: Move/Upload all of the files in the oneportalcloud folder to modules/servers/oneportalcloud

# Dedicated Servers Setup

For every hosting product/service while you set them up:

1.  Go to the "Module Settings" tab
2.  Select "Oneportal" as the "Module Name"
3.  Input your "API Key" from OnePortal's "Administrative" -> "API"
4.  Check any options you'd like
5.  Change the "rDNS Domain" to your domain name
6.  Leave the option as "Do not automatically setup this product"

Then, go to the "Custom Fields" and add a field with the following:

- Field Name: Server ID
- Field Type: Text Box
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

# Cloud Servers Setup

For every hosting product/service while you set them up:

1.  Create a new Oneportal user to be used with the API. USERNAME-api or something similar.
2.  Give your new user the privileges you desire. It will need most of them in order for this module to function properly. For a quick start just check all.
3.  Open WHMCS and go to the "Module Settings" tab
4.  Select "Oneportalcloud" as the "Module Name"
5.  Input your Oneportal api user and password. This user must have the correct privileges set from within Oneportal.
6.  Check any options you'd like
7.  The options for Ram/Storage/OS/Cores serve as the default for any cloud server you provision.
This gives you the ability to either create multiple products with a set configuration or you can create configurable options for these fields.
Scalability will not be possible without configurable options being setup.
8.  Change the "rDNS Domain" to your domain name
9.  When the product is activated it will create the server and set the Server ID (explained below) for you.

Then, go to the "Custom Fields" and add a field with the following:

- Field Name: Server ID
- Field Type: Text Box
- (optionally) Description: Unique Server ID within the data center
- (optionally) Click "Admin Only" if you don't want to show this information to the client
- (optionally) Click "Show on invoice" if you want the Server ID on your clients' invoices

## Setting up configurable options

If you wish to have scalable cloud servers and/or give your users the ability to choose the configuration options of their cloud server then follow the instructions here.

1. Go to Setup -> Products/Services -> Configurable Options
2. Create a new group
3. Click the edit group button
4. Click add new configurable option
5. From this screen you can add the options that you wish
6. The option names are case sensitive and must be one of the following: Ram, Storage, Cores, OS, IPs
7. The option name that is visible on the order form can be customized by using the | character. Example: IPs|IP Addresses
8. Choose type of dropdown and add an entry for each option you want to be available. The accepted options for each type are listed below.
9. The input is expected to be one from this list. If it is not then it will result in the server being unable to provision.

Option parameters
> Ram => 512MB,1GB,2GB,4GB,8GB,16GB,32GB

> Storage => 5GB,10GB,15GB,20GB,50GB,100GB,120GB,140GB,160GB,180GB,200GB

> Cores => 1,2,3,4,5,6,7,8,9,10,11,12

> OS => CentOS 5.9 x64,CentOS 6.4 x64,Debian 7.0 x64,Fedora 18 x64,Gentoo 12.1 x64,Red Hat Enterprise Linux 5.9 x64,Red Hat Enterprise Linux 6.4 x64,Ubuntu 14.10 x64,Arch Linux 2012.12 x64,CloudLinux Server 6.4 x64,Fedora 19 x64,openSUSE 12.1 x86,PBXware 3.1 x86,Scientific Linux 6.2 x64,Slackware 13.37 x64,Windows 2012 Standard Edition R2 X64

> IPs => 1 IP, 2 IPs, 3 IPs, 4 IPs, 5 IPs

> Control Panel => None, cPanel

* Note about control panel: If centos 5/6 or cloud linux is chosen for the operating system then cpanel will be preinstalled and a license issued. If another operating system is chosen then it will not be preinstalled but a license will still be issued.
