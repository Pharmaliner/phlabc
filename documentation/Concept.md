# 2. Concept 💡
## Technical Concept for TYPO3 Extension: Fine-Grained Frontend User Permissions 🛠️🔒

### 1. Objectives 🎯

The extension aims to enable **flexible and centralised control of permissions for frontend users (FE users) at both action and object level**.  
The solution is based on the Symfony **Voter concept** and allows permission checks for individual controller actions or object access via a central interface (`VoterInterface`).

### 2. Architecture & Core Components 🏗️🔧

#### 2.1 VoterInterface 🗳️🖥️

- A central interface defining a method `vote(string $permission, mixed $subject = null): bool`.
- The `vote()` method determines whether the currently logged-in FE user has the requested permission.
- Optionally, a **subject** (e.g. a domain model) can be passed to enable further detailed checks, such as verifying if the user is the owner of the object.

#### 2.2 Implementation of Voters ⚙️👥

- One or more concrete implementations of the `VoterInterface` encapsulate the business logic for permission checks.
- Integration with the TYPO3 frontend user session: the currently logged-in user is determined internally by the voter.
- The voter verifies the passed permission identifier (e.g. `"edit-article"`, `"view-report"`).

#### 2.3 Database Structure 🗄️📊

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

### 3. Permission Checking Process ✅🔍

1. A **controller action** calls `VoterInterface::vote()` with a permission identifier (e.g. `"my-permission-to-access-action"`) either in the constructor or within the action method.
2. The voter implementation retrieves the currently logged-in FE user from the session.
3. If a **subject** is provided, the voter verifies whether the user has access to the object or is its creator (based on `cruser_id`).
4. The voter checks, via the many-to-many relations, whether the user has the relevant permission directly or through groups and roles.
5. Returns `true` if permission is granted, otherwise `false`.
6. The controller then enforces access control accordingly (e.g. throws an exception, redirects, or displays an error page).