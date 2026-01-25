# Usage Guide 📘

This section explains how to **use** the extension, focusing on key implementation details.
In this part, we demonstrate how to **check user permissions** before accessing a specific controller action using the `SecurityManager`.

## Checking User Permissions 🔐

To restrict access to a controller action based on user permissions, the extension provides a `SecurityManager`. This allows you to check whether a user is authorized to execute a particular action.

### How It Works 🧠

- The `SecurityManager` is used to **abstract and centralize permission logic**.
- Call the `vote()` method with onw or more  **permission identifier(s) (attribute(s))** and optionally a **subject** and **strategy**.
- If the method returns `false`, the user **is not authorized** to perform the action.
- The currently logged-in FrontendUser is automatically determined from the session within the SecurityManager.
  It is therefore not necessary to explicitly pass the user object or user ID when calling vote().

### Tri-State Voting System 🎭

The system uses a tri-state voting mechanism where voters return:

- **`ACCESS_GRANTED` (1)**: Voter actively allows this action
- **`ACCESS_DENIED` (-1)**: Voter actively blocks this action (business rule violated)
- **`ACCESS_ABSTAIN` (0)**: Voter has no opinion / doesn't handle this case

Multiple voters can participate in a decision, and their results are combined using a **voting strategy**.

### Voting Strategies ⚖️

#### Unanimous Strategy (Default)
- **Any DENY → Access denied**
- **At least one GRANT (no DENY) → Access granted**
- **All ABSTAIN → Access denied** (default behavior)

Best for: Base permission + all constraints must pass

#### Affirmative Strategy
- **At least one GRANT → Access granted**
- **All DENY or ABSTAIN → Access denied**

Best for: Multiple alternative paths to access ("any one grants")

### Parameters 🔧

| Name           | Required | Type          | Description                                                                                                                  |
|----------------|----------|---------------|------------------------------------------------------------------------------------------------------------------------------|
| `attributes`   | ✅ Yes   | string\|array | The permission identifier(s) to check (e.g. 'edit_post', 'view_invoice', or ['view', 'preview'])                            |
| `subject`      | ❌ No    | mixed         | Optional context object used during evaluation (e.g. domain object, model)                                                   |
| `strategy`     | ❌ No    | string\|null  | Optional voting strategy: 'unanimous' (default) or 'affirmative'                                                             |

### Basic Usage Examples 📝

#### Simple Permission Check
```php
if ($this->security->vote('view-report') === false) {
    // Access denied
}
```

#### Permission Check with Subject
```php
if ($this->security->vote('order', $product) === false) {
    // Cannot order this product (no permission or business rules failed)
}
```

#### Multiple Permissions (OR Logic)
```php
// Grant access if user can either view OR preview
if ($this->security->vote(['view', 'preview'], $document) === false) {
    // Access denied
}
```

#### Custom Voting Strategy
```php
// Use affirmative strategy: grant if user has any of these roles
if ($this->security->vote(['ROLE_ADMIN', 'ROLE_EDITOR'], null, 'affirmative') === false) {
    // Access denied
}
```

### Best Practices 📄

- Use semantic permission names like `view-report`, `edit-user`, `delete-record`, etc.
- Centralize permission logic inside voters to keep controllers clean.
- Consider throwing `AccessDeniedException` or using TYPO3's FlashMessage system for unauthorized access.
- Use the default unanimous strategy for base permission + constraint checks.
- Use affirmative strategy for "any one of these grants access" scenarios.
- Let voters return `ACCESS_ABSTAIN` when they don't have information, not `ACCESS_DENIED`.

### Advanced Usage: Passing a Subject to `vote()` ➕

The `vote()` method can optionally accept a **subject**, which can be any object or value.

**Important**: Voters use their `supports()` method to determine if they can handle a specific attribute/subject combination. This allows voters to selectively participate based on the type of subject passed.

**Example of voter support checking:**
```php
public function supports(string $attribute, mixed $subject): bool
{
    // Only vote on Product objects for 'order' action
    return $subject instanceof Product && $attribute === 'order';
}
```

When a subject is provided, voters that support it can perform context-aware checks such as:

- **Ownership verification**: Check if the user is the creator or owner of the object
- **Business rule validation**: Verify availability, stock levels, status, or other constraints
- **Relationship checks**: Determine if the user has a specific relationship to the object
- **State-based permissions**: Grant or deny access based on the object's current state

The subject is passed to all voters, but only those whose `supports()` method returns `true` will participate in the voting decision. Each participating voter can then implement its own logic to determine whether access should be granted, denied, or abstained.

**Example:**
```php
// Check if user can order this specific product
$security->vote('order', $product);

// ProductAvailabilityVoter: supports(Product) → checks stock, status
// PermissionVoter: supports(null subject only) → skips this check
// Result: Only relevant voters participate
```

### Voting Strategy Selection Guide 🎯

**Use Unanimous (default) when:**
- Base permission + all constraints must pass
- Example: Permission key AND offer availability AND stock check
- Safety-first approach (any violation blocks)

**Use Affirmative when:**
- Multiple alternative paths to access
- Example: "User is admin OR owner OR editor"
- Any one condition grants access

### Implementation Examples ✅

- [Integration in Controller](./Controller.md)
- [Integration in Service](./Service.md)
- [Integration in Static Classes](./StaticAccess.md)
- [Integration in Fluid Templates](./ViewHelper.md)
