# PhlAbc

## Table of contents

- [1. Foreword 📖](#foreword-)
- [2. Concept 💡](./documentation/Concept.md)
- [3. Documentation 📚🛠️]()
  - [3.1. Installation 📦](./documentation/Installation.md)
  - [Requirements 📋✅](./documentation/Installation.md#requirements-)
  - [Step-by-step guide 🪜📝](./documentation/Installation.md#step-by-step-guide-)
  - [Integration of the Extension with Permissions, Roles, and Presets](./documentation/Integration.md#integration-of-the-extension-with-permissions-roles-and-presets)
    - [Overview](./documentation/Integration.md#overview)
    - [1. Integration in composer.json](./documentation/Integration.md#1-integration-in-composerjson)
    - [2. Definition of Permissions (permissions.yaml)](./documentation/Integration.md#2-definition-of-permissions-permissionsyaml)
    - [3. Definition of Roles (roles.yaml)](./documentation/Integration.md#3-definition-of-roles-rolesyaml)
    - [4. Definition of Presets (presets.yaml)](./documentation/Integration.md#4-definition-of-presets-presetsyaml)
    - [5. Specials and Advanced Features for Presets](./documentation/Integration.md#5-specials-and-advanced-features-for-presets)
      - [5.1 Wildcard *](./documentation/Integration.md#51-wildcard-)
      - [5.2 Partial Wildcards PART_OF_PERMISSION_NAME_*](./documentation/Integration.md#52-partial-wildcards-part_of_permission_name_)
    - [6. Importing the Configuration](./documentation/Integration.md#6-importing-the-configuration)
  - [Usage Guide 📘](./documentation/usage/UsageGuide.md)
    - [Checking User Permissions 🔐](./documentation/usage/UsageGuide.md#checking-user-permissions-)
    - [How It Works 🧠](./documentation/usage/UsageGuide.md#how-it-works-)
    - [Parameters 🔧](./documentation/usage/UsageGuide.md#parameters-)
    - [Best Practices 🔄](./documentation/usage/UsageGuide.md#best-practices-)
    - [Advanced Usage: Passing a Subject to `vote()` ➕](./documentation/usage/UsageGuide.md#advanced-usage-passing-a-subject-to-vote-)
    - [Implementation Example ✅](./documentation/usage/UsageGuide.md#implementation-example-)
      - [Implementation Controller](./documentation/usage/Controller.md)
        - [Purpose and Usage](./documentation/usage/Controller.md#-purpose-and-usage)
        - [Dependencies](./documentation/usage/Controller.md#-dependencies)
        - [Constructor](./documentation/usage/Controller.md#-constructor)
        - [Method: indexAction](./documentation/usage/Controller.md#-method-indexaction)
        - [Access Control](./documentation/usage/Controller.md#access-control)
        - [Parameters](./documentation/usage/Controller.md#-parameters)
        - [Return Value](./documentation/usage/Controller.md#return-value)
        - [Example Code](./documentation/usage/Controller.md#full-example-code)
      - [Implementation Service](./documentation/usage/Service.md)
        - [Namespace & Dependencies](./documentation/usage/Service.md#namespace--dependencies)
        - [Constructor](./documentation/usage/Service.md#constructor)
        - [Method: serviceMethod()](./documentation/usage/Service.md#method-servicemethod)
        - [Purpose](./documentation/usage/Service.md#purpose)
        - [Logic](./documentation/usage/Service.md#logic)
        - [Example Usage](./documentation/usage/Service.md#example-usage)
        - [Benefits](./documentation/usage/Service.md#benefits)
        - [Extensibility](./documentation/usage/Service.md#extensibility)
      - [Implementation Static Access](./documentation/usage/StaticAccess.md)
        - [Purpose and Motivation](./documentation/usage/StaticAccess.md#purpose-and-motivation)
        - [Why is this approach necessary?](./documentation/usage/StaticAccess.md#why-is-this-approach-necessary)
        - [Component Breakdown](./documentation/usage/StaticAccess.md#component-breakdown)
        - [VoterInterface](./documentation/usage/StaticAccess.md#voterinterface)
        - [VoterServiceLocator](./documentation/usage/StaticAccess.md#voterservicelocator)
        - [Constructor](./documentation/usage/StaticAccess.md#constructor)
        - [Static Method: serviceFunction()](./documentation/usage/StaticAccess.md#static-method-servicefunction)
        - [Example usage](./documentation/usage/StaticAccess.md#example-usage)
        - [Benefits of This Design](./documentation/usage/StaticAccess.md#benefits-of-this-design)
        - [Limitations and Considerations](./documentation/usage/StaticAccess.md#limitations-and-considerations)
        - [Conclusion](./documentation/usage/StaticAccess.md#conclusion)
      - [Implementation ViewHelper](./documentation/usage/ViewHelper.md)
        - [Declaration](./documentation/usage/ViewHelper.md#-viewhelper-declaration)
        - [Parameters](./documentation/usage/ViewHelper.md#-parameters)
        - [Code Example](./documentation/usage/ViewHelper.md#code-example)
        - [How it works](./documentation/usage/ViewHelper.md#how-it-works)
        - [Purpose](./documentation/usage/ViewHelper.md#purpose)
        - [Usage Instructions](./documentation/usage/ViewHelper.md#usage-instructions)
        - [Example Usage](./documentation/usage/ViewHelper.md#example-usage)
- [Contact](#contact)


## Foreword 📖
In modern web applications, fine-grained control of user permissions plays a crucial role—especially when it comes to access to sensitive content and features in the frontend. The TYPO3 extension **PhlAbc** was developed with exactly this goal in mind: to provide flexible, centralised, and fine-grained management of permissions for frontend users.

Based on the well-established Symfony Voter concept, this extension offers an elegant and powerful architecture that allows access rights to be controlled not only at the action level but also for individual objects. This opens up a wide range of possibilities for developers and administrators to create a secure yet user-friendly experience.

PhlAbc integrates seamlessly with TYPO3 and leverages established mechanisms such as user and group management, as well as role models. The extension places particular emphasis on extensibility and ease of use, enabling it to support both standard applications and complex scenarios.

We hope this extension helps you equip your TYPO3 projects with robust and flexible frontend permission control and wish you every success in meeting your individual requirements.

## Contact
If you have any questions, problems or suggestions for improving the extension, please feel free to contact us:

|  |                                                                                                                                                                                                                                                                       |
|---|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| **Publisher**<br>- **Name:** Pharmaline GmbH<br>- **E-Mail:** info@pharmaline.de<br>- **GitHub Issues:** [https://github.com/Pharmaliner/phlabc/issues](https://github.com/Pharmaliner/phlabc/issues)<br>- **Website:** [https://www.pharmaline.de/](https://www.pharmaline.de/) | **Developer**<br>- **Name:** MCEikens<br>- **E-Mail:** dialog@mceikens.de<br>- **GitHub Issues:** [https://github.com/Pharmaliner/phlabc/issues](https://github.com/Pharmaliner/phlabc/issues)<br>- **Website:** [https://www.mceikens.de/](https://www.mceikens.de/) |
