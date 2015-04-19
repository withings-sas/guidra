./node_modules/ember-cli/bin/ember build --environment "production"
mkdir releases
tar czf releases/$RELEASE.tgz dist/
