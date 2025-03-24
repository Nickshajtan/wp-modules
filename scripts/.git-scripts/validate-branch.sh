#!/bin/sh

script_dir="$(dirname "$0")"
branch_name=$(sh "$script_dir/detect-branch.sh")

echo "Branch name '$branch_name'"

case "$branch_name" in
  init|main|staging|production|prelaunch|develop)
  echo "Skipping validation for '$branch_name' branch..."
  exit 0 ;;
esac

case "$branch_name" in
  init/*|feature/*|fix/*|hotfix/*) ;;
  *)
    echo "Branch name must start with 'init/', 'feature/', 'fix/', or 'hotfix/'"
    exit 1
    ;;
esac
