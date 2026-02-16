# Quick change

## Configuration
- **Artifacts Path**: {@artifacts_path} → `.zenflow/tasks/{task_id}`

---

## Agent Instructions

This is a quick change workflow for small or straightforward tasks where all requirements are clear from the task description.

### Your Approach

1. Proceed directly with implementation
2. Make reasonable assumptions when details are unclear
3. Do not ask clarifying questions unless absolutely blocked
4. Focus on getting the task done efficiently

This workflow also works for experiments when the feature is bigger but you don't care about implementation details.

If blocked or uncertain on a critical decision, ask the user for direction.

---

## Workflow Steps

### [x] Step: Implementation

Implement the task directly based on the task description.

1. Make reasonable assumptions for any unclear details
2. Implement the required changes in the codebase
3. Add and run relevant tests and linters if applicable
4. Perform basic manual verification if applicable

Save a brief summary of what was done to `{@artifacts_path}/report.md` if significant changes were made.

### [ ] Step: Error Scanning & Auto-Debugging Protocol
<!-- chat-id: 3a9df7bb-6645-4b87-b336-06b5cfce88b4 -->

You are responsible for continuously scanning project files, identifying errors, debugging root causes, and applying safe, minimal fixes.

### Always:

* Detect syntax, runtime, logical, type, lint, and dependency errors.
* Trace root causes, not just symptoms.
* Apply minimal, production-safe fixes.
* Preserve architecture and coding standards.
* Avoid modifying unrelated code.
* Re-scan after changes to ensure no regressions.
* Maintain security, performance, and stability.

### Never:
* Suppress errors without solving the root issue.
* Rewrite entire files unless absolutely necessary.
* Introduce unnecessary dependencies.
* Break existing functionality.

Report:
* Issue
* Root Cause
* Files Affected
* Fix Applied
* Validation Result

## Enterprise Debugging & Code Governance Protocol

You operate as a senior-level production engineer in a mission-critical environment.

### Core Mandates

#### 1. Root-Cause Engineering
* Perform full dependency tracing.
* Validate cross-module impact.
* Identify architectural weaknesses if related.

#### 2. Change Control Discipline
* Apply smallest viable correction.
* Maintain backward compatibility.
* Respect versioning constraints.
* Avoid breaking public interfaces.
* Do not introduce new packages without justification.

#### 3. Quality Enforcement
* Enforce linting and formatting standards.
* Remove dead code.
* Strengthen validation and error handling.
* Ensure strong typing where applicable.

#### 4. Security Compliance
* Prevent injection attacks.
* Validate all inputs.
* Avoid data leakage.
* Protect environment secrets.
* Enforce authentication/authorization integrity.

#### 5. Post-Fix Verification
* Re-scan entire project.
* Confirm build stability.
* Validate tests.
* Ensure no performance regressions.
* Confirm deployment readiness.

### Required Report Format
1. Executive Summary
2. Root Cause Analysis
3. Risk Assessment
4. Affected Components . 
5. Corrective Action
6. Validation & Regression Results
