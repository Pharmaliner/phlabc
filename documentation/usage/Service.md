# Overview

The `ExampleService` class is an example service that implements access control using a `SecurityManager`. It checks permissions before allowing execution of certain actions. This enables centralized and consistent authorization logic within an application.

## Namespace & Dependencies

```php
use Pharmaline\PhlAbc\Security\SecurityManager;
```

- Namespace: The `SecurityManager` is expected to reside in the `Pharmaline\PhlAbc\Security` namespace.
- Dependency: `ExampleService` depends on `SecurityManager` which provides voting-based permission checks.

## Constructor
```php
public function __construct(
    private readonly SecurityManager $security,
)
```
- Injection: Dependency is injected via the constructor.
- readonly: The `$security` property is immutable after construction.
- Purpose: Allows the service to delegate permission decisions to the voter system.

## Method: serviceMethod()
```php
public function serviceMethod(): string
```

### Purpose
This method demonstrates a typical protected service flow:

1. Checks if the current user has the permission `"my-permission-to-access-action"`.
2. Returns `"Access denied"` if permission is not granted.
3. Returns `"Access granted"` if permission is granted. (This is a placeholder for real business logic.)

### Logic

```php
if ($this->security->vote('my-permission-to-access-action') === false) {
    return 'Access denied';
}
return 'Access granted';
```

- `vote()`: Returns a boolean – `true` if access is allowed, `false` otherwise.
- **Return value**: A simple string indicating access result. Real implementations would continue with business logic.

## Example Usage

### Basic Permission Check
```php
public function __construct(
    private readonly SecurityManager $security,
)
{
}

public function serviceMethod(): string
{
    if ($this->security->vote('my-permission-to-access-action') === false) {
        return 'Access denied';
    }

    // Access granted
    // Continue with the rest of the action logic

    return 'Access granted';
}
```

### Permission Check with Subject
```php
public function processOrder(Product $product): bool
{
    if ($this->security->vote('order', $product) === false) {
        // Access denied - either no permission or business rules failed
        throw new AccessDeniedException('Cannot order this product');
    }

    // Process the order
    // ...

    return true;
}
```

### Multiple Permissions (OR Logic)
```php
public function viewDocument(Document $document): string
{
    // Grant access if user can either view OR preview
    if ($this->security->vote(['view', 'preview'], $document) === false) {
        return 'Access denied';
    }

    // Return document content
    return $document->getContent();
}
```

### Custom Voting Strategy
```php
public function adminOperation(): string
{
    // Use affirmative strategy: grant if user has any of these roles
    if ($this->security->vote(['ROLE_ADMIN', 'ROLE_SUPERADMIN'], null, 'affirmative') === false) {
        return 'Admin access required';
    }

    // Perform admin operation
    return 'Operation completed';
}
```

## Benefits
- **Separation of concerns**: Authorization logic is separated via the voter interface.
- **Testability**: Easy to mock the SecurityManager / Voter(s) in unit tests.
- **Reusability**: The service can be reused in different contexts with different voters.
- **Flexibility**: Supports multiple attributes, subjects, and voting strategies.

## Extensibility
- Additional methods with different permission checks can be added easily.
- The method could be extended to throw exceptions or return richer response objects.
- Custom voters can be added to implement complex business rules without changing service code.
