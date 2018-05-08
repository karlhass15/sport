Wagento Subscription Module for PayPal
===========================================

This Module for Magento® 2 for purchase products as subscription.

Facts
-----
* version: 2.0.0

Description
-----------
* This Module for Magento® 2 for purchase products as subscription

Requirements
------------
* PHP >= 5.6.5

Compatibility
-------------
* Magento >= 2.2.0

Installation Instructions
-------------------------
The Wagento Subscription module for Magento® 2 is distributed in two formats:
* Drop-In
* [Composer VCS](https://getcomposer.org/doc/05-repositories.md#using-private-repositories)

### Install Source Files ###

The following sections describe how to install the module source files,
depending on the distribution format, to your Magento® 2 instance.

#### Drop-In ####
If you received a single ZIP file with no `composer.json` file included, extract
its contents to the project root directory. The module sources should then be
available in the following sub-directory:

    app
    └── code
        └── Wagento
            └── Subscription

#### VCS ####
If you prefer to install the module using [git](https://git-scm.com/), run the
following commands in your project root directory:

    composer config repositories.wagento-module-subscription vcs git@bitbucket.org:wagento-global/subscription.git
    composer require wagento/module-subscription:dev-master

### Enable Module ###
Once the source files are available, make them known to the application:

    ./bin/magento module:enable Wagento_Subscription
    ./bin/magento setup:upgrade

Last but not least, flush cache and compile.

    ./bin/magento cache:flush
    ./bin/magento setup:di:compile

Uninstallation
--------------

The following sections describe how to uninstall the module, depending on the
distribution format, from your Magento® 2 instance.

#### Composer Git and Composer Artifact ####

To unregister the shipping module from the application, run the following command:

    ./bin/magento module:uninstall --remove-data Wagento_Subscription

This will automatically remove source files and clean up the database.

#### Drop-In ####

To uninstall the module manually, run the following commands in your project
root directory:

    ./bin/magento module:disable Wagento_Subscription
    rm -rf app/code/Wage/Subscription
    
Developer
---------
* Sanjay Patel | [Wagento](https://www.wagento.com/) | sanjay@wagento.com

License
-------
[OSL - Open Software Licence 3.0](http://opensource.org/licenses/osl-3.0.php)
