# Contributing

As a guideline, please follow this process when contributing:

1. [Fork the repository].
2. Create a branch from **master** (`git checkout -b branch-name master`).
3. Make the relevant code changes.
4. Use the various quality checks provided:
    - Run the tests with `make test`.
    - Generate a coverage report with `make coverage`, then open
      `coverage/index.html`.
    - Fix code style issues with `make lint`, but be sure to stage changes
      first.
5. [Squash] commits if necessary (`git rebase -i master`).
6. Submit a pull request to the **master** branch.

[fork the repository]: https://help.github.com/articles/fork-a-repo
[squash]: http://git-scm.com/book/en/Git-Tools-Rewriting-History#Changing-Multiple-Commit-Messages
