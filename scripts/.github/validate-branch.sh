#!/bin/sh

# Check if running in GitHub Actions or locally
if [ -z "$GITHUB_REF" ]; then
  # Running locally, use git symbolic-ref to get the branch name
  branch_name=$(git symbolic-ref --short HEAD)
else
  # Running in GitHub Actions, use GITHUB_REF to get the branch name
    # If it's a pull request, we use GITHUB_HEAD_REF (the source branch of the PR)
    if [[ "$GITHUB_REF" =~ ^refs/pull/ ]]; then
      branch_name="$GITHUB_HEAD_REF"
    else
      branch_name=$(echo "$GITHUB_REF" | sed 's/refs\/heads\///')
    fi
fi

echo "Branch name '$branch_name'"

if [[ "$branch_name" == "init" ]]; then
  exit 0
fi;

if [[ ! "$branch_name" =~ ^(init|feature|fix|hotfix)/.* ]]; then
  echo "Branch name must start with 'init/', 'feature/', 'fix/', or 'hotfix/'"
  exit 1
fi
