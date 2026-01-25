Overview
--------

The ExampleServiceStatic class implements a **static access pattern** to a SecurityManager service responsible for permission checks. It uses a StaticServiceLocator to inject the SecurityManager implementation into a static context, enabling permission validation even without an object instance.

Purpose and Motivation
----------------------

In PHP, **static methods** cannot benefit from **constructor-based dependency injection**, which makes it difficult to access services like a SecurityManager (for permission checks) within a static context.

This code solves the problem by using a **Service Locator pattern** to store the SecurityManager instance at runtime, allowing static methods to retrieve it when needed.

### Why is this approach necessary?

- **Static method (serviceFunction) needs access to a SecurityManager service**
    - But static methods don't support constructor injection.
- **Decouples** permission logic from the static service logic.
- **Centralized access control** logic.
- Allows reusability of permission checks across static utility functions.


Component Breakdown
-------------------

### SecurityManager

A service that provides voting-based permission checks:

```php
public function vote(
    string|array $attributes,
    mixed $subject = null,
    ?string $strategy = null
): bool;
```

### StaticServiceLocator

A utility that manages the static access to the SecurityManager service. It provides:

- setService(SecurityManager $security) – Stores the SecurityManager instance statically.
- getService(): SecurityManager – Returns the stored instance.


> **Important**: This must be set early during application bootstrapping or service initialization.

### Constructor

```php
public function __construct(SecurityManager $security)
```

- Accepts a SecurityManager implementation.
- Registers the SecurityManager via the StaticServiceLocator.


**Usage**: Called once (e.g., during dependency injection container setup) to make the SecurityManager available statically.

### Static Method: serviceFunction()

```php
public static function serviceFunction(): string
```

Functionality:
1. Retrieves the current SecurityManager via the service locator.
2. Calls vote('my-permission-to-access-action') to check access.
3. Returns "Access denied" if the check fails.
4. Returns "Access granted" if permission is granted.

### Example Usage

### Basic Permission Check
```php
public function __construct(
    SecurityManager $security,
)
{
    StaticServiceLocatorUtility::setService($security);
}

public static function serviceFunction(): string
{
    $security = StaticServiceLocatorUtility::getService();
    if ($security->vote('my-permission-to-access-action') === false) {
        // Access denied
        return 'Access denied';
    }

    // Access granted
    // Continue with the rest of the action logic

    return 'Access granted';
}
```

### Permission Check with Subject
```php
public static function canOrderProduct(Product $product): bool
{
    $security = StaticServiceLocatorUtility::getService();
    return $security->vote('order', $product);
}
```

### Multiple Permissions (OR Logic)
```php
public static function canAccessDocument(Document $document): bool
{
    $security = StaticServiceLocatorUtility::getService();
    // Grant access if user can either view OR preview
    return $security->vote(['view', 'preview'], $document);
}
```

### Custom Voting Strategy
```php
public static function isAdmin(): bool
{
    $security = StaticServiceLocatorUtility::getService();
    // Use affirmative strategy: grant if user has any of these roles
    return $security->vote(['ROLE_ADMIN', 'ROLE_SUPERADMIN'], null, 'affirmative');
}
```

Benefits of This Design
-----------------------

- ✅ **Works in static contexts**
- ✅ **Centralized permission logic**
- ✅ **Easily swappable SecurityManager implementations**
- ✅ **Separation of business and security logic**

Limitations and Considerations
------------------------------

- ⚠️ **Global state** – The SecurityManager is stored statically, which may lead to issues in unit tests or multi-threaded environments.
- ⚠️ **Testing** – The SecurityManager must be carefully mocked and reset between tests.

Conclusion
----------

This implementation enables permission checks inside static methods using a StaticServiceLocator. While practical and useful in many scenarios, it introduces a global state, which should be used with caution—especially in large, concurrent, or highly dynamic applications.

It is best suited for controlled environments where access control must be enforced within static utilities or service layers that cannot rely on constructor injection.
