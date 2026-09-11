# 3.1. Installation 📦

This extension is installed via Composer and **must be added as a regular dependency (`require`)** in your project. It **must not** be added as a development dependency (`require-dev`), as it is required in production.

## Requirements 📋✅

Before installing the extension, ensure your system meets the following requirements:

- **PHP 8.4**
- **PHP extension `yaml`**
- **TYPO3 v13 LTS**

## Step-by-step guide 🪜📝

1. **Install the extension via Composer**

   Run the following command in the root directory of your TYPO3 project:
    ```bash
    composer require pharmaline/phlabc
    ```

2. **Update the database schema**

   After installation, execute the following command to apply any required database changes:
    ```bash
    vendor/bin/typo3 database:updateschema
    ```

3. **Import permissions, roles, and presets** (optional)

   If your extension defines custom permissions, roles, or presets, import them using:
    ```bash
    vendor/bin/typo3 abc:import
    ```

   This step is required if you're using custom YAML configuration files for permissions and roles.

   It also removes stale category relations for permissions to ensure data consistency.
