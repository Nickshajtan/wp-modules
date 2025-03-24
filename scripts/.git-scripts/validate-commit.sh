#!/bin/sh

script_dir="$(dirname "$0")"
branch_name=$(sh "$script_dir/detect-branch.sh")
case "$branch_name" in
  init|main|staging|production|prelaunch|develop)
  echo "Skipping validation for '$branch_name' branch..."
  exit 0 ;;
esac

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