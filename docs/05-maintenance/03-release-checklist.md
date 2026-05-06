# Release Checklist

1. Confirm all open PRs are merged or intentionally closed.
2. Start from latest `develop`.
3. Run `composer validate --strict`.
4. Run `composer update` when dependency/tooling freshness is part of the release.
5. Run `composer lint:fix`.
6. Run `composer lint:all`.
7. Run `composer test`.
8. Run `composer test:coverage`.
9. Run `composer check`.
10. Run `composer ci`.
11. Review README, docs, examples, AGENTS, skills, and CI assumptions.
12. Tag only after the release branch or PR has passed CI.
