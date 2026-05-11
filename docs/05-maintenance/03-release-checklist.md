# Release Checklist

1. Confirm all open PRs are merged or intentionally closed.
2. Start from latest `develop`.
3. Create feature/fix branches from `develop` and merge them back through PRs.
4. Create the release branch from latest `develop` using `release/<version>`.
5. Update release metadata on the release branch.
6. Run `composer validate --strict`.
7. Run `composer update` when dependency/tooling freshness is part of the release.
8. Run `composer lint:fix`.
9. Run `composer lint:all`.
10. Run `composer test`.
11. Run `composer test:coverage`.
12. Run `composer check`.
13. Run `composer ci`.
14. Review README, docs, examples, AGENTS, skills, and CI assumptions.
15. Open the release PR from `release/<version>` to `master`.
16. Tag only after the release PR has passed CI and merged to `master`.
17. Merge `master` back into `develop` after the release.
