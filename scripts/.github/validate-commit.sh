#!/bin/sh
commit_msg=$(cat "$1")
if [[ ! "$commit_msg" =~ ^(Feat|Fix|Hotfix): ]]; then
  echo "Commit message must start with 'feat:', 'fix:', or 'hotfix:'"
  exit 1
fi
