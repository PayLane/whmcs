WHMCS PayLane Payment Gateway
============

This is a free and open source PayLane Payment Gateway for WHMCS that supports SecureForm, Card and recurring payments.

## Instructions For Use

1. Copy the files from the repository into the root directory of your WHMCS installation.
2. Run command bash `cd modules/gateways/ && composer install`

In the end, your folder structure should look roughly like the diagram below

```
whmcs
├── includes
│   └── hooks
│       ├── index.php
│       └── paylanecard.php
├── modules
│   └── gateways
│       ├── callback
│       │   └── paylane.php
│       ├── composer.json
│       ├── composer.lock
│       ├── paylanecard.php
│       ├── paylanesecureform.php
│       └── vendor
│           ├── autoload.php
│           ├── composer
│           │   ├── autoload_classmap.php
│           │   ├── autoload_namespaces.php
│           │   ├── autoload_psr4.php
│           │   ├── autoload_real.php
│           │   ├── autoload_static.php
│           │   ├── ClassLoader.php
│           │   ├── installed.json
│           │   └── LICENSE
│           └── paylane
│               └── client
│                   ├── composer.json
│                   ├── composer.lock
│                   ├── LICENSE
│                   ├── paylane
│                   │   └── PayLaneRestClient.php
│                   └── README.md

 ```

You may now activate this new payment gateway from within WHMCS through the Setup > Payments > Payment Gateways screen. This module should be listed as *PayLane Card* and *PayLane SecureForm*. You can then fill in the appropriate API keys information.
