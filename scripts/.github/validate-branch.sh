#!/bin/sh

# Check if running in GitHub Actions or locally
if [ -z "$GITHUB_REF" ]; then
  # Running locally, use git symbolic-ref to get the branch name
  branch_name=$(git symbolic-ref --short HEAD)
else
  # Running in GitHub Actions, use GITHUB_REF to get the branch name
    # If it's a pull request, we use GITHUB_HEAD_REF (the source branch of the PR)
    case "$GITHUB_REF" in
        refs/pull/*) branch_name="$GITHUB_HEAD_REF" ;;
        *) branch_name=$(echo "$GITHUB_REF" | sed 's|refs/heads/||') ;;
    esac
fi

echo "Branch name '$branch_name'"

case "$branch_name" in
  init|main) exit 0 ;;
esac

case "$branch_name" in
  init/*|feature/*|fix/*|hotfix/*) ;;
  *)
    echo "Branch name must start with 'init/', 'feature/', 'fix/', or 'hotfix/'"
    exit 1
    ;;
esac
