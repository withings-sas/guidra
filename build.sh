RELEASE=$1

./node_modules/ember-cli/bin/ember build --environment "production"
mkdir -p releases
tar czf releases/$RELEASE.tgz dist/
