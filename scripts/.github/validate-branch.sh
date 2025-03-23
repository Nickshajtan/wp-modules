#!/bin/sh

# Check if running in GitHub Actions or locally
if [ -z "$GITHUB_REF" ]; then
  # Running locally, use git symbolic-ref to get the branch name
  branch_name=$(git symbolic-ref --short HEAD)
else
  # Running in GitHub Actions, use GITHUB_REF to get the branch name
  branch_name=$(echo "$GITHUB_REF" | sed 's/refs\/heads\///')
fi

if [[ ! "$branch_name" =~ ^(init|feature|fix|hotfix)/.* && "$branch_name" != "init" ]]; then
  echo "Branch name must start with 'init/', 'feature/', 'fix/', or 'hotfix/'"
  exit 1
fi
