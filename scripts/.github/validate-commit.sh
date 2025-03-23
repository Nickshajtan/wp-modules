#!/bin/sh
commit_msg=$(cat "$1")
commit_msg_lower=$(echo "$commit_msg" | tr '[:upper:]' '[:lower:]')

if [[ ! "$commit_msg_lower" =~ ^(feat|fix|hotfix|test): ]]; then
  echo "Commit message must start with 'feat:', 'fix:', 'test:', or 'hotfix:'"
  exit 1
fi
