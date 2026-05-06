# AI Skills Usage Guide

This guide explains how to use the repository skill pack in the adapters that are currently checked into this package.

## Start here

For any non-trivial task, agents should treat these files as the shared baseline:

- `AGENTS.md`
- `CLAUDE.md`
- `ai/skills/README.md`

The canonical repository skills live under `.github/skills/`.

## What this skill pack is for

This pack helps agents and teammates:

- follow repository-native code style and naming
- choose the correct package module before editing
- keep docs and README in sync with behavior changes
- respect the repository command map and quality gates
- align CI and release changes with the checked-in workflows

## Recommended workflow for agents

1. Read `AGENTS.md`.
2. Load the relevant skills from `.github/skills/`.
3. Implement or review the requested change.
4. Re-check docs, tests, CI assumptions, and release impact before finishing.
5. Keep examples on FormRequest + service/repository architecture.
6. Keep Laravel Resources as presentation transformers; response envelopes only wrap output.

## Common task recipes

### Package code changes

Use:

- `repo-quality-foundation`
- `code-style-and-conventions`
- `architecture-and-design-principles`

### Docs or README updates

Use:

- `docs-and-readme-sync`
- `repo-quality-foundation`

### CI, workflow, or release changes

Use:

- `ci-and-release`
- `repo-quality-foundation`

### Controller or response examples

Use:

- `architecture-and-design-principles`
- `docs-and-readme-sync`

### Mixed package and docs work

Use:

- `repo-quality-foundation`
- `architecture-and-design-principles`
- `docs-and-readme-sync`

## Supported checked-in adapters

### General agent baseline

- `AGENTS.md` is the primary repository policy

### Claude Code

- `CLAUDE.md` mirrors the repository rules in a Claude-friendly format

### VS Code with Copilot

- `.github/copilot-instructions.md` provides the Copilot baseline instructions

## What is intentionally not in this repository yet

Unlike `jooservices/dto`, this package does not currently ship:

- `.cursor/rules/`
- `.claude/commands/`
- `.github/instructions/`
- `.github/prompts/`
- `jetbrains/prompts/`
- `antigravity/prompts/`

If those layers are introduced later, they should stay aligned with the canonical `.github/skills/` files.

## Maintenance rule

When repository behavior changes, update the canonical `.github/skills/` files first, then sync the checked-in adapter layers:

- `AGENTS.md`
- `CLAUDE.md`
- `.github/copilot-instructions.md`
- `ai/skills/README.md`
- `ai/skills/USAGE.md`
