echo Preparing release
read -p "Release version: " version
git archive --format zip -o "components_extended_${version}.zip" -4 master:plugin

read -p "Create github release? (y/n)" release

if [ release = "y" ]
  then
    exec release-it
fi