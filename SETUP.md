# Titania

This is a quick setup guide to walk you through the process of installing and configuring Titania.

## Installation

1. Create a phpBB board.
   
   The easiest option is to use [QuickInstall](https://www.phpbb.com/customise/db/official_tool/phpbb3_quickinstall/) to create a phpBB board. Make sure your config.php is set to Production mode.
   
2. Download [Titania](https://github.com/VSEphpbb/customisation-db) from github, using the **3.2.x** branch.

3. Rename the downloaded folder to `titania` and copy it to the `ext/phpbb` folder of your phpBB board.

4. Navigate to your board's ACP and enable the Titania extension.

5. Use CLI to navigate to root of Titania and run composer to install its dependencies.

   ```
   $ cd phpBB/ext/phpbb/titania
   $ php composer.phar install
   ```
   
## Configuration
   
1. Give yourself permissions to use Titania:

    Go to ACP permissions -> "Group permissions". 
    
    Select "Administrators". 
    
    Go to the "Titania Moderate" tab and set all to yes.

2. Go to Titania: `http://yoursite.com/phpBB3/db/`

   It can be helpful to create a Forum Link to Titania's URL so it's easy find it from the index page of your board.
   
3. Create extension categories.
 
   Go to "Manage" -> "Administration" -> "Manage Categories".

   Edit the existing "Modifications" category and rename it "Extensions". Leave Category type as "--".

   Click on the "Extensions" category and edit any of the existing sub categories you want to keep by changing their category type to "Extensions".
   
Titania should now be usable.
