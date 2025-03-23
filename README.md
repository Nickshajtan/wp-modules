# WordPress Abstractions and Modules for modern development

## Setting up Git Hooks

To automatically check branch and commit naming conventions, you need to set up Git hooks on your local machine.
This step **cannot be skipped** because it validates by *Github Actions*.

### Steps:

1. Clone the repository:
   ```bash
   git clone https://github.com/Nickshajtan/wp-modules.git
   cd wp-modules
2. Navigate to the .git/hooks directory:
   ```bash
   cd .git/hooks
3. Create or edit the `pre-push` hook for branch name validation by copying the following code:
   ```bash
   #!/bin/bash
   # Call the validate-branch script
   bash ./scripts/.github/validate-branch.sh
4. Don't forget to make the file executable:
   ```bash
   chmod +x pre-push
5. Create or edit the `commit-msg` hook for commit message validation by using this script:
   ```bash
   #!/bin/bash
   # Call the validate-commit script with commit message file as argument
   bash ./scripts/.github/validate-commit.sh "$1"
6. Don't forget to make the file executable:
   ```bash
   chmod +x commit-msg

Now, your branch names and commit messages will be automatically validated before pushing.