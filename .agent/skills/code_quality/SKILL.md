---
name: Code Quality & Architecture
description: Rules for ensuring production-ready, maintainable, and extensible code.
---

# Code Quality & Architecture

## RULE 1 — CODE QUALITY
- All code must be production-ready.
- No placeholder logic.
- No TODO left unresolved.
- No demo shortcuts.
- If something is not safe or robust, do not implement it.

## RULE 2 — ARCHITECTURE
- Respect the existing domain model and database structure.
- Do not flatten or simplify the model for convenience.
- All entities must remain multi-tenant and multi-branch compatible.
- Never introduce single-tenant assumptions.

## RULE 7 — CHANGE MANAGEMENT
- Never refactor or change existing structures unless explicitly requested.
- If an improvement affects existing behavior, explain the impact before coding.

## RULE 8 — EXTENSIBILITY
- Code must be written assuming future extensions.
- Avoid hardcoded limits.
- Avoid magic numbers.
- Prefer configuration over conditionals.
