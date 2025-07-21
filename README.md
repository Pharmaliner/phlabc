# PhlAbc

## Table of contents

- [Foreword 📖](#foreword-)
- [Concept 💡](#concept-)
    - [Technical Concept for TYPO3 Extension: Fine-Grained Frontend User Permissions 🛠️🔒](#technical-concept-for-typo3-extension-fine-grained-frontend-user-permissions-)
        - [1. Objectives 🎯](#1-objectives-)
        - [2. Architecture & Core Components 🏗️🔧](#2-architecture--core-components-)
            - [2.1 VoterInterface 🗳️🖥️](#21-voterinterface-)
            - [2.2 Implementation of Voters ⚙️👥](#22-implementation-of-voters-)
            - [2.3 Database Structure 🗄️📊](#23-database-structure-)
        - [3. Permission Checking Process ✅🔍](#3-permission-checking-process-)
- [Documentation 📚🛠️](#documentation-)
  - [Installation 📦](#installation-)
  - [Requirements 📋✅](#requirements-)
  - [Step-by-step guide 🪜📝](#step-by-step-guide-)
  - [Usage Guide 📘](#usage-guide-)
    - [1. Checking User Permissions 🔐](#1-checking-user-permissions-)
    - [Implementation Example ✅](#implementation-example-)
    - [How It Works 🧠](#how-it-works-)
    - [Best Practices 🔄](#best-practices-)
    - [Advanced Usage: Passing a Subject to `vote()` ➕](#advanced-usage-passing-a-subject-to-vote-)
    - [ViewHelper](#viewhelper)
      - [Declaration](#-viewhelper-declaration)
      - [Parameters](#-parameters)
      - [Basic Usage Example](#-basic-usage-example)
      - [Alternative Example Without a Subject](#-alternative-example-without-a-subject)
    - [Integration of the Extension with Permissions, Roles, and Presets](#integration-of-the-extension-with-permissions-roles-and-presets)
      - [Overview](#overview)
      - [1. Integration in composer.json](#1-integration-in-composerjson)
      - [2. Definition of Permissions (permissions.yaml)](#2-definition-of-permissions-permissionsyaml)
      - [3. Definition of Roles (roles.yaml)](#3-definition-of-roles-rolesyaml)
      - [4. Definition of Presets (presets.yaml)](#4-definition-of-presets-presetsyaml)
      - [5. Specials and Advanced Features for Presets](#5-specials-and-advanced-features-for-presets)
        - [5.1 Wildcard *](#51-wildcard-)
        - [5.2 Partial Wildcards PART_OF_PERMISSION_NAME_*](#52-partial-wildcards-part_of_permission_name_)
      - [6. Importing the Configuration](#6-importing-the-configuration)
- [Contact](#contact)


## Foreword 📖
In modern web applications, fine-grained control of user permissions plays a crucial role—especially when it comes to access to sensitive content and features in the frontend. The TYPO3 extension **PhlAbc** was developed with exactly this goal in mind: to provide flexible, centralised, and fine-grained management of permissions for frontend users.

Based on the well-established Symfony Voter concept, this extension offers an elegant and powerful architecture that allows access rights to be controlled not only at the action level but also for individual objects. This opens up a wide range of possibilities for developers and administrators to create a secure yet user-friendly experience.

PhlAbc integrates seamlessly with TYPO3 and leverages established mechanisms such as user and group management, as well as role models. The extension places particular emphasis on extensibility and ease of use, enabling it to support both standard applications and complex scenarios.

We hope this extension helps you equip your TYPO3 projects with robust and flexible frontend permission control and wish you every success in meeting your individual requirements.

## Concept 💡
### Technical Concept for TYPO3 Extension: Fine-Grained Frontend User Permissions 🛠️🔒

#### 1. Objectives 🎯

The extension aims to enable **flexible and centralised control of permissions for frontend users (FE users) at both action and object level**.  
The solution is based on the Symfony **Voter concept** and allows permission checks for individual controller actions or object access via a central interface (`VoterInterface`).

#### 2. Architecture & Core Components 🏗️🔧

##### 2.1 VoterInterface 🗳️🖥️

- A central interface defining a method `vote(string $permission, mixed $subject = null): bool`.
- The `vote()` method determines whether the currently logged-in FE user has the requested permission.
- Optionally, a **subject** (e.g. a domain model) can be passed to enable further detailed checks, such as verifying if the user is the owner of the object.

##### 2.2 Implementation of Voters ⚙️👥

- One or more concrete implementations of the `VoterInterface` encapsulate the business logic for permission checks.
- Integration with the TYPO3 frontend user session: the currently logged-in user is determined internally by the voter.
- The voter verifies the passed permission identifier (e.g. `"edit-article"`, `"view-report"`).

##### 2.3 Database Structure 🗄️📊

- **fe_users**: TYPO3’s standard table for frontend users.
- **fe_groups**: TYPO3’s standard table for frontend user groups.
- **role**: Custom table containing predefined and custom roles.
- **permission**: Table storing all defined permissions (e.g. `"edit-article"`, `"delete-record"`).
- Many-to-many relations:
    - fe_users ↔ fe_groups
    - fe_users ↔ role
    - fe_groups ↔ role
    - role ↔ permission
    - permission ↔ fe_users (for direct assignment of permissions to users)

This structure allows flexible assignment of permissions on multiple levels (users, groups, roles, permissions).

#### 3. Permission Checking Process ✅🔍

1. A **controller action** calls `VoterInterface::vote()` with a permission identifier (e.g. `"my-permission-to-access-action"`) either in the constructor or within the action method.
2. The voter implementation retrieves the currently logged-in FE user from the session.
3. If a **subject** is provided, the voter verifies whether the user has access to the object or is its creator (based on `cruser_id`).
4. The voter checks, via the many-to-many relations, whether the user has the relevant permission directly or through groups and roles.
5. Returns `true` if permission is granted, otherwise `false`.
6. The controller then enforces access control accordingly (e.g. throws an exception, redirects, or displays an error page).

---

## Documentation 📚🛠️

---
### Installation 📦

This extension is installed via Composer and **must be added as a regular dependency (`require`)** in your project. It **must not** be added as a development dependency (`require-dev`), as it is required in production.

### Requirements 📋✅

Before installing the extension, ensure your system meets the following requirements:

- **PHP 8.3**
- **PHP extension `yaml`**
- **TYPO3 v13 LTS**

### Step-by-step guide 🪜📝

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

---
### Usage Guide 📘

This section explains how to **use** the extension, focusing on key implementation details.  
In this part, we demonstrate how to **check user permissions** before accessing a specific controller action using the `VoterInterface`.

#### 1. Checking User Permissions 🔐

To restrict access to a controller action based on user permissions, the extension provides a `VoterInterface`. This allows you to check whether a user is authorized to execute a particular action.

##### Implementation Example ✅

```php
use Pharmaline\PhlAbc\Security\VoterInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use Psr\Http\Message\ResponseInterface;

class ExampleController extends ActionController
{
    public function __construct(
        private readonly VoterInterface $voter,
    ) {
    }

    public function indexAction(): ResponseInterface
    {
        if ($this->voter->vote('my-permission-to-access-action') === false) {
            // Access denied
            // You can throw an exception, redirect, or show an error message
        }

        // Access granted
        // Continue with the rest of the action logic

        // Example return (adjust to your real return logic)
        return $this->htmlResponse('Access granted');
    }
}
```
##### How It Works 🧠

- The `VoterInterface` is used to **abstract and centralize permission logic**.
- Call the `vote()` method with a **string-based permission identifier**.
- If the method returns `false`, the user **is not authorized** to perform the action.
- The currently logged-in FrontendUser is automatically determined from the session within the VoterInterface.
  It is therefore not necessary to explicitly pass the user object or user ID when calling vote().

##### Best Practices 🔄

- Use semantic permission names like `view-report`, `edit-user`, `delete-record`, etc.
- Centralize permission logic inside voters to keep controllers clean.
- Consider throwing `AccessDeniedException` or using TYPO3’s FlashMessage system for unauthorized access.

#### Advanced Usage: Passing a Subject to `vote()` ➕

The `vote()` method can optionally accept a **subject**, which may be an object or an array. The requirements are:

- The subject must have an array key `"cruser_id"`
- **Or** a property `cruser_id` including getter methods.

If neither condition is met, a `MissingOwnerAttributeInObjectException` will be thrown.

The function then verifies whether the user, identified from the session, either:

- Has the rights to access the subject, **or**
- Is the creator of the object (determined via `cruser_id`).

---
#### ViewHelper
The `VoterViewHelper` allows conditional rendering in Fluid templates based on custom permission logic implemented in a `VoterInterface`.  
It enables clean and reusable access control in the view layer.

---

##### 🛠️ ViewHelper Declaration

```php
Pharmaline\PhlAbc\ViewHelpers\VoterViewHelper
```

To use it in your Fluid templates, register the namespace:
```html
{namespace phl=Pharmaline\PhlAbc\ViewHelpers}
```

##### 🔧 Parameters

| Name           | Required | Type              | Description                                                      |
|----------------|----------|-------------------|------------------------------------------------------------------|
| `permission_key` | ✅ Yes   | string or bool  | The permission identifier to check (e.g. 'edit_post', 'view_invoice') |
| `subject`      | ❌ No    | mixed             | Optional context object used during evaluation (e.g. domain object, model) |


##### ✅ Basic Usage Example

```html
<phl:voter permission_key="edit_post" subject="{post}">
    <f:then>
        <a href="edit/{post.uid}">Edit Post</a>
    </f:then>
    <f:else>
        <span>You do not have permission to edit this post.</span>
    </f:else>
</phl:voter>
```

##### 🔄 Alternative Example Without a Subject
This works for permission keys that do not require a context object:

```html
<phl:voter permission_key="access_admin_panel">
    <f:then>
        <li><a href="/admin">Admin Panel</a></li>
    </f:then>
</phl:voter>
```

---

#### Integration of the Extension with Permissions, Roles, and Presets

##### Overview

This documentation describes the integration of an extension that defines and manages permissions, roles, and presets within a system. The configuration is done via the extension’s `composer.json`, which references YAML files for permissions, roles, and presets.

---

##### 1. Integration in composer.json

In the extension’s `composer.json`, an `extra` section must be added under the namespace of the extension (`pharmaline/abc`), pointing to the respective YAML configuration files:

```json
"extra": {
  "pharmaline/abc": {
    "roles": "/Configuration/Permission/roles.yaml",
    "presets": "/Configuration/Permission/presets.yaml",
    "permissions": "/Configuration/Permission/permissions.yaml"
  }
}
```
- roles: Path to the YAML file defining roles 
- presets: Path to the YAML file defining presets (predefined sets of permissions assigned to roles)
- permissions: Path to the YAML file defining permissions


##### 2. Definition of Permissions (permissions.yaml)
This file defines individual permissions in YAML format:

```yaml
permissions:
  - permission_key: "EXAMPLE_VIEW_LIST"
    title: "Example view list permission"
    description: "Example view list permission"
```
- permission_key: Unique key identifying the permission 
- title: Human-readable title of the permission 
- description: Description of the permission

##### 3. Definition of Roles (roles.yaml)
This file defines user roles in the system:

```yaml
roles:
  - role_key: "default"
    title: "Default Role"
    description: "Example default role."
```
- role_key: Unique key identifying the role
- title: Human-readable title of the role
- description: Description of the role

##### 4. Definition of Presets (presets.yaml)
Presets are predefined sets of permissions assigned to roles:

```yaml
presets:
  - role: "default"
    permissions:
      - "EXAMPLE_VIEW_DETAIL"
      - "EXAMPLE_VIEW_LIST"
```
- role: Role key from roles.yaml to which the preset applies
- permissions: List of permission keys from permissions.yaml assigned to the role

##### 5. Specials and Advanced Features for Presets
###### 5.1 Wildcard *
Assigning "*" as a permission in a preset means all available permissions are assigned to that role.

###### 5.2 Partial Wildcards PART_OF_PERMISSION_NAME_*
Using a pattern like EXAMPLE_* assigns all permissions starting with the prefix EXAMPLE_ to the role.
For example, EXAMPLE_* covers permissions such as EXAMPLE_VIEW_LIST, EXAMPLE_VIEW_DETAIL, etc.

##### 6. Importing the Configuration
The YAML configurations are not automatically imported during composer updates and must be explicitly imported using a CLI command.

**Import Command:**
```bash
php vendor/bin/typo3 abc:import
```
This command reads the YAML files specified in the composer.json and synchronizes the defined roles, permissions, and presets with the system.
The import should be run after changes to any of the YAML files to update the system’s configuration accordingly.

## Contact
If you have any questions, problems or suggestions for improving the extension, please feel free to contact us:

|  |                                                                                                                                                                                                                                                                       |
|---|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| **Publisher**<br>- **Name:** Pharmaline GmbH<br>- **E-Mail:** info@pharmaline.de<br>- **GitHub Issues:** [https://github.com/Pharmaliner/phlabc/issues](https://github.com/Pharmaliner/phlabc/issues)<br>- **Website:** [https://www.pharmaline.de/](https://www.pharmaline.de/) | **Developer**<br>- **Name:** MCEikens<br>- **E-Mail:** dialog@mceikens.de<br>- **GitHub Issues:** [https://github.com/Pharmaliner/phlabc/issues](https://github.com/Pharmaliner/phlabc/issues)<br>- **Website:** [https://www.mceikens.de/](https://www.mceikens.de/) |
