#!/bin/sh

git tag -f -a "$1" -m "$1"
git push origin master
curl -X POST --data-binary "@github_release.json" https://api.github.com/repos/webketje/gs_components_extended/releases