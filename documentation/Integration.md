# Integration of the Extension with Permissions, Roles, and Presets

## Overview

This documentation describes the integration of an extension that defines and manages permissions, roles, and presets within a system. The configuration is done via the extension’s `composer.json`, which references YAML files for permissions, roles, and presets.

---

## 1. Integration in composer.json

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


## 2. Definition of Permissions (permissions.yaml)
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

## 3. Definition of Roles (roles.yaml)
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

## 4. Definition of Presets (presets.yaml)
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

## 5. Specials and Advanced Features for Presets
### 5.1 Wildcard *
Assigning "*" as a permission in a preset means all available permissions are assigned to that role.

```yaml
presets:
  - role: "superadmin"
    permissions:
      - "*"
```

### 5.2 Partial Wildcards PART_OF_PERMISSION_NAME_*
Using a pattern like EXAMPLE_* assigns all permissions starting with the prefix EXAMPLE_ to the role.
For example, EXAMPLE_* covers permissions such as EXAMPLE_VIEW_LIST, EXAMPLE_VIEW_DETAIL, etc.

```yaml
presets:
  - role: "admin"
    permissions:
      - "EXAMPLE_*"
```

## 6. Importing the Configuration
The YAML configurations are not automatically imported during composer updates and must be explicitly imported using a CLI command.

**Import Command:**
```bash
php vendor/bin/typo3 abc:import
```
This command reads the YAML files specified in the composer.json and synchronizes the defined roles, permissions, and presets with the system.
The import should be run after changes to any of the YAML files to update the system’s configuration accordingly.
