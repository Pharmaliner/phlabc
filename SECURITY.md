# Security Policy

## Supported Versions

The following versions of the **PhlAbc** extension are currently supported with security updates:

| Version | Status      |
|---------|-------------|
| v1.x    | ✅ Supported |
| < 1.0   | ❌ Unsupported (pre-release) |

## Reporting a Vulnerability

If you discover a security vulnerability within this TYPO3 extension, **please do not use public channels or issue trackers** to report it.  
Instead, contact the maintainers directly and responsibly:

- **Email (maintainer):** dialog@mceikens.de
- **Email (vendor):** info@pharmaline.de
- **GitHub Issues (for non-sensitive bugs):** [https://github.com/Pharmaliner/phlabc/issues](https://github.com/Pharmaliner/phlabc/issues)

We aim to respond to valid reports within **3–5 business days**.

---

## Security Model

The **PhlAbc** extension is built around a centralised and strict permission control concept using Symfony's **Voter** pattern. Security is enforced at two levels:

### 1. Action-Level Permission Checks

- Every protected controller action must call the `VoterInterface::vote()` method before execution.
- If permission is denied, the system should throw an exception or redirect the user to a safe location.

### 2. Object-Level Permission Checks

- If a subject (object or array) is passed to the `vote()` method, ownership or permission will be verified using the `cruser_id` attribute.
- If this attribute is missing or invalid, a `MissingOwnerAttributeInObjectException` is thrown to prevent access bypass.

### 3. Session-Based Permission Caching

- On successful login, all resolved permissions for the current frontend user are stored in their session.
- This improves performance and reduces redundant database access, but relies on session integrity.

---

## Recommendations for Developers

- Always use semantic and specific permission identifiers (e.g. `edit-article`, `view-report`).
- Avoid relying on controller-level access control alone; use voters consistently.
- Validate subject objects passed to the `vote()` method to avoid logic flaws.
- Log and monitor denied access attempts if handling sensitive operations.

---

## Disclaimer

While this extension aims to provide a secure framework for frontend user permissions in TYPO3, ultimate responsibility lies with the integrator or developer using the extension. Ensure you apply best practices in permission assignment and access control integration.

