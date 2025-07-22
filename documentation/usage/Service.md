# Overview

The `ExampleService` class is an example service that implements access control using a `VoterInterface`. It checks permissions before allowing execution of certain actions. This enables centralized and consistent authorization logic within an application.

## Namespace & Dependencies

```php
use Pharmaline\PhlAbc\Security\VoterInterface;
```

- Namespace: The `VoterInterface` is expected to reside in the `Pharmaline\PhlAbc\Security` namespace. 
- Dependency: `ExampleService` depends on an implementation of `VoterInterface` which provides a method to check permissions.

## Constructor
```php
public function __construct(
    private readonly VoterInterface $voter,
)
```
- Injection: Dependency is injected via the constructor. 
- readonly: The `$voter` property is immutable after construction. 
- Purpose: Allows the service to delegate permission decisions to the voter implementation.

## Method: serviceMethod()
```php
public function serviceMethod(): string
```

### Purpose
This method demonstrates a typical protected service flow:

1. Checks if the current user (implicitly via the `VoterInterface` implementation) has the permission `"my-permission-to-access-action"`. 
2. Returns `"Access denied"` if permission is not granted. 
3. Returns `"Access granted"` if permission is granted. (This is a placeholder for real business logic.)

### Logic

```php
if ($this->voter->vote('my-permission-to-access-action') === false) {
    return 'Access denied';
}
return 'Access granted';
```

- `vote()`: Returns a boolean — `true` if access is allowed, `false` otherwise. 
- **Return value**: A simple string indicating access result. Real implementations would continue with business logic.

## Example Usage

```php
    public function __construct(
        private readonly VoterInterface $voter,
    )
    {
    }

    public function serviceMethod(): string
    {
        if ($this->voter->vote('my-permission-to-access-action') === false) {
            // Access denied
            return 'Access denied';
        }

        // Access granted
        // Continue with the rest of the action logic

        // Example return (adjust to your real return logic)
        return 'Access granted';
    }
```

## Benefits
- **Separation of concerns**: Authorization logic is separated via the voter interface. 
- **Testability**: Easy to mock the voter in unit tests. 
- **Reusability**: The service can be reused in different contexts with different voters.

## Extensibility
- Additional methods with other permission checks can be added easily. 
- The method could be extended to throw exceptions or return richer response objects (e.g., in a web controller).