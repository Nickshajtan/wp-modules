#!/bin/sh

# Check if running in GitHub Actions, Gitlab CI or locally
if [ -n "$GITHUB_REF" ]; then
  # Running in GitHub Actions
  case "$GITHUB_REF" in
    refs/pull/*) branch_name="$GITHUB_HEAD_REF" ;;  # PR гілка
    *) branch_name=$(echo "$GITHUB_REF" | sed 's|refs/heads/||') ;;
  esac
elif [ -n "$CI_COMMIT_REF_NAME" ]; then
  # Running in GitLab CI
  if [ -n "$CI_MERGE_REQUEST_SOURCE_BRANCH_NAME" ]; then
    branch_name="$CI_MERGE_REQUEST_SOURCE_BRANCH_NAME"
  else
    branch_name="$CI_COMMIT_REF_NAME"
  fi
else
  # Running locally
  branch_name=$(git symbolic-ref --short HEAD)
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
