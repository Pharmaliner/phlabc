# Usage Guide 📘

This section explains how to **use** the extension, focusing on key implementation details.  
In this part, we demonstrate how to **check user permissions** before accessing a specific controller action using the `VoterInterface`.

## Checking User Permissions 🔐

To restrict access to a controller action based on user permissions, the extension provides a `VoterInterface`. This allows you to check whether a user is authorized to execute a particular action.

### How It Works 🧠

- The `VoterInterface` is used to **abstract and centralize permission logic**.
- Call the `vote()` method with a **string-based permission identifier**.
- If the method returns `false`, the user **is not authorized** to perform the action.
- The currently logged-in FrontendUser is automatically determined from the session within the VoterInterface.
  It is therefore not necessary to explicitly pass the user object or user ID when calling vote().

### Parameters 🔧
| Name           | Required | Type          | Description                                                      |
|----------------|----------|---------------|------------------------------------------------------------------|
| `permission_key` | ✅ Yes   | string  | The permission identifier to check (e.g. 'edit_post', 'view_invoice') |
| `subject`      | ❌ No    | mixed         | Optional context object used during evaluation (e.g. domain object, model) |

### Best Practices 🔄

- Use semantic permission names like `view-report`, `edit-user`, `delete-record`, etc.
- Centralize permission logic inside voters to keep controllers clean.
- Consider throwing `AccessDeniedException` or using TYPO3’s FlashMessage system for unauthorized access.

### Advanced Usage: Passing a Subject to `vote()` ➕

The `vote()` method can optionally accept a **subject**, which may be an object or an array. The requirements are:

- The subject must have an array key `"cruser_id"`
- **Or** a property `cruser_id` including getter methods.

If neither condition is met, a `MissingOwnerAttributeInObjectException` will be thrown.

The function then verifies whether the user, identified from the session, either:

- Has the rights to access the subject, **or**
- Is the creator of the object (determined via `cruser_id`).

### Implementation Example ✅
- [Integration in Controller](./Controller.md)
- [Integration in Service](./Service.md)
- [Integration in Static Classes](./StaticAccess.md)




