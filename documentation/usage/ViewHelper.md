# ViewHelper
The `VoterViewHelper` allows conditional rendering in Fluid templates based on custom permission logic implemented in a `VoterInterface`.  
It enables clean and reusable access control in the view layer.

---

## 🛠️ ViewHelper Declaration

```php
Pharmaline\PhlAbc\ViewHelpers\VoterViewHelper
```

To use it in your Fluid templates, register the namespace:
```html
{namespace phl=Pharmaline\PhlAbc\ViewHelpers}
```

## 🔧 Parameters

| Name           | Required | Type          | Description                                                      |
|----------------|----------|---------------|------------------------------------------------------------------|
| `permission_key` | ✅ Yes   | string  | The permission identifier to check (e.g. 'edit_post', 'view_invoice') |
| `subject`      | ❌ No    | mixed         | Optional context object used during evaluation (e.g. domain object, model) |

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
Without subject
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

With subject
```html
<phl:voter permissionKey="EXAMPLE_VIEW_LIST" subject="{post}">
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