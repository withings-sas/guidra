# Cassandra Explorer

This tool provides an easy way to access the content and properties of Cassandra tables.

The (long term) goal is to build a phpmyadmin for cassandra, with no java required, only CQL queries.

## Quick Start

* Frontend: Download a release, extract it, and configure an apache vhost pointing to the folder "dist/".
* Backend: Edit the line "$nodes = ['127.0.0.1'];" in backend/index.php and replace the IP by one of your Cassandra node.

## Prerequisites

To work on this project, you will need the following things properly installed on your computer.

* [Git](http://git-scm.com/)
* [Node.js](http://nodejs.org/) (with NPM)
* [Bower](http://bower.io/)
* [Ember CLI](http://www.ember-cli.com/)
* [PhantomJS](http://phantomjs.org/)

## Installation

* `git clone <repository-url>` this repository
* change into the new directory
* `npm install`
* `./node_modules/bower/bin/bower install`

## Running / Development

* `ember server`
* Visit your app at [http://localhost:4200](http://localhost:4200).

### Building

* `ember build` (development)
* `ember build --environment production` (production)

### Deploying

Configure a vhost to the "dist/" folder.

## Further Reading / Useful Links

* [ember.js](http://emberjs.com/)
* [ember-cli](http://www.ember-cli.com/)
* Development Browser Extensions
  * [ember inspector for chrome](https://chrome.google.com/webstore/detail/ember-inspector/bmdblncegkenkacieihfhpjfppoconhi)
  * [ember inspector for firefox](https://addons.mozilla.org/en-US/firefox/addon/ember-inspector/)

