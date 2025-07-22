# Overview
The ExampleController is a simple TYPO3 Extbase controller that performs access control using a voter-based security mechanism. The class leverages an external VoterInterface to determine whether a given action (indexAction) may be executed.

## 💡 Purpose and Usage
This controller demonstrates how to protect actions in TYPO3 using a clean, testable, and reusable approach. The access decision is delegated to the injected VoterInterface, promoting separation of concerns and reusability of the access logic.

## 📦 Dependencies

| Namespace                                        | Description                                                    |
|--------------------------------------------------|----------------------------------------------------------------|
| `Pharmaline\PhlAbc\Security\VoterInterface`      | Interface for access control (voter pattern)                   |

## 🔧 Constructor

```php
public function __construct(
    private readonly VoterInterface $voter,
)
```
The VoterInterface is injected via constructor dependency injection. The readonly modifier (available since PHP 8.1) ensures the dependency cannot be changed after initialization.


## 🧪 Method: indexAction

```php
public function indexAction(): ResponseInterface
```
This method is executed when the index route of the controller is invoked.

### Access Control
```php
if ($this->voter->vote('my-permission-to-access-action') === false) {
    // Access denied
}
```
The vote() method is called with a symbolic permission string ('my-permission-to-access-action') to determine access rights. The outcome (true or false) controls whether the action proceeds.

### 🔧 Parameters
| Name           | Required | Type          | Description                                                      |
|----------------|----------|---------------|------------------------------------------------------------------|
| `permission_key` | ✅ Yes   | string  | The permission identifier to check (e.g. 'edit_post', 'view_invoice') |
| `subject`      | ❌ No    | mixed         | Optional context object used during evaluation (e.g. domain object, model) |


### Return Value
```php
return $this->htmlResponse('Access granted');
```
If access is granted, the method returns an HTML response. In a real application, this can be replaced with a view rendering or redirection logic as needed.


## Full Example Code

```php
use Pharmaline\PhlAbc\Security\VoterInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use Psr\Http\Message\ResponseInterface;

class ExampleController extends ActionController
{
    public function __construct(
        private readonly VoterInterface $voter,
    ) {
    }

    public function indexAction(): ResponseInterface
    {
        if ($this->voter->vote('my-permission-to-access-action') === false) {
            // Access denied
            // You can throw an exception, redirect, or show an error message
        }

        // Access granted
        // Continue with the rest of the action logic

        // Example return (adjust to your real return logic)
        return $this->htmlResponse('Access granted');
    }
}
```