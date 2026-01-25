# Voter ViewHelpers

This documentation covers two voter ViewHelpers:
- `phl:voter` - Conditional rendering with then/else blocks
- `phl:voterResult` - Returns a boolean for use in expressions

Both ViewHelpers check permissions but differ in how they output results.

---

## 🛠️ ViewHelper Declaration

```php
Pharmaline\PhlAbc\ViewHelpers\VoterViewHelper
Pharmaline\PhlAbc\ViewHelpers\VoterResultViewHelper
```

To use them in your Fluid templates, register the namespace:
```html
{namespace phl=Pharmaline\PhlAbc\ViewHelpers}
```

## 🔧 Parameters

| Name             | Required | Type          | Description                                                                |
|------------------|----------|---------------|----------------------------------------------------------------------------|
| `permissionKey`  | ✅ Yes   | string\|array | The permission identifier to check (e.g. 'edit_post', 'view_invoice'       |
| `subject`        | ❌ No    | mixed         | Optional context object used during evaluation (e.g. domain object, model) |
| `strategy`       | ❌ No    | string        | Optional voting strategy: 'unanimous' (default) or 'affirmative'           |

## Overview
This code demonstrates the usage of a custom Fluid ViewHelper `<phl:voter>` within a TYPO3 Fluid template. The ViewHelper is used to perform a permission check based on a specified permission key. Depending on the result of this check, different parts of the template are rendered dynamically.

## Code Example
```html
<html xmlns:phl="Pharmaline\PhlAbc\ViewHelpers" data-namespace-typo3-fluid="true">
    <phl:voter permissionKey="EXAMPLE_VIEW_LIST">
        <f:then>
            <!-- Access Granted -->
        </f:then>
        <f:else>
            <!-- Access denied -->
        </f:else>
    </phl:voter>
</html>
```

## How it works
- Namespace: The attribute `xmlns:phl="Pharmaline\PhlAbc\ViewHelpers"` registers the `phl` namespace, allowing the use of custom ViewHelpers from the specified PHP namespace.
- ViewHelper `<phl:voter>`: This ViewHelper performs a permission check based on the `permissionKey` attribute provided.
- Attribute `permissionKey`: The string `EXAMPLE_VIEW_LIST` represents the permission to be checked (e.g., whether the current user has the right to view a list).
- Conditional Rendering:
    - `<f:then>` block is rendered if permission is granted (the user has the required rights).
    - `<f:else>` block is rendered if permission is denied (the user lacks the required rights).

## Purpose
This code is used to control the visibility or availability of UI elements or features inside a Fluid template based on user permissions.

Example:
You want to display a list of records only if the user has the `EXAMPLE_VIEW_LIST` permission. Otherwise, an alternative message or UI is shown to indicate insufficient permissions.

## Usage Instructions
1. Register Namespace: Include the namespace for the ViewHelper in the `<html>` tag or in the `<f:layout>` definition.
2. Insert ViewHelper: Use the `<phl:voter>` ViewHelper at the location in your template where the permission check should happen.
3. Specify Permission Key: Pass the relevant permission key as the `permissionKey` attribute.
4. Define Then / Else Blocks: Inside the ViewHelper, provide the `<f:then>` block containing content to display if access is allowed, and the `<f:else>` block with content if access is denied.

## Example Usage

### Basic Permission Check (Without Subject)
```html
<phl:voter permissionKey="EXAMPLE_VIEW_LIST">
    <f:then>
        <ul>
            <li>Record 1</li>
            <li>Record 2</li>
        </ul>
    </f:then>
    <f:else>
        <p>You do not have permission to view this list.</p>
    </f:else>
</phl:voter>
```

### With Subject (Object-based Permission)
```html
<phl:voter permissionKey="order" subject="{product}">
    <f:then>
        <button>Order Now</button>
    </f:then>
    <f:else>
        <p>This product cannot be ordered.</p>
    </f:else>
</phl:voter>
```


### With Custom Voting Strategy
```html
<!-- Use affirmative strategy: grant if user has any of these roles -->
<phl:voter permissionKey="ROLE_ADMIN" strategy="affirmative">
    <f:then>
        <a href="/admin">Admin Panel</a>
    </f:then>
    <f:else>
        <p>Admin access required</p>
    </f:else>
</phl:voter>
```

---

## VoterResultViewHelper (phl:voterResult)

The `phl:voterResult` ViewHelper performs the same permission check as `phl:voter`, but instead of rendering conditional blocks, it returns a boolean value (`TRUE` or `FALSE`). This makes it ideal for use within Fluid's standard `<f:if>` conditions and allows chaining with other conditional logic.

### When to Use
Use `phl:voterResult` when you need to:
- Combine permission checks with other conditions using AND/OR logic
- Use the result in inline conditions `{f:if(...)}`
- Chain multiple permission checks together in an '<f:if>' - ViewHelper
- Integrate permission logic into more complex conditional expressions

### Basic Usage

**Simple permission check:**
```html
<f:if condition="{phl:voterResult(permissionKey: 'EXAMPLE_VIEW_LIST')}">
    <f:then>
        <ul>
            <li>Record 1</li>
            <li>Record 2</li>
        </ul>
    </f:then>
    <f:else>
        <p>You do not have permission to view this list.</p>
    </f:else>
</f:if>
```

**With subject:**
```html
<f:if condition="{phl:voterResult(permissionKey: 'order', subject: product)}">
    <button>Order Product</button>
</f:if>
```

**With custom voting strategy:**
```html
<!-- Use affirmative strategy -->
<f:if condition="{phl:voterResult(permissionKey: 'ROLE_ADMIN', strategy: 'affirmative')}">
    <a href="/admin">Admin Panel</a>
</f:if>
```

### Chaining with Other Conditions

**Combining with AND logic:**
```html
<f:if condition="{phl:voterResult(permissionKey: 'EXAMPLE_VIEW_LIST')} && {post.published}">
    <p>This published post is visible to you.</p>
</f:if>
```

**Complex conditional logic:**
```html
<f:if condition="{phl:voterResult(permissionKey: 'order', subject: product)} && {product.inStock} && {user.verifiedAccount}">
    <button>Complete Purchase</button>
</f:if>
```

### Comparison: voter vs voterResult

**Same permission check, different approaches:**

Using `phl:voter` (conditional rendering):
```html
<phl:voter permissionKey="EXAMPLE_VIEW_LIST">
    <f:then>Access granted</f:then>
    <f:else>Access denied</f:else>
</phl:voter>
```

Using `phl:voterResult` (boolean return):
```html
<f:if condition="{phl:voterResult(permissionKey: 'EXAMPLE_VIEW_LIST')}">
    <f:then>Access granted</f:then>
    <f:else>Access denied</f:else>
</f:if>
```

**Choose `phl:voter` when:** You have a simple permission check with then/else blocks.

**Choose `phl:voterResult` when:** You need to combine the permission check with other conditions or use it in expressions.

### Advanced Examples

**Check permission and business rule:**
```html
<f:if condition="{phl:voterResult(permissionKey: 'order', subject: product, strategy: 'unanimous')}">
    <!-- Both permission AND all business rules (stock, availability, etc.) pass -->
    <button>Order Now</button>
</f:if>
```

### Voting Strategy Selection in ViewHelpers

**Unanimous (default):**
- Base permission + all constraints must pass
- Don't specify strategy parameter or use `strategy="unanimous"`

**Affirmative:**
- Multiple alternative paths to access
- Use `strategy="affirmative"`

### Custom Voter Viewhelper

If you want to implement your own Voter class for Viewhelper voter checks, you need to develop a custom viewhelper and define your voter logic appropriately. The ViewHelper should use the SecurityManager's vote method with the appropriate parameters (permissionKey, subject, and strategy).
