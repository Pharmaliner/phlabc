Overview
--------

The ExampleServiceStatic class implements a **static access pattern** to a Voter service responsible for permission checks. It uses a VoterServiceLocator to inject the VoterInterface implementation into a static context, enabling permission validation even without an object instance.

Purpose and Motivation
----------------------

In PHP, **static methods** cannot benefit from **constructor-based dependency injection**, which makes it difficult to access services like a voter (for permission checks) within a static context.

This code solves the problem by using a **Service Locator pattern** to store the voter instance at runtime, allowing static methods to retrieve it when needed.

### Why is this approach necessary?

- **Static method (serviceFunction) needs access to a Voter service**
  - But static methods don’t support constructor injection.
- **Decouples** permission logic from the static service logic.
- **Centralized access control** logic. 
- Allows reusability of permission checks across static utility functions.


Component Breakdown
-------------------

### VoterInterface

An interface that defines a method to check user permissions:

```php
public function vote(string $permission): bool;
```

### VoterServiceLocator

A utility that manages the static access to the Voter service. It provides:

- setVoter(VoterInterface $voter) – Stores the voter instance statically. 
- getVoter(): VoterInterface – Returns the stored instance.


> **Important**: This must be set early during application bootstrapping or service initialization.

### Constructor

```php
public function __construct(VoterInterface $voter)
```

- Accepts a VoterInterface implementation. 
- Registers the voter via the VoterServiceLocator.


**Usage**: Called once (e.g., during dependency injection container setup) to make the voter available statically.

### Static Method: serviceFunction()

```php
public static function serviceFunction(): string
```

Functionality:
1. Retrieves the current voter via the service locator. 
2. Calls vote('my-permission-to-access-action') to check access. 
3. Returns "Access denied" if the check fails. 
4. Returns "Access granted" if permission is granted.

### Example Usage

```php
    public function __construct(
        VoterInterface $voter,
    )
    {
        VoterServiceLocator::setVoter($voter);
    }

    public static function serviceFunction(): string
    {
        $voter = VoterServiceLocator::getVoter();
        if ($voter->vote('my-permission-to-access-action') === false) {
            // Access denied
            return 'Access denied';
        }

        // Access granted
        // Continue with the rest of the action logic

        // Example return (adjust to your real return logic)
        return 'Access granted';
    }
```

Benefits of This Design
-----------------------

- ✅ **Works in static contexts**
- ✅ **Centralized permission logic**
- ✅ **Easily swappable voter implementations**
- ✅ **Separation of business and security logic**

Limitations and Considerations
------------------------------

- ⚠️ **Global state** – The voter is stored statically, which may lead to issues in unit tests or multi-threaded environments.
- ⚠️ **Testing** – The voter must be carefully mocked and reset between tests.

Conclusion
----------

This implementation enables permission checks inside static methods using a VoterServiceLocator. While practical and useful in many scenarios, it introduces a global state, which should be used with caution-especially in large, concurrent, or highly dynamic applications.

It is best suited for controlled environments where access control must be enforced within static utilities or service layers that cannot rely on constructor injection.