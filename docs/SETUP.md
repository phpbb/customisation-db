# Titania

This is a quick setup guide to walk you through the process of installing and configuring Titania.

## Requirements

1. phpBB 3.1.x, 3.2.x or 3.3.x

2. PHP 5.3.3 or newer (see your phpBB board's requirement)

3. Mod Rewrite must be available

4. MultiViews must be disabled

5. For the Styles Demo board:
    - If not installing on the main board, it must be on the same server, on a different database, and with the same database prefix as the main board uses (limitation of current code using table constants).


## Installation

1. Create a phpBB board.
   
   The easiest option is to use [QuickInstall](https://www.phpbb.com/customise/db/official_tool/phpbb3_quickinstall/) to create a phpBB board. Make sure your config.php is set to Production mode.
   
2. Download [Titania](https://github.com/phpbb/customisation-db) from github, using the branch that corresponds with your phpBB board's version (3.1.x or 3.2.x etc.).

3. Rename the downloaded folder to `titania` and copy it to the `ext/phpbb` folder of your phpBB board.

4. Navigate to your board's ACP and enable the Titania extension.

5. Use CLI to navigate to root of Titania and run composer to install its dependencies.

   ```
   $ cd phpBB/ext/phpbb/titania
   $ php composer.phar install
   ```
6. Make the `files/` and `store/` directories (and sub-directories) writable by the server.

## Configuration
   
1. Give yourself permissions to use Titania:

    Go to ACP permissions -> "Group permissions". 
    
    Select "Administrators". 
    
    Go to the "Titania Moderate" tab and set all to yes.

2. Go to Titania: `http://yoursite.com/phpBB3/db/`

   It can be helpful to create a Forum Link to Titania's URL so it's easy find it from the index page of your board.

3. Configure any settings as required.

   Go to "Manage" -> "Administration" -> "Configuration Settings".
   
4. Create extension categories.
 
   Go to "Manage" -> "Administration" -> "Manage Categories".

   Edit the existing "Modifications" category and rename it "Extensions". Leave Category type as "--".

   Click on the "Extensions" category and edit any of the existing sub categories you want to keep by changing their category type to "Extensions".
   
Titania should now be usable.

## Maintenance

After each new release of phpBB you need to perform a few actions:

1. Upload the properly named package (ex: phpBB-3.0.7-PL1.zip for 3.0.7-pl1) to `includes/phpbb_packages`.

2. Update the `phpbb_versions` information in the `config/config.php` file to contain the latest version for the updated phpBB branch.
