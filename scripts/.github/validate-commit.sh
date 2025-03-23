#!/bin/sh
commit_msg_lower=$(echo "$1" | tr '[:upper:]' '[:lower:]')

if [[ ! "$commit_msg_lower" =~ ^(feat|fix|hotfix|test): ]]; then
  echo "Commit message must start with 'feat:', 'fix:', 'test:', or 'hotfix:'"
  exit 1
fi
