# AI Skills

This repository includes a lightweight AI skill pack aligned with the structure used in `jooservices/dto`.

The intent is to keep one repository truth for code style, architecture, docs sync, and CI behavior, then expose that truth through the agent entry points that this package actually uses today.

## Current scope

This package keeps the AI layer intentionally smaller than `jooservices/dto`.

It currently focuses on:

- repository quality expectations
- code style and conventions
- architecture and module ownership
- docs and README synchronization
- CI and release policy

## Canonical sources

Start with these files:

- `AGENTS.md`
- `.github/skills/`
- `ai/skills/README.md`
- `ai/skills/USAGE.md`

The canonical repository skill files currently are:

- `.github/skills/repo-quality-foundation/SKILL.md`
- `.github/skills/code-style-and-conventions/SKILL.md`
- `.github/skills/architecture-and-design-principles/SKILL.md`
- `.github/skills/docs-and-readme-sync/SKILL.md`
- `.github/skills/ci-and-release/SKILL.md`

## Adapter layers currently checked in

This repository currently maintains these adapter documents:

- `AGENTS.md` as the shared repository policy
- `CLAUDE.md` for Claude Code
- `.github/copilot-instructions.md` for GitHub Copilot
- `ai/skills/README.md` and `ai/skills/USAGE.md` for teammate onboarding and cross-agent usage

Unlike `jooservices/dto`, this package does not currently maintain additional checked-in adapter directories for Cursor, JetBrains, or Antigravity.

## Recommended workflow for agents

1. Read `AGENTS.md`.
2. Load the relevant skills from `.github/skills/`.
3. Implement or review the requested change.
4. Re-check docs, tests, CI impact, and release impact before finishing.

## Maintenance rule

When repository behavior changes, update the canonical `.github/skills/` files first, then sync the checked-in adapters:

- `AGENTS.md`
- `CLAUDE.md`
- `.github/copilot-instructions.md`
- `ai/skills/README.md`
- `ai/skills/USAGE.md`