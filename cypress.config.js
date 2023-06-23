const { defineConfig } = require( 'cypress' );

module.exports = defineConfig( {
	chromeWebSecurity: false,
	defaultCommandTimeout: 20000,
	e2e: {
		setupNodeEvents( on, config ) {
			return require( './tests/plugins/index.js' )( on, config );
		},
		specPattern: './tests/*.cypress.js',
		supportFile: false,
	},
	env: {
		testURL: 'http://localhost:8889',
		wpPassword: 'password',
		wpUsername: 'admin',
	},
	pageLoadTimeout: 120000,
	projectId: 'zvzntf',
	retries: {
		openMode: 0,
		runMode: 0,
	},
	screenshotsFolder: 'tests/screenshots',
	videosFolder: 'tests/videos',
	viewportHeight: 1440,
	viewportWidth: 2560,
} );