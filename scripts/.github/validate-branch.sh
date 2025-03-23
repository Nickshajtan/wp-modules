#!/bin/sh
branch_name=$(git symbolic-ref --short HEAD)
if [[ ! "$branch_name" =~ ^(init|feature|fix|hotfix)/.* ]]; then
  echo "Branch name must start with 'init/', 'feature/', 'fix/', or 'hotfix/'"
  exit 1
fi
