#!/bin/sh

# Check if running in GitHub Actions or locally
if [ -z "$GITHUB_REF" ]; then
  # Running locally, use git symbolic-ref to get the branch name
  branch_name=$(git symbolic-ref --short HEAD)
else
  # Running in GitHub Actions, use GITHUB_REF to get the branch name
  branch_name=$(echo "$GITHUB_REF" | sed 's/refs\/heads\///')
fi

# Check if the branch name is valid
if [[ "$branch_name" == "init" || "$branch_name" =~ ^(feature|fix|hotfix)/.* ]]; then
  exit 0
else
  echo "Branch name must start with 'init/', 'feature/', 'fix/', or 'hotfix/'"
  exit 1
fi