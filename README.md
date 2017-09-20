# Content Repo

OctoberCMS plugin for controlling content/theme versioning with Git.
Each theme can be versioned using a Git repository.
Changes are committed immediately when changes are made.

Deployments to production can be manual, or automatic on a set schedule.

All changes are always automatically pulled to staging environments.

Future needs: 
    Alerts for merge conflicts to assist in resolution.
    Note, these settings must be made on the production environment itself. Other environments cannot (typically) control the production environment.