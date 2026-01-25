# Generic Voter – Developer Documentation

## Namespace

```php
YourNamespace\Security\Voter
```

## Purpose

A Voter is a component used to evaluate whether a specific condition is met in your application. It can check permissions, roles, feature flags, business rules, or any other custom business rules.

Voters use a **tri-state voting system** to provide nuanced access control decisions.

This document provides a template and guidance for creating custom voters that integrate with TYPO3's `Context` system or any similar context mechanism.

---

## Tri-State Voting System

Voters must return one of three states:

- **`VoterInterface::ACCESS_GRANTED` (1)**: Voter actively allows this action
- **`VoterInterface::ACCESS_DENIED` (-1)**: Voter actively blocks this action (business rule violated)
- **`VoterInterface::ACCESS_ABSTAIN` (0)**: Voter has no opinion / doesn't handle this case

**Critical Rule**:
- Use `ACCESS_DENIED` only when actively blocking due to violated business rules (e.g., offer closed, out of stock, past deadline)
- Use `ACCESS_ABSTAIN` when the voter simply doesn't have information to decide
- Never return `ACCESS_DENIED` just because a permission is missing - use `ACCESS_ABSTAIN` instead

---

## Implementation

### Constructor

```php
public function __construct(private readonly Context $context)
```

* The `Context` or equivalent system **must** be injected in the constructor.
* It is used to access data aspects needed for evaluating conditions.
* Example: user attributes, permissions, roles, or any other relevant context data.

---

### Method: `supports()`

```php
public function supports(string $attribute, mixed $subject): bool
```

* Determines if the voter supports the given attribute and subject.
* Returns `true` if the attribute is supported and the subject is of the expected type, otherwise `false`.
* Be specific about what you support to avoid unnecessary processing.

Example:

```php
public function supports(string $attribute, mixed $subject): bool
{
    return $subject instanceof Product
        && in_array($attribute, ['order', 'view', 'edit']);
}
```

---

### Method: `voteOnAttribute()`

```php
public function voteOnAttribute(string $attribute, mixed $subject): int
```

* Performs the actual evaluation of the attribute.
* Uses the injected `Context` to access necessary data aspects.
* Can implement any custom logic (permissions, roles, business rules, feature flags, or other conditions).
* Returns `VoterInterface::ACCESS_GRANTED`, `ACCESS_DENIED`, or `ACCESS_ABSTAIN`.
* Can throw exceptions if required aspects are not available.

**Example structure:**

```php
public function voteOnAttribute(string $attribute, mixed $subject): int
{
    // GRANT: Actively allow
    if ($condition) {
        return VoterInterface::ACCESS_GRANTED;
    }

    // DENY: Actively block (use only for business rule violations!)
    if ($businessRuleViolated) {
        return VoterInterface::ACCESS_DENIED;
    }

    // ABSTAIN: Default when no strong opinion
    return VoterInterface::ACCESS_ABSTAIN;
}
```

---

## When to Use Each Return Value

### Use ACCESS_GRANTED when:
- User has the required permission
- Business rule is satisfied
- Resource is available
- User is the owner of the resource

### Use ACCESS_DENIED when:
- Business rule is explicitly violated (offer closed, out of stock, past deadline)
- User must NOT perform this action
- Active blocking is required

### Use ACCESS_ABSTAIN when:
- Voter doesn't handle this case
- Permission not in voter's source
- No restriction applies
- Letting other voters decide
- Permission is missing (don't use DENY for this!)

---

## Register voter in Dependency Injection

It is necessary to register the custom voters in the dependency injection.
You can choose two ways of registration.

First way is the classic way you register your voter in your Services.yaml and add a tag to them like:

```yaml
Vendor\MyExtension\Security\Voter\MyVoter:
    tags: [ phlabc.voter ]
```

The second way is to use the named attribute functionality and register the tag in the voter directly like:

```php
namespace Vendor\MyExtension\Security\Voter;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('phlabc.voter')]
class MyVoter implements VoterInterface {
    // Voter Code
}
```

---

## Example Voters

### Example 1: Permission Voter (Checks Global Permissions)

```php
<?php

namespace YourNamespace\Security\Voter;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Context\Exception\AspectPropertyNotFoundException;
use Pharmaline\PhlAbc\Security\VoterInterface;

#[AutoconfigureTag('phlabc.voter')]
class PermissionVoter implements VoterInterface
{
    public function __construct(private readonly Context $context)
    {
    }

    public function supports(string $attribute, mixed $subject): bool
    {
        // Only support permission checks without subject
        return $subject === null;
    }

    public function voteOnAttribute(string $attribute, mixed $subject): int
    {
        $frontendUserPermissionAspect = $this->context->getAspect('frontend.user.permissions');
        $permissions = $frontendUserPermissionAspect->get('permissions');

        if (in_array($attribute, $permissions)) {
            return static::ACCESS_GRANTED;
        }

        return static::ACCESS_DENIED;
    }
}
```

### Example 2: Business Rule Voter (Checks Product Availability)

```php
<?php

namespace YourNamespace\Security\Voter;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use YourNamespace\Domain\Model\Product;
use Pharmaline\PhlAbc\Security\VoterInterface;

#[AutoconfigureTag('phlabc.voter')]
class ProductAvailabilityVoter implements VoterInterface
{
    public function supports(string $attribute, mixed $subject): bool
    {
        return $subject instanceof Product
            && in_array($attribute, ['order', 'purchase']);
    }

    public function voteOnAttribute(string $attribute, mixed $subject): int
    {
        /** @var Product $subject */

        // Actively block if product is discontinued
        if ($subject->isDiscontinued()) {
            return VoterInterface::ACCESS_DENIED;
        }

        // Actively block if out of stock
        if ($subject->getStock() <= 0) {
            return VoterInterface::ACCESS_DENIED;
        }

        // Grant access if available
        return VoterInterface::ACCESS_GRANTED;
    }
}
```

### Example 3: Feature Flag Voter

```php
<?php

namespace YourNamespace\Security\Voter;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use TYPO3\CMS\Core\Context\Context;
use Pharmaline\PhlAbc\Security\VoterInterface;

#[AutoconfigureTag('phlabc.voter')]
class FeatureFlagVoter implements VoterInterface
{
    private const FEATURE_PREFIX = 'feature.';

    public function __construct(private readonly Context $context)
    {
    }

    public function supports(string $attribute, mixed $subject): bool
    {
        return str_starts_with($attribute, self::FEATURE_PREFIX);
    }

    public function voteOnAttribute(string $attribute, mixed $subject): int
    {
        try {
            $featureName = substr($attribute, strlen(self::FEATURE_PREFIX));
            $featureAspect = $this->context->getAspect('features');

            if ($featureAspect->get($featureName) === true) {
                return VoterInterface::ACCESS_GRANTED;
            }

            return VoterInterface::ACCESS_ABSTAIN;

        } catch (\Exception $e) {
            return VoterInterface::ACCESS_ABSTAIN;
        }
    }
}
```

---

## Developer Notes

* Follows a general Voter pattern: `supports()` + `voteOnAttribute()`.
* Always inject the `Context` (or equivalent) to access relevant data.
* Custom voters should define clear support criteria in `supports()`.
* Use tri-state returns appropriately:
    - `ACCESS_GRANTED`: Actively allow
    - `ACCESS_DENIED`: Actively block (business rule violated)
    - `ACCESS_ABSTAIN`: No opinion (default case)
* Ensure `supports()` only returns `true` for attributes and subjects the voter is meant to handle.
* Be specific in `supports()` to avoid unnecessary processing.
* Multiple voters can participate in the same decision - the voting strategy combines their results.
