# 2. Concept 💡
## Technical Concept for TYPO3 Extension: Fine-Grained Frontend User Permissions 🛠️🔒

### 1. Objectives 🎯

The extension aims to enable **flexible and centralised control of permissions for frontend users (FE users) at both action and object level**.
The solution is based on the Symfony **Voter concept** and allows permission checks for individual controller actions or object access via a central interface (`VoterInterface`).

### 2. Architecture & Core Components 🏗️🔧

#### 2.1 VoterInterface 🗳️🖥️

- A central interface defining voting methods for access control.
- The `supports()` method determines whether a voter can handle a specific permission check.
- The `voteOnAttribute()` method returns a **tri-state decision**: `ACCESS_GRANTED` (1), `ACCESS_DENIED` (-1), or `ACCESS_ABSTAIN` (0).
- Optionally, a **subject** (e.g. a domain model) can be passed to enable further detailed checks, such as verifying if the user is the owner of the object.

#### 2.2 SecurityManager 🔐

- Provides the central `vote()` method: `vote(string|array $attributes, mixed $subject = null, ?string $strategy = null): bool`.
- The **attribute** parameter represents the permission or action being checked (e.g. `"edit-article"`, `"view-report"`).
- The **subject** parameter is optional and contains the object to check permissions on.
- The **strategy** parameter allows per-call override of the voting strategy.
- Integration with the TYPO3 frontend user session: the currently logged-in user is determined internally.
- Supports checking multiple attributes with OR logic (access is granted if any attribute passes).

#### 2.3 Tri-State Voting System 🎭

Voters return one of three states:

- **`ACCESS_GRANTED` (1)**: Voter actively allows this action
- **`ACCESS_DENIED` (-1)**: Voter actively blocks this action (business rule violated)
- **`ACCESS_ABSTAIN` (0)**: Voter has no opinion / doesn't handle this case

**Critical Rule**: Use `ACCESS_DENIED` only when actively blocking due to violated business rules. Use `ACCESS_ABSTAIN` when the voter simply doesn't have information to decide.

#### 2.4 Voting Strategies ⚖️

Strategies determine how multiple voter results are combined:

##### Unanimous Strategy (Default)
- **Any DENY → Access denied**
- **At least one GRANT (no DENY) → Access granted**
- **All ABSTAIN → Depends on `allowIfAllAbstainDecisions`** (default: false = deny)

Best for: Base permission + all constraints must pass

##### Affirmative Strategy
- **At least one GRANT → Access granted**
- **All DENY or ABSTAIN → Access denied**

Best for: Multiple alternative paths to access ("any one grants")

#### 2.5 Database Structure 🗄️📊

- **fe_users**: TYPO3's standard table for frontend users.
- **fe_groups**: TYPO3's standard table for frontend user groups.
- **role**: Custom table containing predefined and custom roles.
- **permission**: Table storing all defined permissions (e.g. `"edit-article"`, `"delete-record"`).
- Many-to-many relations:
    - fe_users ↔ fe_groups
    - fe_users ↔ role
    - fe_groups ↔ role
    - role ↔ permission
    - permission ↔ fe_users (for direct assignment of permissions to users)

This structure allows flexible assignment of permissions on multiple levels (users, groups, roles, permissions).

### 3. Permission Checking Process ✅🔍

1. A **controller action**, **service** or **ViewHelper** calls `SecurityManager::vote()` with a permission identifier (e.g. `"edit-article"`) and optionally a subject (e.g. an article object).
2. The SecurityManager retrieves the currently logged-in FE user from the session.
3. The SecurityManager identifies all voters that support the given attribute and subject (via `supports()` method).
4. Each supporting voter's `voteOnAttribute()` method is called, returning ACCESS_GRANTED, ACCESS_DENIED, or ACCESS_ABSTAIN.
5. The configured voting strategy (or per-call override) combines the votes into a final boolean decision.
6. If a **subject** is provided, voters can verify business rules (e.g., offer availability, stock levels) or ownership (based on `cruser_id`).
7. Returns `true` if access is granted, otherwise `false`.
8. The controller, service or ViewHelper then enforces access control accordingly (e.g. throws an exception, redirects, or displays an error page).

### 4. Advanced Features 🚀

#### 4.1 Multiple Attributes Support
Check multiple permissions with OR logic - access is granted if any attribute passes:
```php
$security->vote(['view', 'preview'], $document);
```

#### 4.2 Per-Call Strategy Override
Override the default strategy for specific checks:
```php
$security->vote(['ROLE_ADMIN', 'ROLE_EDITOR'], null, 'affirmative');
```

#### 4.3 Subject-Based Permissions
Pass domain objects to enable context-aware permission checks:
```php
$security->vote('order', $product); // Check if user can order this specific product
```
