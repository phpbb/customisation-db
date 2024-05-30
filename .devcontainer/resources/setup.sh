# Copy Apache configuration
# sudo rm /etc/apache2/sites-enabled/000-default.conf
# sudo cp .devcontainer/resources/phpbb-apache.conf /etc/apache2/sites-enabled/000-default.conf

# Start MySQL
sudo service mysql start

# Start Apache
sudo service apache2 start

# Add SSH key
echo "$SSH_KEY" > /home/vscode/.ssh/id_rsa && chmod 600 /home/vscode/.ssh/id_rsa

# Create a MySQL user to use
sudo mysql -u root<<EOFMYSQL
    CREATE USER 'phpbb'@'localhost' IDENTIFIED BY 'phpbb'; 
    GRANT ALL PRIVILEGES ON *.* TO 'phpbb'@'localhost' WITH GRANT OPTION;
    CREATE DATABASE IF NOT EXISTS phpbb;
EOFMYSQL

# Download dependencies
echo "Dependencies"
composer install --no-interaction

# Install phpBB
echo "phpBB project"
composer create-project --no-interaction phpbb/phpbb /workspaces/phpbb

# Copy phpBB config
echo "Copy phpBB config"
sudo cp /workspaces/customisation-db/.devcontainer/resources/phpbb-config.yml /workspaces/phpbb/install/install-config.yml

# https://docs.github.com/en/codespaces/developing-in-a-codespace/default-environment-variables-for-your-codespace
sudo export PHPBB__CODESPACESX=$CODESPACES

echo "Symlink extension"
sudo rm -rf /var/www/html
sudo ln -s /workspaces/phpbb /var/www/html
sudo mkdir /workspaces/phpbb/ext/phpbb
sudo ln -s /workspaces/customisation-db /workspaces/phpbb/ext/phpbb/titania

echo "phpBB CLI install"
cd /workspaces/phpbb && composer install --no-interaction
sudo php /workspaces/phpbb/install/phpbbcli.php install /workspaces/phpbb/install/install-config.yml
sudo rm -rf /workspaces/phpbb/install 

echo "Completed"
