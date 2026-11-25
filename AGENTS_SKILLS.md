# AI Agent Skills & Workflows

This document outlines the specialized agentic skills installed in the `MVCLite-IAD` project and provides a repeatable workflow for utilizing them to their full potential.

These skills act as "expert modes" that enforce high-quality standards, security, and consistent design across the codebase.

## 🛠️ Installed Skills Directory

### Development & Architecture
*   **`php-pro`**: Senior PHP development. Enforces PHP 8.3+ features, strict typing (`declare(strict_types=1)`), modern patterns (DTOs, Value Objects), and architectural best practices.
*   **`php-best-practices`**: Code quality guardian. Focuses on PSR standards, SOLID object-oriented design principles, and general PHP maintainability.

### Security & Database
*   **`laravel-owasp-security`**: Application security expert. Performs OWASP Top 10 security audits, ensures secure authentication, input validation, and secure API design.
*   **`sql-code-review`**: Database guardian. Analyzes SQL queries for security (SQL injection prevention), maintainability, and optimization.

### Design & Frontend
*   **`frontend-design`**: UI/UX specialist. Creates production-grade, premium frontend interfaces using Vanilla CSS (avoiding generic AI aesthetics). Focuses on responsive, dynamic designs.
*   **`web-design-guidelines`**: Accessibility and usability auditor. Reviews UI code for compliance with Web Interface Guidelines and UX best practices.

### Project Standards
*   **`documentation-writer`**: Technical writer. Authors high-quality software documentation guided by the Diátaxis framework (Tutorials, How-To Guides, Reference, Explanation).
*   **`git-commit-formatter`**: Version control organizer. Formats git commit messages according to the Conventional Commits specification.

---

## 🔄 Repeatable Development Workflow

To build a feature reliably and securely, follow this 4-step repeatable workflow when prompting the AI:

### 1. Analyze (Planning & Audit)
Before writing code, ask the AI to analyze the current state or architecture using the relevant skill.
*   **Keywords**: `analyze`, `review`, `audit`, `check against`
*   *Example*: "Review `app/controllers/AuthController.php` using the **laravel-owasp-security** and **php-best-practices** skills. Provide an audit report."

### 2. Design (Architecture & UI)
Define the structure or aesthetics before implementation.
*   **Keywords**: `design`, `structure`, `mockup`
*   *Example*: "Use the **frontend-design** skill to design a premium, Vanilla CSS layout for the Admin Dashboard."

### 3. Implement (Coding)
Instruct the AI to write the code enforcing strict standards.
*   **Keywords**: `build`, `implement`, `refactor`, `create`
*   *Example*: "Implement the User creation logic using the **php-pro** skill. Include a typed DTO and strict types."

### 4. Verify & Document (Quality Assurance)
Ensure the code meets the project's standards and document it.
*   **Keywords**: `verify`, `document`, `format`
*   *Example*: "Use the **documentation-writer** skill to write a Reference guide for the newly created Authentication service. Then, use **git-commit-formatter** to generate a commit message for these changes."

---

## 💡 Practical Prompt Examples

Here are exact phrases you can copy and paste into your AI assistant to trigger specific workflows:

**Backend Feature Creation:**
> "I need to build a new Event Registration feature. Please use the **php-pro** skill to create the Controller and Service classes, ensuring strict typing and DTOs."

**Security Review:**
> "Run an OWASP security audit on the `login.php` view and `AuthController.php` using the **laravel-owasp-security** and **sql-code-review** skills. Check for SQL injection and XSS vulnerabilities."

**UI Polish:**
> "The `events.php` page looks too basic. Use the **frontend-design** skill to upgrade the UI. Make it look premium with smooth hover animations and a color scheme that matches `style.css`."

**Commit & Push:**
> "I've finished the user registration module. Use the **git-commit-formatter** skill to write a conventional commit message for my changes."
