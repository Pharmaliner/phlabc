# Integration of the Extension with Permissions, Roles, and Presets

## Overview

This documentation describes the integration of an extension that defines and manages permissions, roles, and presets within a system. The configuration is done via the extension's `composer.json`, which references YAML files for permissions, roles, and presets.

---

## 1. Integration in composer.json

In the extension's `composer.json`, an `extra` section must be added under the namespace of the extension (`pharmaline/abc`), pointing to the respective YAML configuration files:

```json
"extra": {
  "pharmaline/abc": {
    "roles": "/Configuration/Permission/roles.yaml",
    "presets": "/Configuration/Permission/presets.yaml",
    "permissions": "/Configuration/Permission/permissions.yaml"
  }
}
```
- **roles**: Path to the YAML file defining roles
- **presets**: Path to the YAML file defining presets (predefined sets of permissions assigned to roles)
- **permissions**: Path to the YAML file defining permissions

---

## 2. Definition of Permissions (permissions.yaml)

This file defines individual permissions in YAML format:

```yaml
permissions:
  - permission_key: "EXAMPLE_VIEW_LIST"
    title: "Example view list permission"
    description: "Example view list permission"
    title_translation_key: "tx_extension.permissions.example_view_list.title"
    description_translation_key: "tx_extension.permissions.example_view_list.description"
    category: 12
```

- **permission_key**: Unique key identifying the permission
- **title**: Human-readable title of the permission
- **description**: Description of the permission
- **title_translation_key** *(optional)*: Reference to a translation key for frontend display
- **description_translation_key** *(optional)*: Reference to a translation key for extended description
- **category** *(optional)*: UID of a TYPO3 SysCategory to group permissions

---

## 3. Definition of Roles (roles.yaml)

```yaml
roles:
  - role_key: "default"
    title: "Default Role"
    description: "Example default role."
```

- **role_key**: Unique key identifying the role
- **title**: Human-readable title of the role
- **description**: Description of the role

---

## 4. Definition of Presets (presets.yaml)

Presets are predefined sets of permissions assigned to roles:

```yaml
presets:
  - role: "default"
    permissions:
      - "example-view-detail"
      - "example-view-list"
```

- **role**: Role key from `roles.yaml` to which the preset applies
- **permissions**: List of permission keys from `permissions.yaml` assigned to the role

---

## 5. Specials and Advanced Features for Presets

### 5.1 Wildcard *

Assigning `"*"` as a permission in a preset means all available permissions are assigned to that role:

```yaml
presets:
  - role: "superadmin"
    permissions:
      - "*"
```

### 5.2 Partial Wildcards PART_OF_PERMISSION_NAME_*

Using a pattern like `example-*` assigns all permissions starting with the prefix `example-` to the role:

```yaml
presets:
  - role: "admin"
    permissions:
      - "example-*"
```

---

## 6. Importing the Configuration

The YAML configurations are not automatically imported during composer updates and must be explicitly imported using a CLI command.

**Import Command:**

```bash
php vendor/bin/typo3 abc:import
```

This command reads the YAML files specified in `composer.json` and synchronizes the defined roles, permissions, and presets with the system. The import should be run after changes to any of the YAML files to update the system's configuration accordingly.

---

## 7. Voting Strategies Configuration

The extension uses a voting strategy system to combine decisions from multiple voters. Voting strategies can be configured in your TYPO3 service configuration.

### 7.1 Available Strategies

#### Unanimous Strategy (Default)
- **Any DENY → Access denied**
- **At least one GRANT (no DENY) → Access granted**
- **All ABSTAIN → Depends on `allowIfAllAbstainDecisions`**

Best for: Base permission + all constraints must pass (permission AND availability AND stock)

#### Affirmative Strategy
- **At least one GRANT → Access granted**
- **All DENY or ABSTAIN → Access denied**

Best for: Multiple alternative paths to access ("user is admin OR owner OR editor")

---

### 7.2 Configuration in Services.yaml

```yaml
services:
  # Strategy instances
  Pharmaline\PhlAbc\Security\Strategy\UnanimousStrategy:
    arguments:
      $allowIfAllAbstainDecisions: false

  Pharmaline\PhlAbc\Security\Strategy\AffirmativeStrategy: ~

  # Set the default strategy for all permission checks
  Pharmaline\PhlAbc\Security\Strategy\AccessDecisionStrategyInterface:
    alias: Pharmaline\PhlAbc\Security\Strategy\UnanimousStrategy

  # Strategy factory for per-call strategy overrides
  Pharmaline\PhlAbc\Security\Strategy\AccessDecisionStrategyFactory:
    arguments:
      $affirmative: '@Pharmaline\PhlAbc\Security\Strategy\AffirmativeStrategy'
      $unanimous: '@Pharmaline\PhlAbc\Security\Strategy\UnanimousStrategy'

  # Security Manager
  Pharmaline\PhlAbc\Security\SecurityManager:
    arguments:
      $defaultStrategy: '@Pharmaline\PhlAbc\Security\Strategy\UnanimousStrategy'
```

---

### 7.3 Strategy Selection Guidelines

**Use Unanimous (default) when:**

```php
$security->vote('order', $product);
```
- Base permission + all constraints must pass
- PermissionVoter: checks permission → GRANT or ABSTAIN
- ProductAvailabilityVoter: checks stock → GRANT or DENY
- Result: Both must pass (unanimous)

**Use Affirmative when:**

```php
$security->vote(['ROLE_ADMIN', 'ROLE_EDITOR'], null, 'affirmative');
```
- RoleVoter: checks roles → GRANT if user has any role
- Result: Any one grants access

---

### 7.4 Best Practices for Configuration

1. Default to Unanimous strategy with `allowIfAllAbstainDecisions: false`
   - Security-first approach: Access is denied unless explicitly granted

2. Use Affirmative strategy for role-based checks
   - When checking if a user has any of multiple roles
   - When checking alternative access paths

---

### 7.5 Per-Call Strategy Override

```php
// Use default strategy (unanimous)
$security->vote('order', $product);

// Override to affirmative for this specific check
$security->vote(['ROLE_ADMIN', 'ROLE_EDITOR'], null, 'affirmative');
```

In Fluid templates:

```html
<!-- Use default strategy -->
<phl:voter permissionKey="order" subject="{product}">
    <f:then>Can order</f:then>
</phl:voter>

<!-- Override to affirmative -->
<phl:voter permissionKey="ROLE_ADMIN" strategy="affirmative">
    <f:then>Is admin or editor</f:then>
</phl:voter>
```

---

## 8. Summary

The extension integration involves:

1. **Configuration via composer.json** – Define paths to permission, role, and preset YAML files
2. **YAML file definitions** – Define permissions, roles, and preset mappings
3. **Import command** – Synchronize YAML configurations with the system
4. **Voting strategy configuration** – Configure how multiple voter decisions are combined
5. **Per-call customization** – Override strategies when needed for specific checks

This flexible approach allows for centralized permission management while supporting complex access control scenarios through the voting system.
