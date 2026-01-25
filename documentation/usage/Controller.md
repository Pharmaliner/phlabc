# Overview
The ExampleController is a simple TYPO3 Extbase controller that performs access control using a voter-based security mechanism. The class leverages an external SecurityManager to determine whether a given action (indexAction) may be executed.

## 💡 Purpose and Usage
This controller demonstrates how to protect actions in TYPO3 using a clean, testable, and reusable approach. The access decision is delegated to the injected SecurityManager, promoting separation of concerns and reusability of the access logic.

## 📦 Dependencies

| Namespace                     | Description                                |
|-------------------------------|--------------------------------------------|
| `Pharmaline\PhlAbc\Security\SecurityManager` | Service for access control (voter pattern) |

## 🔧 Constructor

```php
public function __construct(
    private readonly SecurityManager $security,
)
```
The SecurityManager is injected via constructor dependency injection. The readonly modifier (available since PHP 8.1) ensures the dependency cannot be changed after initialization.


## 🧪 Method: indexAction

```php
public function indexAction(): ResponseInterface
```
This method is executed when the index route of the controller is invoked.

### Access Control
```php
if ($this->security->vote('my-permission-to-access-action') === false) {
    // Access denied
}
```
The vote() method is called with a (list of) permission identifier(s) ('attribute(s)') to determine access rights. The outcome (true or false) controls whether the action proceeds.

### 🔧 Parameters

| Name         | Required | Type          | Description                                                                                                                |
|--------------|----------|---------------|----------------------------------------------------------------------------------------------------------------------------|
| `attributes` | ✅ Yes    | string\|array | The permission identifier(s) to check (e.g. 'edit_post', 'view_invoice', or ['view', 'preview'])                          |
| `subject`    | ❌ No     | mixed         | Optional context object used during evaluation (e.g. domain object, model)                                                 |
| `strategy`   | ❌ No     | string\|null  | Optional voting strategy: 'unanimous' (default) or 'affirmative'                                                           |


### Return Value
```php
return $this->htmlResponse('Access granted');
```
If access is granted, the method returns an HTML response. In a real application, this can be replaced with a view rendering or redirection logic as needed.


## Full Example Code

### Basic Permission Check
```php
use Pharmaline\PhlAbc\Security\SecurityManager;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use Psr\Http\Message\ResponseInterface;

class ExampleController extends ActionController
{
    public function __construct(
        private readonly SecurityManager $security,
    ) {
    }

    public function indexAction(): ResponseInterface
    {
        if ($this->security->vote('my-permission-to-access-action') === false) {
            // Access denied
            // You can throw an exception, redirect, or show an error message
        }

        // Access granted
        // Continue with the rest of the action logic

        return $this->htmlResponse('Access granted');
    }
}
```

### Permission Check with Subject
```php
public function orderAction(Product $product): ResponseInterface
{
    if ($this->security->vote('order', $product) === false) {
        // Access denied - either no permission or business rules failed
        return $this->redirect('index');
    }

    // Process order
    return $this->htmlResponse('Order placed');
}
```

### Multiple Permissions (OR Logic)
```php
public function viewAction(Document $document): ResponseInterface
{
    // Grant access if user can either view OR preview
    if ($this->security->vote(['view', 'preview'], $document) === false) {
        return $this->redirect('denied');
    }

    return $this->htmlResponse('Document displayed');
}
```

### Custom Voting Strategy
```php
public function adminAction(): ResponseInterface
{
    // Use affirmative strategy: grant if user has any of these roles
    if ($this->security->vote(['ROLE_ADMIN', 'ROLE_SUPERADMIN'], null, 'affirmative') === false) {
        return $this->redirect('denied');
    }

    return $this->htmlResponse('Admin panel');
}
```
