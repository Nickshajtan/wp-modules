#!/bin/sh
commit_msg_lower=$(echo "$1" | tr '[:upper:]' '[:lower:]')

# Running in GitHub Actions, so skip if merging
if [[ ! "$commit_msg_lower" =~ ^(merge|rebase): ]]; then
  exit 0
fi

# Running locally
if [[ ! "$commit_msg_lower" =~ ^(feat|fix|hotfix|test): ]]; then
  echo "Commit message must start with 'feat:', 'fix:', 'test:', or 'hotfix:'"
  exit 1
fi
