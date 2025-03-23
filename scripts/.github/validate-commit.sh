#!/bin/sh
commit_msg_lower=$(echo "$1" | tr '[:upper:]' '[:lower:]')

# Running in GitHub Actions, so skip if merging
case "$commit_msg_lower" in
  merge:*|rebase:*) exit 0 ;;
esac

# Running locally
case "$commit_msg_lower" in
  feat:*|fix:*|hotfix:*|test:*) ;;
  *)
    echo "Commit message must start with 'feat:', 'fix:', 'test:', or 'hotfix:'"
    exit 1
    ;;
esac